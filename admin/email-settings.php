<?php
/**
 * Site Settings → Email (SMTP): how the website sends email (contact-form notifications).
 * Includes a "Send test email" that reports exactly what the mail server said.
 */
require __DIR__ . '/includes/admin.php';
require_once APP_ROOT . '/includes/mailer.php';
require_login();
$pdo = admin_db();
ensure_settings_mediumtext($pdo);

function email_save(PDO $pdo, string $key, string $value): void
{
    $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
        ->execute([$key, $value]);
}

$s       = get_settings();
$saved   = mail_config($s);
$hasPass = (string) ($s['smtp_password'] ?? '') !== '';
$passBad = $hasPass && $saved['password'] === null;
$test    = null;   // result of a test send
$errors  = [];

// Values shown in the form (what's saved, or what was just typed)
$form = [
    'smtp_enabled'     => $s['smtp_enabled'] ?? '0',
    'smtp_host'        => $s['smtp_host'] ?? '',
    'smtp_port'        => $s['smtp_port'] ?? '465',
    'smtp_encryption'  => $s['smtp_encryption'] ?? 'ssl',
    'smtp_username'    => $s['smtp_username'] ?? '',
    'smtp_verify_cert' => $s['smtp_verify_cert'] ?? '1',
    'smtp_from_email'  => $s['smtp_from_email'] ?? '',
    'smtp_from_name'   => $s['smtp_from_name'] ?? '',
    'smtp_to_email'    => trim((string) ($s['smtp_to_email'] ?? '')) ?: trim((string) ($s['contact_notify_email'] ?? '')) ?: (string) ($s['email'] ?? ''),
    'smtp_cc'          => $s['smtp_cc'] ?? '',
];

