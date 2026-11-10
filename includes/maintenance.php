<?php
/**
 * Maintenance mode. Switched on in Admin → Site Settings → Maintenance Mode.
 * Visitors get a "back soon" page (HTTP 503, so Google knows it is temporary);
 * logged-in admins still see the full site with a reminder banner.
 */

/** True when the visitor is logged in to the admin panel (read-only session check). */
function viewer_is_admin(): bool
{
    static $result = null;
    if ($result !== null) {
        return $result;
    }
    $result = false;
    if (empty($_COOKIE['cc_admin']) || !is_string($_COOKIE['cc_admin'])) {
        return false; // no admin cookie: never start a session for normal visitors
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('cc_admin');
        @session_start(['read_and_close' => true]);
    }
    $result = !empty($_SESSION['admin_id'])
        && time() - (int) ($_SESSION['last_seen'] ?? 0) <= 7200; // same idle timeout as the admin
    return $result;
}

function maintenance_enabled(array $s): bool
{
    return ($s['maintenance_mode'] ?? '0') === '1';
}

/**
 * Call before any page output. Shows the maintenance page and stops,
 * unless an admin is viewing. Returns true when an admin is bypassing it
 * (so the page can show a reminder banner).
 */
function maintenance_gate(array $s): bool
{
    $preview = isset($_GET['preview_maintenance']) && viewer_is_admin();
    if (!maintenance_enabled($s) && !$preview) {
        return false;
    }
    if (!$preview && viewer_is_admin()) {
        return true;
    }
    if (!$preview) {
        http_response_code(503);
        header('Retry-After: 3600');
    }
    header('Cache-Control: no-store');
    require __DIR__ . '/partials/maintenance-page.php';
    exit;
}
