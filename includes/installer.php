<?php
/**
 * One-time setup: shown only while config/config.php is missing on the server.
 * The site owner enters the database details in the browser; they're checked, then
 * config/config.php is written on the server. The password never goes into git.
 *
 * Safety:
 *  - only a database on this same server (host is fixed to localhost)
 *  - the database must already contain this website's tables (settings, menu_categories…)
 *  - disappears for good as soon as config/config.php exists
 */

/** Write config/config.php. Returns '' on success or an error message. */
function installer_write_config(string $file, array $c): string
{
    $q = fn($v) => var_export((string) $v, true);
    $php = "<?php\n/**\n * Chum Chum's — site configuration (lives only on the server, never in git).\n * Written by the one-time setup on " . date('Y-m-d H:i') . ".\n */\n\n"
        . "define('DB_HOST', 'localhost');\n"
        . "define('DB_NAME', " . $q($c['name']) . ");\n"
        . "define('DB_USER', " . $q($c['user']) . ");\n"
        . "define('DB_PASS', " . $q($c['pass']) . ");\n"
        . "define('DB_CHARSET', 'utf8mb4');\n\n"
        . "/* The live website address. */\n"
        . "define('SITE_URL', " . $q($c['url']) . ");\n\n"
        . "/* Keep false on the live site — true shows PHP errors on screen. */\n"
        . "define('APP_DEBUG', false);\n\n"
        . "date_default_timezone_set('Australia/Melbourne');\n";
    if (!is_dir(dirname($file)) || !is_writable(dirname($file))) {
        return 'The config folder is not writable on the server. Create config/config.php in cPanel → File Manager instead.';
    }
    if (@file_put_contents($file, $php, LOCK_EX) === false) {
        return 'Could not write config/config.php. Create it in cPanel → File Manager instead.';
    }
    @chmod($file, 0640);
    return '';
}

function installer_run(string $configFile): void
{
    $values = ['name' => '', 'user' => '', 'url' => 'https://www.chumchums.com.au'];
    $error  = '';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['chum_install'])) {
        $values = [
            'name' => trim((string) ($_POST['db_name'] ?? '')),
            'user' => trim((string) ($_POST['db_user'] ?? '')),
            'pass' => (string) ($_POST['db_pass'] ?? ''),
            'url'  => rtrim(trim((string) ($_POST['site_url'] ?? '')), '/'),
        ];
        if (!preg_match('~^[A-Za-z0-9_]{1,64}$~', $values['name']) || !preg_match('~^[A-Za-z0-9_]{1,64}$~', $values['user'])) {
            $error = 'Database name and user may only contain letters, numbers and underscores.';
        } elseif ($values['url'] !== '' && !preg_match('~^https?://[a-z0-9.-]+$~i', $values['url'])) {
            $error = 'Website address should look like https://www.chumchums.com.au';
        } else {
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=' . $values['name'] . ';charset=utf8mb4', $values['user'], $values['pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
                $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('settings', $tables, true) || !in_array('menu_categories', $tables, true)) {
                    $error = 'Connected, but this database does not contain the website\'s tables. Check the database name.';
                } else {
                    $error = installer_write_config($configFile, $values);
                    if ($error === '') {
                        error_log('[ChumChums] config/config.php created by the one-time setup.');
                        header('Location: ' . strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?'), true, 303);
                        exit;
                    }
                }
            } catch (Throwable $ex) {
                sleep(2); // slow down guessing
                $error = 'Could not connect with those details — please check the database name, user and password.';
            }
        }
        unset($values['pass']);
    }

    http_response_code(503);
    header('Retry-After: 300');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex');
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    ?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Website setup</title>
<style>
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; background: #0d0d0d; color: #222; font: 15px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
  .card { width: 100%; max-width: 440px; background: #fff; border-radius: 14px; padding: 30px 26px; }
  h1 { margin: 0 0 6px; font-size: 22px; } p { margin: 0 0 18px; color: #555; }
  label { display: block; margin: 14px 0 6px; font-weight: 600; }
  input { width: 100%; box-sizing: border-box; padding: 10px 12px; font: inherit; border: 1px solid #ccc; border-radius: 8px; }
  input:focus { outline: 2px solid #e7af27; border-color: #e7af27; }
  button { margin-top: 20px; width: 100%; padding: 12px; border: 0; border-radius: 999px; background: #e7af27; color: #111; font: inherit; font-weight: 700; cursor: pointer; }
  .err { padding: 10px 14px; border-radius: 8px; background: #fdf0ee; color: #8e2a1f; border: 1px solid #f1c4bd; }
  .help { margin: 6px 0 0; font-size: 13px; color: #777; }
</style></head>
<body><main class="card">
  <h1>Finish setting up the website</h1>
  <p>The database settings file is missing on the server. Enter the database details from cPanel → MySQL® Databases. They're checked, then saved on the server only.</p>
  <?php if ($error !== ''): ?><p class="err" role="alert"><?= $e($error) ?></p><?php endif; ?>
  <form method="post" autocomplete="off">
    <input type="hidden" name="chum_install" value="1">
    <label for="db_name">Database name</label>
    <input id="db_name" name="db_name" value="<?= $e($values['name']) ?>" required placeholder="e.g. cpuser_chumchums">
    <label for="db_user">Database user</label>
    <input id="db_user" name="db_user" value="<?= $e($values['user']) ?>" required>
    <label for="db_pass">Database password</label>
    <input id="db_pass" name="db_pass" type="password" required autocomplete="new-password">
    <label for="site_url">Website address</label>
    <input id="site_url" name="site_url" value="<?= $e($values['url']) ?>">
    <p class="help">Usually https://www.chumchums.com.au</p>
    <button type="submit">Save &amp; open the website</button>
  </form>
</main></body></html>
<?php
    exit;
}
