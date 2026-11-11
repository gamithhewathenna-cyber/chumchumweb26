<?php
/**
 * Menu Page: paste the menu as one block of HTML with its CSS (live preview + one-step undo),
 * upload images to use in it, and edit the page title / banner / SEO.
 */
require __DIR__ . '/includes/admin.php';
require_once APP_ROOT . '/includes/menu.php';
require_login();
$pdo = admin_db();

$tab = (string) ($_GET['tab'] ?? 'code');
$tab = in_array($tab, ['code', 'images', 'page'], true) ? $tab : 'code';
$imgDir = APP_ROOT . '/assets/img/menu';

function save_setting(PDO $pdo, string $key, string $value): void
{
    $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')->execute([$key, $value]);
}

/** Uploaded menu images, newest first. */
function menu_images(string $dir): array
{
    $files = glob($dir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
    return array_map('basename', $files);
}

/* ======================= Title, banner & SEO (standard settings form) ======================= */
if ($tab === 'page') {
    $fields = [
        'menu_title'            => ['Page title', 'text', 'The big heading at the top, e.g. "Menu" or "Our Menu".'],
        'menu_intro'            => ['Intro text', 'textarea', 'The short paragraph beside the title.'],
        'menu_banner'           => ['Banner photo (optional)', 'image', 'Wide photo under the title, about 2400 × 680 px. Leave empty for no banner.'],
        'menu_empty_text'       => ['Message when no menu is pasted yet', 'textarea', 'Shown with your phone number until you paste a menu.'],
        'menu_meta_description' => ['Meta description', 'textarea', 'Shown under the page title in Google results. Aim for 120–160 characters.'],
    ];
    if (is_post() && post('op') === 'remove_banner') {
        verify_csrf();
        save_setting($pdo, 'menu_banner', '');
        flash('success', 'Banner removed.');
        redirect('menu-page.php?tab=page');
    }
    $current = settings_form_handle($pdo, $fields, 'menu-page.php?tab=page', 'Menu page settings saved.');
    admin_page_start('Menu Page', 'menu-page');
    menu_tabs('page');
    settings_form_render($fields, $current, 'Title, Banner & SEO',
        '<a class="btn btn--ghost" href="' . e(base_url('menu.php')) . '" target="_blank" rel="noopener">View menu page ↗</a>');
    if (trim((string) $current['menu_banner']) !== '') {
        echo '<form method="post" data-confirm="Remove the banner photo from the menu page?">', csrf_field(),
             '<input type="hidden" name="op" value="remove_banner"><button class="btn btn--small btn--danger" type="submit">Remove banner photo</button></form>';
    }
    admin_page_end();
    exit;
}

/* ======================= Images ======================= */
if ($tab === 'images') {
    if (is_post()) {
        verify_csrf();
        if (post('op') === 'upload') {
            $files = $_FILES['images'] ?? null;
            $ok = 0; $errors = [];
            if ($files && is_array($files['name'])) {
                foreach ($files['name'] as $i => $name) {
                    $_FILES['one'] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i],
                                      'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    try {
                        if (handle_upload('one', 'menu') !== null) {
                            $ok++;
                        }
                    } catch (RuntimeException $ex) {
                        $errors[] = $name . ': ' . $ex->getMessage();
                    }
                }
            }
            if ($ok) {
                flash('success', $ok . ' image' . ($ok === 1 ? '' : 's') . ' uploaded. Copy the address below into your menu HTML.');
            }
            foreach ($errors as $err) {
                flash('error', $err);
            }
            if (!$ok && !$errors) {
                flash('error', 'Please choose at least one image.');
            }
        }
        if (post('op') === 'delete_image') {
            $name = basename(post('name'));
            if (preg_match('~^[\w.-]+\.(jpe?g|png|webp|gif)$~i', $name) && is_file($imgDir . '/' . $name)) {
                @unlink($imgDir . '/' . $name);
                flash('success', 'Image deleted. Remember to remove it from your menu HTML too.');
            }
        }
        redirect('menu-page.php?tab=images');
    }

    $images = is_dir($imgDir) ? menu_images($imgDir) : [];
    admin_page_start('Menu Page', 'menu-page');
    menu_tabs('images');
    ?>
    <form method="post" enctype="multipart/form-data" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="upload">
      <h2 class="panel__title">Upload images for your menu</h2>
      <p class="muted small">Images in pasted HTML need a real web address. Upload them here, then press <strong>Copy address</strong> and paste it into your HTML, e.g. <code>&lt;img src="…"&gt;</code>. JPG, PNG, WebP or GIF, max 5 MB each.</p>
      <div class="field"><label for="f_images">Choose images</label>
        <input id="f_images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required></div>
      <div class="actions"><button class="btn btn--primary" type="submit">Upload</button></div>
    </form>

    <?php if (!$images): ?>
      <div class="panel empty">No menu images uploaded yet.</div>
    <?php else: ?>
      <div class="media-grid">
        <?php foreach ($images as $name): $url = base_url('assets/img/menu/' . $name); ?>
          <figure class="media-item">
            <img src="<?= e($url) ?>" alt="" loading="lazy">
            <figcaption>
              <input class="media-item__url" type="text" value="<?= e($url) ?>" readonly aria-label="Image address">
              <div class="media-item__actions">
                <button class="btn btn--small btn--primary" type="button" data-copy="<?= e($url) ?>">Copy address</button>
                <button class="btn btn--small" type="button" data-copy="<?= e('<img src="' . $url . '" alt="">') ?>">Copy &lt;img&gt;</button>
                <form method="post" data-confirm="Delete this image? If your menu uses it, it will show as broken.">
                  <?= csrf_field() ?><input type="hidden" name="op" value="delete_image"><input type="hidden" name="name" value="<?= e($name) ?>">
                  <button class="btn btn--small btn--danger" type="submit">Delete</button>
                </form>
              </div>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php
    admin_page_end();
    exit;
}

