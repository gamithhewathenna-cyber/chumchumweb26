<?php
/**
 * First-time setup: creates the first admin account.
 * Only works while there are no admin users. To prove ownership, the
 * person must enter the database password from config/config.php.
 */
require __DIR__ . '/includes/admin.php';

$pdo = admin_db();
ensure_admin_tables($pdo);

if (admin_count($pdo) > 0) {
    flash('info', 'Setup is already complete. Please log in.');
    redirect('login.php');
}

$username = '';
$error    = '';

if (is_post()) {
    verify_csrf();
    $key      = (string) ($_POST['setup_key'] ?? '');
    $username = post('username');
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    if (login_locked($pdo)) {
        $error = 'Too many failed attempts. Please wait ' . LOGIN_WINDOW_MIN . ' minutes and try again.';
    } elseif (!hash_equals((string) DB_PASS, $key)) {
        record_failed_login($pdo);
        $error = 'The setup key is incorrect. It is the database password (DB_PASS) in config/config.php.';
    } elseif (!valid_username($username)) {
        $error = 'Username must be 3–60 characters: letters, numbers, dot, dash or underscore.';
    } elseif (($problem = password_problem($password, $confirm)) !== '') {
        $error = $problem;
    } else {
        $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $id = (int) $pdo->lastInsertId();
        clear_login_attempts($pdo);
        login_user($pdo, ['id' => $id]);
        flash('success', 'Your admin account has been created. Welcome!');
        redirect('index.php');
    }
}

auth_page_start('Create admin account');
if ($error !== ''): ?>
  <div class="alert alert--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
  <p class="muted">This one-time step creates the first administrator. After that, this page is disabled.</p>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <?= field_text('setup_key', 'Setup key', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'off',
        'help' => 'Enter the database password (DB_PASS from config/config.php) to prove you own this site.']) ?>
    <?= field_text('username', 'Choose a username', $username, ['required' => true, 'autocomplete' => 'username', 'maxlength' => 60]) ?>
    <?= field_text('password', 'Choose a password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password',
        'help' => 'At least ' . PASSWORD_MIN_LENGTH . ' characters.']) ?>
    <?= field_text('password_confirm', 'Confirm password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
    <button class="btn btn--primary btn--block" type="submit">Create account</button>
  </form>
<?php auth_page_end();
