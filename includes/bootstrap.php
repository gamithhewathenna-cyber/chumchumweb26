<?php
/**
 * Bootstrap: config, error handling, DB connection and helpers.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/config.php';

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

/**
 * Shared PDO connection. Returns null when the database is unreachable,
 * so the site still renders using the default content.
 */
function db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Throwable $ex) {
        error_log('[ChumChums] DB connection failed: ' . $ex->getMessage());
        $pdo = null;
    }
    return $pdo;
}

/** HTML-escape output. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Base URL of the site (no trailing slash). */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        if (SITE_URL !== '') {
            $base = rtrim(SITE_URL, '/');
        } else {
            $dir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $base = rtrim($dir === '/' || $dir === '.' ? '' : $dir, '/');
        }
    }
    return $base . '/' . ltrim($path, '/');
}

/** URL to a static asset with a cache-busting version. */
function asset(string $path): string
{
    $file = APP_ROOT . '/assets/' . ltrim($path, '/');
    $ver  = is_file($file) ? (string) filemtime($file) : '1';
    return base_url('assets/' . ltrim($path, '/')) . '?v=' . $ver;
}

/** Turn a phone number into a tel: link. */
function tel_link(string $phone): string
{
    $digits = preg_replace('/[^0-9+]/', '', $phone);
    return 'tel:' . $digits;
}

/** Resolve a stored URL: absolute URLs, anchors and tel/mailto links pass through. */
function link_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '#';
    }
    if (preg_match('~^(https?:|mailto:|tel:|#)~i', $url)) {
        return $url;
    }
    return base_url($url);
}

/** Resolve an image path stored in the DB (relative to assets/img). */
function img_url(?string $file): string
{
    $file = trim((string) $file);
    if (preg_match('~^https?://~i', $file)) {
        return $file;
    }
    return asset('img/' . ltrim($file, '/'));
}

require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/content.php';
