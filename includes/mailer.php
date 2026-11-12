<?php
/**
 * Email sending for the website — a small, dependency-free SMTP client.
 *
 * Settings come from Admin → Site Settings → Email (SMTP) and are stored in the
 * `settings` table (smtp_*). The SMTP password is stored encrypted.
 * If SMTP is switched off, PHP's built-in mail() is used instead.
 */

/* ------------------------------------------------------------------ */
/*  Secret storage for the SMTP password                               */
/* ------------------------------------------------------------------ */

/** 32-byte key kept in config/secret.key (created once, never in git). */
function mail_secret_key(): string
{
    $file = APP_ROOT . '/config/secret.key';
    if (is_file($file) && strlen($key = (string) @file_get_contents($file)) === 64 && ctype_xdigit($key)) {
        return hex2bin($key);
    }
    $key = bin2hex(random_bytes(32));
    if (@file_put_contents($file, $key, LOCK_EX) !== false) {
        @chmod($file, 0600);
        return hex2bin($key);
    }
    // Folder not writable: fall back to a key derived from this installation
    return hash('sha256', 'chum-smtp|' . APP_ROOT . '|' . (defined('DB_NAME') ? DB_NAME : ''), true);
}

function mail_encrypt(string $plain): string
{
    if ($plain === '') {
        return '';
    }
    if (function_exists('openssl_encrypt')) {
        $iv  = random_bytes(12);
        $tag = '';
        $ct  = openssl_encrypt($plain, 'aes-256-gcm', mail_secret_key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($ct !== false) {
            return 'enc1:' . base64_encode($iv . $tag . $ct);
        }
    }
    return 'b64:' . base64_encode($plain); // no OpenSSL on this server (rare)
}

/** Returns the plain password, or null if it can't be decrypted (e.g. the key file was lost). */
function mail_decrypt(string $stored): ?string
{
    if ($stored === '') {
        return '';
    }
    if (strpos($stored, 'enc1:') === 0 && function_exists('openssl_decrypt')) {
        $raw = base64_decode(substr($stored, 5), true);
        if ($raw === false || strlen($raw) < 29) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', mail_secret_key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }
    if (strpos($stored, 'b64:') === 0) {
        $plain = base64_decode(substr($stored, 4), true);
        return $plain === false ? null : $plain;
    }
    return null;
}

/* ------------------------------------------------------------------ */
/*  Settings                                                           */
/* ------------------------------------------------------------------ */

/** Split "a@x.com, b@y.com; c@z.com" into valid, unique addresses. */
function mail_parse_list(string $list): array
{
    $out = [];
    foreach (preg_split('~[\s,;]+~', $list) as $addr) {
        $addr = trim($addr);
        if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $out[strtolower($addr)] = $addr;
        }
    }
    return array_values($out);
}

/** Current email settings (password decrypted). */
function mail_config(?array $s = null): array
{
    $s = $s ?? get_settings();
    $to = trim((string) ($s['smtp_to_email'] ?? ''));
    if ($to === '') { // older installs: Contact Page notification address, then the restaurant email
        $to = trim((string) ($s['contact_notify_email'] ?? '')) ?: trim((string) ($s['email'] ?? ''));
    }
    return [
        'enabled'     => ($s['smtp_enabled'] ?? '0') === '1',
        'host'        => trim((string) ($s['smtp_host'] ?? '')),
        'port'        => (int) ($s['smtp_port'] ?? 465) ?: 465,
        'encryption'  => in_array($s['smtp_encryption'] ?? 'ssl', ['ssl', 'tls', 'none'], true) ? $s['smtp_encryption'] : 'ssl',
        'username'    => trim((string) ($s['smtp_username'] ?? '')),
        'password'    => mail_decrypt((string) ($s['smtp_password'] ?? '')),
        'verify_cert' => ($s['smtp_verify_cert'] ?? '1') === '1',
        'from_email'  => trim((string) ($s['smtp_from_email'] ?? '')),
        'from_name'   => trim((string) ($s['smtp_from_name'] ?? '')) ?: (string) ($s['site_name'] ?? 'Website'),
        'to'          => mail_parse_list($to),
        'cc'          => mail_parse_list((string) ($s['smtp_cc'] ?? '')),
    ];
}

/* ------------------------------------------------------------------ */
/*  Message building                                                   */
/* ------------------------------------------------------------------ */

function mail_header_value(string $v): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $v));
}

function mail_encode_name(string $name): string
{
    $name = mail_header_value($name);
    return preg_match('~^[\x20-\x7E]*$~', $name) ? '"' . addcslashes($name, '"\\') . '"' : '=?UTF-8?B?' . base64_encode($name) . '?=';
}

function mail_address(string $email, string $name = ''): string
{
    $email = mail_header_value($email);
    return $name === '' ? '<' . $email . '>' : mail_encode_name($name) . ' <' . $email . '>';
}

/**
 * Build the full RFC 5322 message (headers + multipart text/HTML body).
 * $m: from_email, from_name, to[], cc[], reply_to (email), reply_name, subject, text, html (optional)
 */