/* ======================= Menu code (one box: HTML with its CSS in <style>) ======================= */
ensure_settings_mediumtext($pdo);
$s = get_settings();
// Older saves kept CSS in a separate field — fold it into the single box
$code = menu_combine((string) ($s['menu_html'] ?? ''), (string) ($s['menu_css'] ?? ''));

if (is_post()) {
    verify_csrf();
    $op = post('op');

    if ($op === 'save_code') {
        $new = str_replace("\r\n", "\n", (string) ($_POST['menu_code'] ?? ''));
        if (!mb_check_encoding($new, 'UTF-8')) {
            // e.g. text pasted from an old Windows program — replace the few broken characters instead of failing
            $new = mb_convert_encoding($new, 'UTF-8', 'UTF-8');
        }
        if (strlen($new) > MENU_MAX_BYTES) {
            flash('error', 'That is too large (max 2 MB). If your code contains pasted images ("data:image…"), upload them in the Menu Images tab instead.');
            $code = $new;
        } else {
            if ($new !== $code) {
                try {
                    $pdo->beginTransaction();
                    // Keep the previous version so it can be restored
                    save_setting($pdo, 'menu_html_backup', $code);
                    save_setting($pdo, 'menu_css_backup', '');
                    save_setting($pdo, 'menu_backup_at', date('Y-m-d H:i:s'));
                    save_setting($pdo, 'menu_html', trim($new) === '' ? '' : $new);
                    save_setting($pdo, 'menu_css', '');
                    $pdo->commit();
                    flash('success', 'Menu saved — it is live on the website now.');
                } catch (Throwable $ex) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('[ChumChums] menu save: ' . $ex->getMessage());
                    flash('error', 'The menu could not be saved (' . $ex->getMessage() . '). Your previous menu is still live.');
                }
            } else {
                flash('info', 'No changes to save.');
            }
            redirect('menu-page.php');
        }
    }

    if ($op === 'restore') {
        if (!isset($s['menu_backup_at'])) {
            flash('error', 'There is no previous version to restore.');
        } else {
            // Swap current ⇄ previous, so a restore can itself be undone
            $previous = menu_combine((string) $s['menu_html_backup'], (string) ($s['menu_css_backup'] ?? ''));
            save_setting($pdo, 'menu_html', $previous);
            save_setting($pdo, 'menu_css', '');
            save_setting($pdo, 'menu_html_backup', $code);
            save_setting($pdo, 'menu_css_backup', '');
            save_setting($pdo, 'menu_backup_at', date('Y-m-d H:i:s'));
            flash('success', 'Previous version restored. (Press Restore again to switch back.)');
        }
        redirect('menu-page.php');
    }
}

