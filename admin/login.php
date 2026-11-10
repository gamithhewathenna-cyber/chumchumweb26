<?php
require __DIR__ . '/includes/admin.php';

$pdo = admin_db();
ensure_admin_tables($pdo);

if (admin_count($pdo) === 0) {
    redirect('setup.php');
}
if (current_admin()) {
    redirect('index.php');
}

$next     = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
$username = '';
$error    = '';

if (is_post()) {
    verify_csrf();
    $username = post('username');
    $password = (string) ($_POST['password'] ?? '');

    if (login_locked($pdo)) {
        $error = 'Too many failed attempts. Please wait ' . LOGIN_WINDOW_MIN . ' minutes and try again.';
    } else {
        $st = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
        $st->execute([$username]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]);
            }
            clear_login_attempts($pdo);
            login_user($pdo, $user);
            redirect($next);
        }
        record_failed_login($pdo);
        $error = 'Incorrect username or password.';
    }
}

auth_page_start('Log in');
render_flashes();
if ($error !== ''): ?>
  <div class="alert alert--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <?= field_text('username', 'Username', $username, ['required' => true, 'autocomplete' => 'username']) ?>
    <?= field_text('password', 'Password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
    <button class="btn btn--primary btn--block" type="submit">Log in</button>
  </form>
  <p class="auth__foot"><a href="<?= e(base_url('')) ?>">← Back to website</a></p>
<?php auth_page_end();
