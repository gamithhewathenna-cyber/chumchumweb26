<?php
/**
 * Contact form: validation, spam protection, saving to `contact_messages`
 * and the e-mail notification. Used by /contact.php.
 *
 * Spam protection without CAPTCHAs:
 *  - honeypot field ("website") that people never see or fill in
 *  - signed timestamp: the form must be at least 3 s old and under 1 day old
 *  - max 5 messages per IP address per hour
 */

const CONTACT_MIN_SECONDS  = 3;
const CONTACT_MAX_AGE      = 86400;
const CONTACT_MAX_PER_HOUR = 5;

/** Create the messages table on first use (also in database/chumchums.sql). */
function ensure_contact_table(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(60)  NOT NULL,
        `last_name`  VARCHAR(60)  NOT NULL DEFAULT '',
        `phone`      VARCHAR(30)  NOT NULL DEFAULT '',
        `email`      VARCHAR(150) NOT NULL,
        `message`    TEXT         NOT NULL,
        `ip`         VARCHAR(45)  NOT NULL DEFAULT '',
        `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
        `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_read_created` (`is_read`, `created_at`),
        KEY `idx_ip_created` (`ip`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function contact_secret(): string
{
    return hash('sha256', DB_PASS . '|' . APP_ROOT . '|contact-form');
}

/** Hidden fields that go in the form: timestamp + signature. */
function contact_form_token(): array
{
    $ts = (string) time();
    return ['ts' => $ts, 'sig' => hash_hmac('sha256', $ts, contact_secret())];
}

/** Trimmed, length-limited POST value. */
function contact_input(string $key, int $max): string
{
    $v = $_POST[$key] ?? '';
    $v = is_string($v) ? trim(str_replace("\0", '', $v)) : '';
    return mb_substr($v, 0, $max);
}

/**
 * Handle a POSTed contact form.
 * Returns ['ok' => bool, 'errors' => [field => message], 'values' => [...], 'spam' => bool]
 */
function handle_contact_submission(array $s): array
{
    $values = [
        'first_name' => contact_input('first_name', 60),
        'last_name'  => contact_input('last_name', 60),
        'phone'      => contact_input('phone', 30),
        'email'      => contact_input('email', 150),
        'message'    => contact_input('message', 3000),
    ];
    $errors = [];

    // --- Bot checks (fail quietly: bots get a fake "sent" page, nothing is stored)
    $ts  = (string) ($_POST['ts'] ?? '');
    $sig = (string) ($_POST['sig'] ?? '');
    $age = ctype_digit($ts) ? time() - (int) $ts : -1;
    if (!empty($_POST['website'])) {
        return ['ok' => true, 'errors' => [], 'values' => [], 'spam' => true];
    }
    if (!hash_equals(hash_hmac('sha256', $ts, contact_secret()), $sig) || $age < 0 || $age > CONTACT_MAX_AGE) {
        $errors['form'] = 'This form has expired. Please check your message and press Send again.';
    } elseif ($age < CONTACT_MIN_SECONDS) {
        return ['ok' => true, 'errors' => [], 'values' => [], 'spam' => true];
    }

    // --- Field validation
    if ($values['first_name'] === '') {
        $errors['first_name'] = 'Please enter your first name.';
    }
    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($values['phone'] !== '' && !preg_match('~^[0-9+()\s.-]{6,30}$~', $values['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number (numbers, spaces, + and brackets only).';
    }
    if (mb_strlen($values['message']) < 5) {
        $errors['message'] = 'Please write a short message.';
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'values' => $values, 'spam' => false];
    }

    // --- Save
    $pdo = db();
    if (!$pdo) {
        return ['ok' => false, 'values' => $values, 'spam' => false,
                'errors' => ['form' => 'Sorry, we could not send your message right now. Please call or email us instead.']];
    }
    try {
        ensure_contact_table($pdo);
        $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
        $recent = $pdo->prepare('SELECT COUNT(*) FROM contact_messages WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
        $recent->execute([$ip]);
        if ((int) $recent->fetchColumn() >= CONTACT_MAX_PER_HOUR) {
            return ['ok' => false, 'values' => $values, 'spam' => false,
                    'errors' => ['form' => "You've sent several messages already — please wait a little or give us a call."]];
        }
        $pdo->prepare('INSERT INTO contact_messages (first_name, last_name, phone, email, message, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$values['first_name'], $values['last_name'], $values['phone'], $values['email'], $values['message'],
                       $ip, mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    } catch (Throwable $ex) {
        error_log('[ChumChums] contact form: ' . $ex->getMessage());
        return ['ok' => false, 'values' => $values, 'spam' => false,
                'errors' => ['form' => 'Sorry, we could not send your message right now. Please call or email us instead.']];
    }

    send_contact_notification($values, $s);
    return ['ok' => true, 'errors' => [], 'values' => [], 'spam' => false];
}

/** E-mail the restaurant about a new message (failures are logged; the message is already saved). */
function send_contact_notification(array $v, array $s): void
{
    if (($s['contact_notify_enabled'] ?? '1') !== '1') {
        return;
    }
    $to = trim((string) ($s['contact_notify_email'] ?? '')) ?: trim((string) ($s['email'] ?? ''));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $host = preg_replace('~^www\.~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $host = preg_replace('~[^a-z0-9.-]~i', '', $host) ?: 'localhost';
    $name = trim($v['first_name'] . ' ' . $v['last_name']);
    $clean = fn(string $x) => str_replace(["\r", "\n"], ' ', $x); // no header injection

    $subject = '=?UTF-8?B?' . base64_encode('New website message from ' . $name) . '?=';
    $body = "You have a new message from the Chum Chum's website contact form.\n\n"
        . "Name:   {$name}\n"
        . "Email:  {$v['email']}\n"
        . 'Phone:  ' . ($v['phone'] !== '' ? $v['phone'] : '—') . "\n\n"
        . "Message:\n{$v['message']}\n\n"
        . "—\nReply to this email to answer them directly. All messages are also saved in the website admin under Contact Page → Messages.\n";
    $headers = [
        'From: ' . $clean("Chum Chum's Website <noreply@{$host}>"),
        'Reply-To: ' . $clean($name !== '' ? "{$name} <{$v['email']}>" : $v['email']),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: ChumChums-Website',
    ];
    if (!@mail($to, $subject, $body, implode("\r\n", $headers))) {
        error_log('[ChumChums] contact form: notification email to ' . $to . ' could not be sent.');
    }
}