if (is_post()) {
    verify_csrf();
    $op = post('op');
    $in = [
        'smtp_enabled'     => isset($_POST['smtp_enabled']) ? '1' : '0',
        'smtp_host'        => strtolower(post('smtp_host')),
        'smtp_port'        => (string) (int) post('smtp_port'),
        'smtp_encryption'  => in_array(post('smtp_encryption'), ['ssl', 'tls', 'none'], true) ? post('smtp_encryption') : 'ssl',
        'smtp_username'    => post('smtp_username'),
        'smtp_verify_cert' => isset($_POST['smtp_verify_cert']) ? '1' : '0',
        'smtp_from_email'  => post('smtp_from_email'),
        'smtp_from_name'   => mb_substr(post('smtp_from_name'), 0, 100),
        'smtp_to_email'    => post('smtp_to_email'),
        'smtp_cc'          => post('smtp_cc'),
    ];
    $newPass = (string) ($_POST['smtp_password'] ?? '');   // empty = keep the saved one
    $form    = $in;

    // --- Validate
    if ($in['smtp_host'] !== '' && !preg_match('~^[a-z0-9.-]+$~', $in['smtp_host'])) {
        $errors[] = 'SMTP host should look like mail.chumchums.com.au (no http://).';
    }
    if ((int) $in['smtp_port'] < 1 || (int) $in['smtp_port'] > 65535) {
        $errors[] = 'SMTP port should be a number such as 465 (SSL) or 587 (TLS).';
    }
    if ($in['smtp_from_email'] !== '' && !filter_var($in['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Sender email address is not a valid email.';
    }
    if (!filter_var($in['smtp_to_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Recipient email address is required and must be a valid email.';
    }
    $ccAll = array_filter(preg_split('~[\s,;]+~', $in['smtp_cc']));
    $ccBad = array_filter($ccAll, fn($a) => !filter_var($a, FILTER_VALIDATE_EMAIL));
    if ($ccBad) {
        $errors[] = 'These CC addresses are not valid: ' . implode(', ', $ccBad);
    }
    $needsServer = $in['smtp_enabled'] === '1' || $op === 'test';
    if ($needsServer && ($in['smtp_host'] === '' || $in['smtp_username'] === '')) {
        $errors[] = $op === 'test' ? 'To send a test, fill in at least the SMTP host and username.' : 'To switch SMTP on, fill in at least the SMTP host and username.';
    }
    if ($needsServer && $newPass === '' && (!$hasPass || $passBad)) {
        $errors[] = 'Please enter the SMTP password.';
    }
    $in['smtp_cc'] = implode(', ', mail_parse_list($in['smtp_cc']));

    if (!$errors && $op === 'save') {
        foreach ($in as $k => $v) {
            email_save($pdo, $k, $v);
        }
        if ($newPass !== '') {
            email_save($pdo, 'smtp_password', mail_encrypt($newPass));
        }
        flash('success', 'Email settings saved.' . ($in['smtp_enabled'] === '1' ? ' Use "Send test email" to check everything works.' : ''));
        redirect('email-settings.php');
    }

    if (!$errors && $op === 'test') {
        // Test with what's in the form right now (saved password if the box was left empty) — nothing is saved
        $cfg = mail_config(array_merge($s, $in, ['smtp_password' => $newPass !== '' ? mail_encrypt($newPass) : ($s['smtp_password'] ?? '')]));
        $cfg['enabled'] = true;
        $only = post('test_to') === 'me';
        $when = date('j M Y, g:i a');
        $test = site_mail([
            'to'      => $only ? [$cfg['to'][0]] : $cfg['to'],
            'cc'      => $only ? [] : $cfg['cc'],
            'subject' => "Test email from the {$s['site_name']} website",
            'text'    => "This is a test email from your website ({$when}).\n\nIf you can read this, contact-form messages will be delivered.\n"
                . "Sent via {$cfg['host']}:{$cfg['port']} (" . strtoupper($cfg['encryption']) . ")\n"
                . 'To: ' . implode(', ', $cfg['to']) . ($cfg['cc'] && !$only ? "\nCC: " . implode(', ', $cfg['cc']) : '') . "\n",
            'html'    => '<div style="font-family:Arial,sans-serif;font-size:15px"><h2 style="color:#b9861a;margin:0 0 10px">It works! ✓</h2>'
                . '<p>This is a test email from your website (' . htmlspecialchars($when) . ').</p>'
                . '<p>If you can read this, contact-form messages will be delivered.</p>'
                . '<p style="color:#888;font-size:13px">Sent via ' . htmlspecialchars($cfg['host'] . ':' . $cfg['port']) . ' (' . strtoupper($cfg['encryption']) . ')</p></div>',
        ], $cfg);
        $test['sent_to'] = $only ? [$cfg['to'][0]] : array_merge($cfg['to'], $cfg['cc']);
    }
}

$usingSmtp = ($s['smtp_enabled'] ?? '0') === '1';
admin_page_start('Site Settings', 'settings');
settings_tabs_nav('email');
?>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error" role="alert"><?= e($err) ?></div>
  <?php endforeach; ?>

  <?php if ($test !== null): ?>
    <section class="panel test-result <?= $test['ok'] ? 'test-result--ok' : 'test-result--fail' ?>" role="status">
      <h2 class="panel__title"><?= $test['ok'] ? '✓ Test email sent' : '✗ Test email failed' ?></h2>
      <?php if ($test['ok']): ?>
        <p>The mail server accepted the email for:</p>
        <ul class="test-result__list">
          <?php foreach ($test['accepted'] as $a): ?><li>✓ <?= e($a) ?><?= in_array($a, mail_parse_list($form['smtp_cc']), true) ? ' <span class="badge">CC</span>' : '' ?></li><?php endforeach; ?>
          <?php foreach ($test['rejected'] as $a => $why): ?><li class="is-bad">✗ <?= e($a) ?> — <?= e($why) ?></li><?php endforeach; ?>
        </ul>
        <p class="muted small">Check those inboxes (and the Spam/Junk folder) in a minute or two. If the settings above aren't saved yet, press <strong>Save settings</strong>.</p>
      <?php else: ?>
        <p><strong><?= e($test['error']) ?></strong></p>
        <p class="muted small">Common fixes: SSL uses port <strong>465</strong>, TLS uses port <strong>587</strong>; the username is usually the full email address; check the password; if the error mentions a certificate, untick “Verify the server's security certificate”.</p>
      <?php endif; ?>
      <?php if (!empty($test['log'])): ?>
        <details class="test-result__log"><summary>Show the conversation with the mail server</summary>
          <pre><?= e(implode("\n", $test['log'])) ?></pre></details>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <form method="post" class="panel smtp-form" autocomplete="off">
    <?= csrf_field() ?>
    <h2 class="panel__title">Email (SMTP) settings</h2>
    <p class="muted">Contact-form messages are emailed using these settings. Find them in <strong>cPanel → Email Accounts → Connect Devices</strong> for your mailbox — usually host <code>mail.chumchums.com.au</code>, port <code>465</code>, SSL, and your full email address as the username.</p>

    <?= field_checkbox('smtp_enabled', 'Send emails through SMTP (recommended)', $form['smtp_enabled'] === '1',
        $usingSmtp ? 'SMTP is on.' : 'While this is off, the server\'s basic mail() is used — those emails often land in Spam or never arrive.') ?>

    <h3 class="smtp-form__group">Mail server</h3>
    <div class="field-row field-row--3">
      <?= field_text('smtp_host', 'SMTP Host', $form['smtp_host'], ['placeholder' => 'mail.chumchums.com.au', 'maxlength' => 190, 'autocomplete' => 'off']) ?>
      <?= field_text('smtp_port', 'SMTP Port', $form['smtp_port'], ['type' => 'number', 'min' => 1, 'max' => 65535, 'help' => '465 for SSL · 587 for TLS']) ?>
      <div class="field">
        <label for="f_smtp_encryption">Encryption Type</label>
        <select id="f_smtp_encryption" name="smtp_encryption" data-port-hint>
          <?php foreach (['ssl' => 'SSL (port 465)', 'tls' => 'TLS / STARTTLS (port 587)', 'none' => 'None (not recommended)'] as $k => $label): ?>
            <option value="<?= $k ?>"<?= $form['smtp_encryption'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field-row">
      <?= field_text('smtp_username', 'SMTP Username', $form['smtp_username'], ['placeholder' => 'info@chumchums.com.au', 'maxlength' => 190, 'autocomplete' => 'off', 'help' => 'Usually the full email address.']) ?>
      <?= field_text('smtp_password', 'SMTP Password', '', ['type' => 'password', 'autocomplete' => 'new-password',
          'placeholder' => $hasPass && !$passBad ? '•••••••• (saved)' : '',
          'help' => $passBad ? 'The saved password can no longer be read — please type it again.' : ($hasPass ? 'Leave empty to keep the saved password. It is stored encrypted.' : 'Stored encrypted; never shown again.')]) ?>
    </div>
    <?= field_checkbox('smtp_verify_cert', 'Verify the server\'s security certificate', $form['smtp_verify_cert'] === '1',
        'Leave ticked. Untick only if the test fails with a certificate error (some hosts use a certificate with a different name).') ?>

    <h3 class="smtp-form__group">Addresses</h3>
    <div class="field-row">
      <?= field_text('smtp_from_email', 'Sender Email Address', $form['smtp_from_email'], ['type' => 'email', 'placeholder' => 'info@chumchums.com.au', 'maxlength' => 190,
          'help' => 'The "From" address. Use the same mailbox as the SMTP username so emails aren\'t marked as spam.']) ?>
      <?= field_text('smtp_from_name', 'Sender Name', $form['smtp_from_name'], ['maxlength' => 100, 'placeholder' => "Chum Chum's Website"]) ?>
    </div>
    <?= field_text('smtp_to_email', 'Recipient Email Address', $form['smtp_to_email'], ['type' => 'email', 'required' => true, 'maxlength' => 190,
        'help' => 'Where contact-form messages are delivered.']) ?>
    <?= field_text('smtp_cc', 'CC Email Address(es)', $form['smtp_cc'], ['maxlength' => 1000, 'placeholder' => 'manager@example.com, owner@example.com',
        'help' => 'Optional. Separate several addresses with commas. Each gets a copy of every message.']) ?>

    <div class="actions">
      <button class="btn btn--primary" type="submit" name="op" value="save">Save settings</button>
      <span class="smtp-form__test">
        <button class="btn" type="submit" name="op" value="test">Send test email</button>
        <select name="test_to" aria-label="Who receives the test">
          <option value="all">to Recipient + CC</option>
          <option value="me">to Recipient only</option>
        </select>
      </span>
    </div>
    <p class="muted small">The test uses what's in this form right now (it doesn't save). Every contact-form message also records whether its email was sent — see <a href="messages.php">Contact Page → Messages</a>.</p>
  </form>
<?php admin_page_end();
