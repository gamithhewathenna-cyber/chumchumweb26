<?php
/**
 * Admin bootstrap: sessions, authentication, CSRF, flash messages,
 * image uploads and form helpers. Included at the top of every admin page.
 */
define('IN_ADMIN', true);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

const ADMIN_IDLE_TIMEOUT  = 7200;            // log out after 2 hours of inactivity
const LOGIN_MAX_ATTEMPTS  = 5;               // failed logins allowed per IP ...
const LOGIN_WINDOW_MIN    = 15;              // ... within this many minutes
const PASSWORD_MIN_LENGTH = 10;
const UPLOAD_MAX_BYTES    = 5 * 1024 * 1024; // 5 MB

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');

/* ------------------------------------------------------------------ */
/*  Database                                                           */
/* ------------------------------------------------------------------ */

/** The admin needs the database — show a clear error page when it is unreachable. */
function admin_db(): PDO
{
    $pdo = db();
    if (!$pdo) {
        http_response_code(503);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Database unavailable</title>'
            . '<div style="font-family:system-ui,sans-serif;max-width:560px;margin:15vh auto;padding:0 20px">'
            . '<h1>Can\'t connect to the database</h1>'
            . '<p>Check <code>DB_NAME</code>, <code>DB_USER</code> and <code>DB_PASS</code> in <code>config/config.php</code>, '
            . 'and make sure the user has All Privileges on the database in cPanel → MySQL® Databases.</p>'
            . '<p>The exact error has been written to the PHP error log.</p></div>';
        exit;
    }
    return $pdo;
}

