<?php
/**
 * Promotion popup (Admin → Popup Offers).
 * Several offers can be saved; the website shows the first one (in the admin's
 * order) that is switched on and inside its start/end dates. Images are 800 × 1000 px.
 */

/** Popup table — created the first time the admin page or website needs it. */
function popup_table_sql(): string
{
    return "CREATE TABLE IF NOT EXISTS `promo_popups` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `title`       VARCHAR(150) NOT NULL DEFAULT '',
        `image`       VARCHAR(255) NOT NULL,
        `link_url`    VARCHAR(500) NOT NULL DEFAULT '',
        `button_text` VARCHAR(60)  NOT NULL DEFAULT '',
        `starts_on`   DATE NULL DEFAULT NULL,
        `ends_on`     DATE NULL DEFAULT NULL,
        `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
        `sort_order`  INT          NOT NULL DEFAULT 0,
        `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}

/** "live" | "scheduled" | "expired" | "hidden" for one popup row. */
function popup_status(array $p, ?string $today = null): string
{
    $today = $today ?? date('Y-m-d');
    if (!(int) $p['is_active']) {
        return 'hidden';
    }
    if (!empty($p['starts_on']) && $p['starts_on'] > $today) {
        return 'scheduled';
    }
    if (!empty($p['ends_on']) && $p['ends_on'] < $today) {
        return 'expired';
    }
    return 'live';
}

/**
 * The popup to show on this page, or null.
 * ?popup_preview=ID lets a logged-in admin see any popup (even hidden), every time.
 */
function popup_for_page(array $s): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $previewId = isset($_GET['popup_preview']) && function_exists('viewer_is_admin') && viewer_is_admin() ? (int) $_GET['popup_preview'] : 0;
    if (!$previewId) {
        if (($s['popup_enabled'] ?? '1') !== '1' || http_response_code() >= 400) {
            return null;
        }
        if (($s['popup_pages'] ?? 'all') === 'home' && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'index.php') {
            return null;
        }
    }
    try {
        if ($previewId) {
            $st = $pdo->prepare('SELECT * FROM promo_popups WHERE id = ?');
            $st->execute([$previewId]);
            $row = $st->fetch();
            return $row ? $row + ['preview' => true] : null;
        }
        $today = date('Y-m-d');
        $st = $pdo->prepare('SELECT * FROM promo_popups
            WHERE is_active = 1 AND (starts_on IS NULL OR starts_on <= ?) AND (ends_on IS NULL OR ends_on >= ?)
            ORDER BY sort_order, id LIMIT 1');
        $st->execute([$today, $today]);
        return $st->fetch() ?: null;
    } catch (Throwable $ex) {
        return null; // table not created yet — no popup
    }
}