function mail_build(array $m): string
{
    $domain   = substr(strrchr($m['from_email'], '@') ?: '@localhost', 1);
    $boundary = '=_chum_' . bin2hex(random_bytes(8));
    $h = [
        'Date: ' . date('r'),
        'From: ' . mail_address($m['from_email'], $m['from_name'] ?? ''),
        'To: ' . implode(', ', array_map('mail_address', $m['to'])),
    ];
    if (!empty($m['cc'])) {
        $h[] = 'Cc: ' . implode(', ', array_map('mail_address', $m['cc']));
    }
    if (!empty($m['reply_to'])) {
        $h[] = 'Reply-To: ' . mail_address($m['reply_to'], $m['reply_name'] ?? '');
    }
    $h[] = 'Subject: =?UTF-8?B?' . base64_encode(mail_header_value($m['subject'])) . '?=';
    $h[] = 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>';
    $h[] = 'MIME-Version: 1.0';
    $h[] = 'X-Mailer: ChumChums-Website';

    $text = chunk_split(base64_encode(str_replace("\r\n", "\n", $m['text'])));
    if (empty($m['html'])) {
        $h[] = 'Content-Type: text/plain; charset=UTF-8';
        $h[] = 'Content-Transfer-Encoding: base64';
        return implode("\r\n", $h) . "\r\n\r\n" . $text;
    }
    $h[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
    return implode("\r\n", $h) . "\r\n\r\n"
        . "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . $text
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($m['html']))
        . "--$boundary--\r\n";
}

/* ------------------------------------------------------------------ */
/*  SMTP                                                               */
/* ------------------------------------------------------------------ */

/**
 * Send a message through SMTP.
 * Returns ['ok' => bool, 'error' => string, 'accepted' => [...], 'rejected' => [...], 'log' => [...]]
 * The log is the SMTP conversation with the password hidden — shown by the admin's Test Email.
 */