/** Create the admin tables on first use, so no extra SQL import is needed. */
function ensure_admin_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (
        `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `username`      VARCHAR(60)  NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_login_at` TIMESTAMP    NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_login_attempts` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `ip`           VARCHAR(45)  NOT NULL,
        `attempted_at` DATETIME     NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_ip_time` (`ip`, `attempted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/** Hero-slider columns on menu_categories (added Nov 2026) — created automatically if missing. */
function ensure_category_hero_columns(PDO $pdo): void
{
    if ($pdo->query("SHOW COLUMNS FROM `menu_categories` LIKE 'hero_title'")->fetch()) {
        return;
    }
    $pdo->exec("ALTER TABLE `menu_categories`
        ADD COLUMN `hero_badge`    VARCHAR(100) NOT NULL DEFAULT '' AFTER `is_active`,
        ADD COLUMN `hero_title`    VARCHAR(150) NOT NULL DEFAULT '' AFTER `hero_badge`,
        ADD COLUMN `hero_subtitle` VARCHAR(150) NOT NULL DEFAULT '' AFTER `hero_title`,
        ADD COLUMN `hero_image`    VARCHAR(255) NOT NULL DEFAULT '' AFTER `hero_subtitle`");
}

function admin_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

/* ------------------------------------------------------------------ */
/*  Sessions & authentication                                          */
/* ------------------------------------------------------------------ */

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
}

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('cc_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Empty the session and give it a fresh ID (the old one is deleted). */
function logout_session(): void
{
    admin_session_start();
    $_SESSION = [];
    session_regenerate_id(true);
}

/** The logged-in admin user, or null. */
function current_admin(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    admin_session_start();
    $user = null;
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    if (time() - (int) ($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        logout_session();
        flash('info', 'You were logged out after a period of inactivity.');
        return null;
    }
    $st = admin_db()->prepare('SELECT id, username, created_at, last_login_at FROM admin_users WHERE id = ?');
    $st->execute([(int) $_SESSION['admin_id']]);
    $row = $st->fetch();
    if (!$row) {
        logout_session();
        return null;
    }
    $_SESSION['last_seen'] = time();
    return $user = $row;
}

/** Guard for every protected page. */
function require_login(): array
{
    $user = current_admin();
    if (!$user) {
        $next = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        if (!empty($_SERVER['QUERY_STRING'])) {
            $next .= '?' . $_SERVER['QUERY_STRING'];
        }
        redirect('login.php?next=' . rawurlencode($next));
    }
    return $user;
}

function login_user(PDO $pdo, array $user): void
{
    admin_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id']  = (int) $user['id'];
    $_SESSION['last_seen'] = time();
    $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([(int) $user['id']]);
}

/** Only allow redirects back to admin pages in this folder. */
function safe_next(?string $next): string
{
    $next = (string) $next;
    return preg_match('~^[a-z_-]+\.php(\?[A-Za-z0-9_=&%-]*)?$~', $next) ? $next : 'index.php';
}

/* ---------- Login throttling ---------- */

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function login_locked(PDO $pdo): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM admin_login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MIN . ' MINUTE)');
    $st->execute([client_ip()]);
    return (int) $st->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function record_failed_login(PDO $pdo): void
{
    $pdo->prepare('INSERT INTO admin_login_attempts (ip, attempted_at) VALUES (?, NOW())')->execute([client_ip()]);
    $pdo->exec('DELETE FROM admin_login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
}

function clear_login_attempts(PDO $pdo): void
{
    $pdo->prepare('DELETE FROM admin_login_attempts WHERE ip = ?')->execute([client_ip()]);
}

/** Returns an error message, or '' when the password is acceptable. */
function password_problem(string $password, string $confirm): string
{
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    if ($password !== $confirm) {
        return 'The two passwords do not match.';
    }
    return '';
}

function valid_username(string $username): bool
{
    return (bool) preg_match('~^[A-Za-z0-9._-]{3,60}$~', $username);
}

/* ------------------------------------------------------------------ */
/*  CSRF, flash messages, request helpers                              */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    admin_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Your session expired or the form was submitted twice. Please go back, refresh the page and try again.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Trimmed POST string. */
function post(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    admin_session_start();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    admin_session_start();
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ------------------------------------------------------------------ */
/*  Image uploads                                                      */
/* ------------------------------------------------------------------ */

/**
 * Save an uploaded image into assets/img/uploads/.
 * Returns the stored path (e.g. "uploads/20261110-ab12cd34ef56.jpg"),
 * null when no file was chosen, or throws RuntimeException on a bad file.
 */
function handle_upload(string $field, string $folder = 'uploads'): ?string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]) || is_array($_FILES[$field]['error'])) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('That image is too large. Please keep images under 5 MB.');
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException('The image could not be uploaded (error ' . (int) $f['error'] . '). Please try again.');
    }

    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    $info  = @getimagesize($f['tmp_name']);
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Please upload a JPG, PNG, WebP or GIF image.');
    }

    $folder = preg_replace('~[^a-z0-9_-]~i', '', $folder) ?: 'uploads';
    $dir = APP_ROOT . '/assets/img/' . $folder;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException("The folder assets/img/{$folder} could not be created. Check folder permissions.");
    }
    // Every upload folder gets the "never run scripts here" rules
    if (!is_file($dir . '/.htaccess') && is_file(APP_ROOT . '/assets/img/uploads/.htaccess')) {
        @copy(APP_ROOT . '/assets/img/uploads/.htaccess', $dir . '/.htaccess');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException("The image could not be saved. Check that assets/img/{$folder} is writable.");
    }
    @chmod($dir . '/' . $name, 0644);
    return $folder . '/' . $name;
}

/* ------------------------------------------------------------------ */
/*  Form field helpers                                                 */
/* ------------------------------------------------------------------ */

function field_text(string $name, string $label, $value, array $o = []): string
{
    $type  = $o['type'] ?? 'text';
    $id    = $o['id'] ?? 'f_' . preg_replace('~[^a-z0-9_]+~i', '_', $name);
    $attrs = '';
    foreach (['maxlength', 'min', 'max', 'step', 'placeholder', 'autocomplete'] as $a) {
        if (isset($o[$a])) {
            $attrs .= ' ' . $a . '="' . e((string) $o[$a]) . '"';
        }
    }
    if (!empty($o['required'])) {
        $attrs .= ' required';
    }
    $html = '<div class="field"><label for="' . $id . '">' . e($label) . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . (int) ($o['rows'] ?? 4) . '"' . $attrs . '>' . e((string) $value) . '</textarea>';
    } else {
        $html .= '<input id="' . $id . '" type="' . e($type) . '" name="' . e($name) . '" value="' . e((string) $value) . '"' . $attrs . '>';
    }
    if (!empty($o['help'])) {
        $html .= '<p class="help">' . e($o['help']) . '</p>';
    }
    return $html . '</div>';
}

function field_image(string $name, string $label, ?string $current, string $help = '', string $id = ''): string
{
    $id   = $id !== '' ? $id : 'f_' . preg_replace('~[^a-z0-9_]+~i', '_', $name);
    $html = '<div class="field field--image"><label for="' . $id . '">' . e($label) . '</label><div class="image-picker">';
    $html .= '<div class="image-picker__preview">';
    $html .= $current ? '<img src="' . e(img_url($current)) . '" alt="">' : '<span>No image</span>';
    $html .= '</div><div class="image-picker__input">';
    $html .= '<input id="' . $id . '" type="file" name="' . e($name) . '" accept="image/jpeg,image/png,image/webp,image/gif">';
    $html .= '<p class="help">' . ($current ? 'Choose a new file to replace the current image. ' : '') . 'JPG, PNG, WebP or GIF, max 5 MB.'
        . ($help !== '' ? ' ' . e($help) : '') . '</p>';
    return $html . '</div></div></div>';
}

function field_checkbox(string $name, string $label, bool $checked, string $help = ''): string
{
    return '<div class="field field--check"><label><input type="checkbox" name="' . e($name) . '" value="1"'
        . ($checked ? ' checked' : '') . '> ' . e($label) . '</label>'
        . ($help !== '' ? '<p class="help">' . e($help) . '</p>' : '') . '</div>';
}

/** Basic sanity check for link fields: '#', '#anchor', relative paths, http(s), tel:, mailto:. */
function clean_link(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (preg_match('~^\s*(javascript|data|vbscript):~i', $url)) {
        throw new RuntimeException('That link is not allowed: ' . $url);
    }
    return $url;
}

function slugify(string $s): string
{
    $s = strtolower(str_replace('&', ' and ', trim($s)));
    $s = trim((string) preg_replace('~[^a-z0-9]+~', '-', $s), '-');
    return $s !== '' ? $s : 'item';
}

/* ------------------------------------------------------------------ */
/*  Settings forms (key/value rows in the `settings` table)            */
/* ------------------------------------------------------------------ */

/**
 * Process a POSTed settings form. Fields: key => [label, type, help] where
 * type is text | textarea | email | link | checkbox | image.
 * On success: saves, flashes $success (string, or callable receiving the saved
 * values) and redirects to $redirectTo. Otherwise returns the current values
 * (with anything typed kept when there was an error).
 */
function settings_form_handle(PDO $pdo, array $fields, string $redirectTo, $success): array
{
    $current = get_settings();
    if (!is_post()) {
        return $current;
    }
    verify_csrf();
    $input = is_array($_POST['s'] ?? null) ? $_POST['s'] : [];
    $save  = [];
    try {
        foreach ($fields as $key => [$label, $type]) {
            if ($type === 'image') {
                $uploaded = handle_upload('img_' . $key);
                if ($uploaded !== null) {
                    $save[$key] = $uploaded;
                }
                continue;
            }
            if ($type === 'checkbox') {
                $save[$key] = !empty($input[$key]) ? '1' : '0';
                continue;
            }
            $value = isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
            if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Please enter a valid email address.');
            }
            if ($type === 'link') {
                $value = clean_link($value);
            }
            $save[$key] = $value;
        }
        $st = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($save as $key => $value) {
            $st->execute([$key, $value]);
        }
        flash('success', is_callable($success) ? $success($save) : (string) $success);
        redirect($redirectTo);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        foreach ($fields as $key => [$label, $type]) {
            if ($type !== 'image' && isset($input[$key]) && is_string($input[$key])) {
                $current[$key] = $input[$key];
            }
        }
    }
    return $current;
}

/** Render a settings form panel. $extraActions is HTML placed next to the Save button. */
function settings_form_render(array $fields, array $current, string $title, string $extraActions = ''): void
{
    echo '<form method="post" enctype="multipart/form-data" class="panel">', csrf_field();
    echo '<h2 class="panel__title">', e($title), '</h2>';
    foreach ($fields as $key => [$label, $type, $help]) {
        if ($type === 'image') {
            echo field_image('img_' . $key, $label, $current[$key] ?? '', $help);
        } elseif ($type === 'checkbox') {
            echo field_checkbox('s[' . $key . ']', $label, ($current[$key] ?? '0') === '1', $help);
        } else {
            echo field_text('s[' . $key . ']', $label, $current[$key] ?? '', [
                'type' => $type === 'link' ? 'text' : $type,
                'help' => $help,
                'rows' => $type === 'textarea' ? (int) min(14, max(3, ceil(strlen((string) ($current[$key] ?? '')) / 95) + 1)) : 3,
            ]);
        }
    }
    echo '<div class="actions"><button class="btn btn--primary" type="submit">Save changes</button>', $extraActions, '</div>';
    echo '</form>';
}

/* ------------------------------------------------------------------ */
/*  Page layout                                                        */
/* ------------------------------------------------------------------ */

function admin_nav(): array
{
    return [
        'index'    => ['Dashboard',       'M3 12l9-8 9 8M5 10v10h5v-6h4v6h5V10'],
        'home'     => ['Home Page',       'M4 5h16v14H4zM4 9h16M8 13h8M8 16h5'],
        'about-page' => ['About Page',    'M12 8h.01M11 12h1v5h1M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z'],
        'menu-page' => ['Menu Page',      'M7 3v8m-2-8v4a2 2 0 0 0 4 0V3M7 11v10M16 3c-2 1-3 4-3 7h3v11'],
        'gallery-page' => ['Gallery Page', 'M4 5h16v14H4zM4 15l4-4 4 4 3-3 5 5M15 9h.01'],
        'messages' => ['Contact Page',    'M4 6h16v12H4zM4 7l8 6 8-6'],
        'settings' => ['Site Settings',   'M4 6h16M4 12h16M4 18h10'],
        'account'  => ['Account & Users', 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0'],
    ];
}

/** Home Page sections, in the order they appear on the website: key => [label, url]. */
function home_sections(): array
{
    return [
        'hero'         => ['Hero',            'home.php?section=hero'],
        'categories'   => ['Slider',          'categories.php'],
        'about'        => ['About',           'home.php?section=about'],
        'hours'        => ['Opening Hours',   'hours.php'],
        'showcase'     => ['Showcase Grid',   'showcase.php'],
        'reservation'  => ['Reservation',     'home.php?section=reservation'],
        'testimonials' => ['Testimonials',    'testimonials.php'],
    ];
}

/** Top bar of tabs shown on every Home Page section. */
function home_tabs(string $active): void
{
    echo '<nav class="tabs" aria-label="Home page sections">';
    foreach (home_sections() as $key => [$label, $url]) {
        echo '<a href="', e($url), '"', $key === $active ? ' aria-current="page"' : '', '>', e($label), '</a>';
    }
    echo '</nav>';
}

/** About Page sections, in the order they appear on the page: key => [label, url]. */
function about_sections(): array
{
    return [
        'header' => ['Title & Banner',       'about-page.php?section=header'],
        'intro'  => ['Intro',                'about-page.php?section=intro'],
        'story'  => ['Our Story',            'about-page.php?section=story'],
        'why'    => ['Why Choose Us',        'about-page.php?section=why'],
        'cards'  => ['Why Choose Us Cards',  'about-features.php'],
        'more'   => ['Reservation & SEO',    'about-page.php?section=more'],
    ];
}

function about_tabs(string $active): void
{
    echo '<nav class="tabs" aria-label="About page sections">';
    foreach (about_sections() as $key => [$label, $url]) {
        echo '<a href="', e($url), '"', $key === $active ? ' aria-current="page"' : '', '>', e($label), '</a>';
    }
    echo '</nav>';
}

/** "Why Choose" cards table — created automatically on first use. */
function ensure_about_features_table(PDO $pdo): void
{
    if ($pdo->query("SHOW TABLES LIKE 'about_features'")->fetch()) {
        return;
    }
    $defaults = get_about_features(); // table missing → the built-in default cards
    $pdo->exec("CREATE TABLE IF NOT EXISTS `about_features` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `title`      VARCHAR(100) NOT NULL,
        `text`       VARCHAR(400) NOT NULL DEFAULT '',
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    // Just created: copy in the default cards so the admin starts with what the website showed
    $st = $pdo->prepare('INSERT INTO about_features (title, text, sort_order) VALUES (?, ?, ?)');
    foreach ($defaults as $i => $f) {
        $st->execute([$f['title'], $f['text'], $i + 1]);
    }
}

/** Gallery Page tabs. */
function gallery_tabs(string $active): void
{
    $tabs = ['photos' => ['Photos', 'gallery-page.php'], 'page' => ['Title & SEO', 'gallery-page.php?tab=page']];
    echo '<nav class="tabs" aria-label="Gallery page sections">';
    foreach ($tabs as $key => [$label, $url]) {
        echo '<a href="', e($url), '"', $key === $active ? ' aria-current="page"' : '', '>', e($label), '</a>';
    }
    echo '</nav>';
}

/** Gallery table — created (and filled with the site's existing photos) the first time it's needed. */
function ensure_gallery_table(PDO $pdo): void
{
    if ($pdo->query("SHOW TABLES LIKE 'gallery_images'")->fetch()) {
        return;
    }
    $defaults = default_gallery_images();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `gallery_images` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `image`      VARCHAR(255) NOT NULL,
        `caption`    VARCHAR(200) NOT NULL DEFAULT '',
        `size`       ENUM('normal','wide','tall','big') NOT NULL DEFAULT 'normal',
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $st = $pdo->prepare('INSERT INTO gallery_images (image, caption, size, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($defaults as $i => $g) {
        $st->execute([$g['image'], $g['caption'], $g['size'], $i + 1]);
    }
}

/** Menu Page tabs. */
function menu_tabs(string $active): void
{
    $tabs = [
        'code'   => ['Menu Code',         'menu-page.php'],
        'images' => ['Menu Images',       'menu-page.php?tab=images'],
        'page'   => ['Title, Banner & SEO', 'menu-page.php?tab=page'],
    ];
    echo '<nav class="tabs" aria-label="Menu page sections">';
    foreach ($tabs as $key => [$label, $url]) {
        echo '<a href="', e($url), '"', $key === $active ? ' aria-current="page"' : '', '>', e($label), '</a>';
    }
    echo '</nav>';
}

/** settings.setting_value was TEXT (64 KB) — a pasted menu needs more room. Upgrade once to MEDIUMTEXT (16 MB). */
function ensure_settings_mediumtext(PDO $pdo): void
{
    $col = $pdo->query("SHOW COLUMNS FROM `settings` LIKE 'setting_value'")->fetch();
    if ($col && stripos((string) $col['Type'], 'mediumtext') === false && stripos((string) $col['Type'], 'longtext') === false) {
        $pdo->exec('ALTER TABLE `settings` MODIFY `setting_value` MEDIUMTEXT NOT NULL');
    }
}

/** Contact Page tabs. */
function contact_tabs(string $active): void
{
    $unread = unread_message_count();
    $tabs = [
        'messages' => ['Messages' . ($unread ? ' (' . $unread . ' new)' : ''), 'messages.php'],
        'content'  => ['Page Content', 'contact-page.php'],
    ];
    echo '<nav class="tabs" aria-label="Contact page sections">';
    foreach ($tabs as $key => [$label, $url]) {
        echo '<a href="', e($url), '"', $key === $active ? ' aria-current="page"' : '', '>', e($label), '</a>';
    }
    echo '</nav>';
}

/** Unread contact-form messages (0 if the table doesn't exist yet). */
function unread_message_count(): int
{
    static $n = null;
    if ($n === null) {
        try {
            $n = (int) admin_db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
        } catch (Throwable $ex) {
            $n = 0;
        }
    }
    return $n;
}

function admin_page_start(string $title, string $active): void
{
    $user = current_admin();
    require __DIR__ . '/layout_top.php';
}

function admin_page_end(): void
{
    require __DIR__ . '/layout_bottom.php';
}

/** Minimal centred layout for login / setup. */
function auth_page_start(string $title): void
{
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Chum Chum's Admin</title>
<link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
<link rel="stylesheet" href="assets/admin.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/admin.css') ?>">
</head>
<body class="auth">
<main class="auth__card">
  <img class="auth__logo" src="<?= e(asset('img/logo.png')) ?>" alt="Chum Chum's" width="120" height="94">
  <h1><?= e($title) ?></h1>
<?php
}

function auth_page_end(): void
{
    echo "</main>\n</body>\n</html>\n";
}

function render_flashes(): void
{
    foreach (take_flashes() as $f) {
        echo '<div class="alert alert--' . e($f['type']) . '" role="status">' . e($f['message']) . '</div>';
    }
}