$example  = menu_example();
$backupAt = $s['menu_backup_at'] ?? null;
$fontUrl  = asset('fonts/dm-sans-latin-opsz-normal.woff2');

admin_page_start('Menu Page', 'menu-page');
menu_tabs('code');
?>
  <div class="panel tips">
    <h2 class="panel__title">How to add your menu</h2>
    <ol>
      <li>Copy your menu's code — the HTML <strong>with its CSS</strong> in <code>&lt;style&gt;…&lt;/style&gt;</code> — and paste it all into the box below. A whole HTML page works too, <strong>including menus built with JavaScript</strong> (tabs, categories…).</li>
      <li>Check the <strong>live preview</strong> underneath, then press <strong>Save menu</strong>. It goes live straight away.</li>
      <li>The menu runs in a sealed frame: its CSS and scripts only affect the menu — they can't change or read the rest of the website.</li>
      <li>For pictures, upload them in <a href="menu-page.php?tab=images">Menu Images</a> and use the copied address.</li>
    </ol>
  </div>

  <form method="post" class="code-form" id="menu-code-form" data-font="<?= e($fontUrl) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="save_code">
    <div class="code-field">
      <div class="code-field__head">
        <label for="menu_code">Menu code <span class="muted small">(HTML + CSS)</span></label>
        <span class="code-field__meta" data-count-for="menu_code"></span>
      </div>
      <textarea class="code-editor" id="menu_code" name="menu_code" spellcheck="false" autocapitalize="off" autocomplete="off" wrap="off"
                placeholder="&lt;style&gt;&#10;  .menu { … }&#10;&lt;/style&gt;&#10;&#10;&lt;div class=&quot;menu&quot;&gt;…&lt;/div&gt;"><?= e($code) ?></textarea>
    </div>

    <div class="code-actions">
      <button class="btn btn--primary" type="submit">Save menu</button>
      <a class="btn btn--ghost" href="<?= e(base_url('menu.php')) ?>" target="_blank" rel="noopener">View menu page ↗</a>
      <button class="btn btn--ghost" type="button" data-insert-example>Insert example</button>
      <span class="muted small code-actions__hint">Tip: the Tab key indents · Ctrl/⌘ + S saves</span>
    </div>

    <div class="preview">
      <div class="preview__bar">
        <strong>Live preview</strong>
        <div class="preview__sizes" role="group" aria-label="Preview width">
          <button type="button" class="is-on" data-preview-width="100%">Desktop</button>
          <button type="button" data-preview-width="768px">Tablet</button>
          <button type="button" data-preview-width="390px">Phone</button>
        </div>
      </div>
      <div class="preview__frame-wrap">
        <iframe class="preview__frame" title="Menu preview" sandbox="<?= MENU_SANDBOX ?>" referrerpolicy="no-referrer"></iframe>
      </div>
    </div>
  </form>

  <?php if ($backupAt): ?>
    <form method="post" class="panel restore" data-confirm="Swap the menu back to the version saved before <?= e(date('j M Y, g:ia', strtotime($backupAt))) ?>? (You can switch back again.)">
      <?= csrf_field() ?><input type="hidden" name="op" value="restore">
      <p><strong>Made a mistake?</strong> The previous version (replaced <?= e(date('j M Y, g:ia', strtotime($backupAt))) ?>) is kept.</p>
      <button class="btn btn--small" type="submit">Restore previous version</button>
    </form>
  <?php endif; ?>

  <script type="application/json" id="menu-example"><?= json_encode(['code' => $example], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?></script>
  <?php
  // The preview builds the same sealed page as the website (includes/menu.php → menu_document()),
  // using the very same defaults and resize script.
  $marker = '%%CHUM_MENU_CODE%%';
  $frameParts = [
      'defaults' => menu_frame_defaults(),
      'resize'   => menu_resize_script(),
      'fragment' => menu_document($marker), // wrapper used when just a fragment is pasted
      'marker'   => $marker,
  ];
  ?>
  <script type="application/json" id="menu-frame-parts"><?= json_encode($frameParts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?></script>
<?php admin_page_end();