function smtp_send(array $cfg, array $m): array
{
    $log = [];
    $res = ['ok' => false, 'error' => '', 'accepted' => [], 'rejected' => [], 'log' => &$log];
    $fp  = null;

    $say = function (string $line, bool $secret = false) use (&$fp, &$log) {
        $log[] = 'C: ' . ($secret ? '•••••• (hidden)' : $line);
        fwrite($fp, $line . "\r\n");
    };
    $read = function () use (&$fp, &$log): array {
        $text = '';
        $code = 0;
        while (($line = fgets($fp, 1024)) !== false) {
            $text .= $line;
            $log[] = 'S: ' . rtrim($line);
            if (strlen($line) >= 4 && $line[3] === ' ') { // last line of the reply ("250 OK", not "250-...")
                $code = (int) substr($line, 0, 3);
                break;
            }
            if (strlen($line) < 4) {
                $code = (int) substr($line, 0, 3);
                break;
            }
        }
        if ($code === 0) {
            $meta = stream_get_meta_data($fp);
            throw new RuntimeException(!empty($meta['timed_out']) ? 'The mail server stopped responding (timed out).' : 'The mail server closed the connection.');
        }
        return [$code, $text];
    };
    $expect = function (array $want, string $what) use ($read): string {
        [$code, $text] = $read();
        if (!in_array($code, $want, true)) {
            throw new RuntimeException($what . ' failed: ' . trim(preg_replace('~^\d{3}[ -]~m', '', $text)) . " (code $code)");
        }
        return $text;
    };

    try {
        if ($cfg['host'] === '') {
            throw new RuntimeException('SMTP host is not set.');
        }
        if (empty($m['to'])) {
            throw new RuntimeException('There is no recipient email address.');
        }
        $ctx = stream_context_create(['ssl' => [
            'verify_peer'       => $cfg['verify_cert'],
            'verify_peer_name'  => $cfg['verify_cert'],
            'allow_self_signed' => !$cfg['verify_cert'],
            'peer_name'         => $cfg['host'],
            'SNI_enabled'       => true,
        ]]);
        $target = ($cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://') . $cfg['host'] . ':' . $cfg['port'];
        $log[] = '— connecting to ' . $target;
        $fp = @stream_socket_client($target, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            if ($cfg['encryption'] === 'ssl' && $errno === 0) {
                // TCP worked but the SSL handshake failed — almost always the certificate check
                throw new RuntimeException("Connected to {$cfg['host']}:{$cfg['port']} but could not start a secure (SSL) connection"
                    . ($cfg['verify_cert'] ? ' — the server\'s security certificate could not be verified. Check the host name matches the certificate (often mail.yourdomain), or untick "Verify the server\'s security certificate".' : '. Check that port ' . $cfg['port'] . ' uses SSL (usually 465).'));
            }
            throw new RuntimeException("Could not connect to {$cfg['host']}:{$cfg['port']} — " . ($errstr ?: 'connection refused or blocked') . '. Check the host and port, and that your hosting allows outgoing mail on this port.');
        }
        stream_set_timeout($fp, 20);
        $expect([220], 'Connecting');

        $helo = preg_replace('~[^a-z0-9.-]~i', '', (string) ($_SERVER['SERVER_NAME'] ?? '')) ?: 'localhost';
        $say('EHLO ' . $helo);
        $caps = $expect([250], 'EHLO');

        if ($cfg['encryption'] === 'tls') {
            $say('STARTTLS');
            $expect([220], 'STARTTLS');
            $method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $method |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
            if (!@stream_socket_enable_crypto($fp, true, $method)) {
                throw new RuntimeException('Could not start a secure (TLS) connection. Try "SSL" on port 465, or untick "Verify the server\'s security certificate".');
            }
            $log[] = '— secure connection started';
            $say('EHLO ' . $helo);
            $caps = $expect([250], 'EHLO');
        }

        if ($cfg['username'] !== '') {
            if ($cfg['password'] === null) {
                throw new RuntimeException('The saved SMTP password could not be read — please enter it again and save.');
            }
            if (stripos($caps, 'AUTH') === false && $cfg['encryption'] === 'none') {
                throw new RuntimeException('The server only allows logging in over a secure connection — choose SSL or TLS.');
            }
            if (preg_match('~AUTH[ =][^\r\n]*\bLOGIN\b~i', $caps) || !preg_match('~AUTH[ =][^\r\n]*\bPLAIN\b~i', $caps)) {
                $say('AUTH LOGIN');
                $expect([334], 'Login');
                $say(base64_encode($cfg['username']), true);
                $expect([334], 'Login (username)');
                $say(base64_encode((string) $cfg['password']), true);
                $expect([235], 'Login (username/password)');
            } else {
                $say('AUTH PLAIN ' . base64_encode("\0" . $cfg['username'] . "\0" . $cfg['password']), true);
                $expect([235], 'Login (username/password)');
            }
        }

        $say('MAIL FROM:<' . mail_header_value($m['from_email']) . '>');
        $expect([250], 'Sender address');
        foreach (array_merge($m['to'], $m['cc'] ?? []) as $rcpt) {
            $say('RCPT TO:<' . mail_header_value($rcpt) . '>');
            [$code, $text] = $read();
            if ($code === 250 || $code === 251) {
                $res['accepted'][] = $rcpt;
            } else {
                $res['rejected'][$rcpt] = trim(preg_replace('~^\d{3}[ -]~m', '', $text)) . " (code $code)";
            }
        }
        if (!$res['accepted']) {
            throw new RuntimeException('The mail server refused every recipient: ' . implode('; ', array_map(fn($k, $v) => "$k — $v", array_keys($res['rejected']), $res['rejected'])));
        }
        $say('DATA');
        $expect([354], 'Sending the message');
        $data = preg_replace('~^\.~m', '..', mail_build($m)); // dot-stuffing
        fwrite($fp, rtrim($data, "\r\n") . "\r\n.\r\n");
        $log[] = 'C: (message, ' . strlen($data) . ' bytes)';
        $expect([250], 'Sending the message');
        $say('QUIT');
        @fgets($fp, 512);
        $res['ok'] = true;
    } catch (Throwable $ex) {
        $res['error'] = $ex->getMessage();
        $log[] = '— ' . $res['error'];
    } finally {
        if (is_resource($fp)) {
            fclose($fp);
        }
    }
    unset($res['log']);
    $res['log'] = $log;
    return $res;
}

/**
 * Send an email using the site's settings: SMTP when switched on, otherwise PHP mail().
 * $m: subject, text, html (optional), reply_to, reply_name, to[] / cc[] (optional — default from settings)
 */
function site_mail(array $m, ?array $cfg = null): array
{
    $cfg = $cfg ?? mail_config();
    $host = preg_replace('~^www\.~i', '', preg_replace('~[^a-z0-9.-]~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')));
    $m += ['to' => $cfg['to'], 'cc' => $cfg['cc'], 'from_name' => $cfg['from_name']];
    $m['from_email'] = $cfg['from_email'] !== '' ? $cfg['from_email'] : ($cfg['username'] !== '' && filter_var($cfg['username'], FILTER_VALIDATE_EMAIL) ? $cfg['username'] : 'noreply@' . $host);

    if ($cfg['enabled']) {
        return smtp_send($cfg, $m);
    }
    // SMTP off → the server's own mail()
    if (empty($m['to'])) {
        return ['ok' => false, 'error' => 'There is no recipient email address.', 'accepted' => [], 'rejected' => [], 'log' => []];
    }
    $full    = mail_build($m);
    [$head, $body] = explode("\r\n\r\n", $full, 2);
    $head    = preg_replace('~^(To|Subject): .*\r\n~mi', '', $head . "\r\n");
    $ok = @mail(implode(', ', $m['to']), '=?UTF-8?B?' . base64_encode(mail_header_value($m['subject'])) . '?=', $body, rtrim($head));
    return ['ok' => $ok, 'error' => $ok ? '' : 'PHP mail() could not send the email — switch on SMTP in Site Settings → Email (SMTP).',
            'accepted' => $ok ? array_merge($m['to'], $m['cc']) : [], 'rejected' => [], 'log' => []];
}
