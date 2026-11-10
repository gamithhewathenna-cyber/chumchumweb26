<?php
require __DIR__ . '/includes/admin.php';
$me  = require_login();
$pdo = admin_db();

$newUsername = '';

if (is_post()) {
    verify_csrf();
    $op = post('op');

    /* ---------- Change my password ---------- */
    if ($op === 'password') {
        $currentPw = (string) ($_POST['current_password'] ?? '');
        $password  = (string) ($_POST['password'] ?? '');
        $confirm   = (string) ($_POST['password_confirm'] ?? '');

        $st = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $st->execute([(int) $me['id']]);
        $hash = (string) $st->fetchColumn();

        if (!password_verify($currentPw, $hash)) {
            flash('error', 'Your current password is incorrect.');
        } elseif (($problem = password_problem($password, $confirm)) !== '') {
            flash('error', $problem);
        } else {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), (int) $me['id']]);
            session_regenerate_id(true);
            flash('success', 'Your password has been changed.');
        }
        redirect('account.php');
    }

    /* ---------- Add a user ---------- */
    if ($op === 'add_user') {
        $newUsername = post('username');
        $password    = (string) ($_POST['user_password'] ?? '');
        $confirm     = (string) ($_POST['user_password_confirm'] ?? '');

        $exists = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
        $exists->execute([$newUsername]);

        if (!valid_username($newUsername)) {
            flash('error', 'Username must be 3–60 characters: letters, numbers, dot, dash or underscore.');
        } elseif ((int) $exists->fetchColumn() > 0) {
            flash('error', 'That username is already taken.');
        } elseif (($problem = password_problem($password, $confirm)) !== '') {
            flash('error', $problem);
        } else {
            $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')
                ->execute([$newUsername, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'User "' . $newUsername . '" added. Share the password with them privately.');
            redirect('account.php');
        }
    }

    /* ---------- Remove a user ---------- */
    if ($op === 'delete_user') {
        $id = (int) post('id');
        if ($id === (int) $me['id']) {
            flash('error', "You can't remove your own account while logged in.");
        } else {
            $pdo->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$id]);
            flash('success', 'User removed.');
        }
        redirect('account.php');
    }
}

$users = $pdo->query('SELECT id, username, created_at, last_login_at FROM admin_users ORDER BY username')->fetchAll();

function fmt_date($value): string
{
    return $value ? date('j M Y, g:ia', strtotime((string) $value)) : 'Never';
}

admin_page_start('Account & Users', 'account');
?>
  <div class="grid-2">
    <form method="post" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="password">
      <h2 class="panel__title">Change your password</h2>
      <p class="muted small">Logged in as <strong><?= e($me['username']) ?></strong>.</p>
      <?= field_text('current_password', 'Current password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
      <?= field_text('password', 'New password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password',
          'help' => 'At least ' . PASSWORD_MIN_LENGTH . ' characters.']) ?>
      <?= field_text('password_confirm', 'Confirm new password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
      <div class="actions"><button class="btn btn--primary" type="submit">Change password</button></div>
    </form>

    <form method="post" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="add_user">
      <h2 class="panel__title">Add another admin</h2>
      <p class="muted small">Every admin can edit all website content and manage users.</p>
      <?= field_text('username', 'Username', $newUsername, ['required' => true, 'maxlength' => 60, 'autocomplete' => 'off']) ?>
      <?= field_text('user_password', 'Password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password',
          'help' => 'At least ' . PASSWORD_MIN_LENGTH . ' characters.']) ?>
      <?= field_text('user_password_confirm', 'Confirm password', '', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
      <div class="actions"><button class="btn btn--primary" type="submit">Add user</button></div>
    </form>
  </div>

  <section class="panel">
    <h2 class="panel__title">Admin users</h2>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Username</th><th>Created</th><th>Last login</th><th><span class="sr-only">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><strong><?= e($u['username']) ?></strong><?= (int) $u['id'] === (int) $me['id'] ? ' <span class="badge badge--on">You</span>' : '' ?></td>
            <td><?= e(fmt_date($u['created_at'])) ?></td>
            <td><?= e(fmt_date($u['last_login_at'])) ?></td>
            <td class="table__actions">
              <?php if ((int) $u['id'] !== (int) $me['id']): ?>
                <form method="post" data-confirm="Remove the admin user &quot;<?= e($u['username']) ?>&quot;?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="op" value="delete_user">
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <button class="btn btn--small btn--danger" type="submit">Remove</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php admin_page_end();
