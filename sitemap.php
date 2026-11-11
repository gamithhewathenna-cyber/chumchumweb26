<?php
/**
 * XML sitemap for search engines — served at /sitemap.xml (see .htaccess).
 * Built live from the database, so new gallery photos and content edits are
 * picked up automatically. Each page's <lastmod> is the latest time any of its
 * settings were saved in the admin (or the page file changed, if newer).
 */
require_once __DIR__ . '/includes/bootstrap.php';

/** Make a site URL absolute ("/menu.php" → "https://host/menu.php"); absolute URLs pass through. */
function make_absolute(string $url): string
{
    if (preg_match('~^https?://~i', $url)) {
        return $url;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host  = preg_replace('~[^a-z0-9.:-]~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return ($https ? 'https' : 'http') . '://' . $host . '/' . ltrim($url, '/');
}

/** Absolute URL for a page path on this site. */
function absolute_url(string $path = ''): string
{
    return make_absolute(base_url($path));
}

/** Newest "updated_at" among settings whose keys start with any of the prefixes (as a Unix time, or 0). */
function settings_updated(array $prefixes): int
{
    $pdo = db();
    if (!$pdo || !$prefixes) {
        return 0;
    }
    try {
        $where = implode(' OR ', array_fill(0, count($prefixes), 'setting_key LIKE ?'));
        $st = $pdo->prepare("SELECT MAX(updated_at) FROM settings WHERE $where");
        $st->execute(array_map(fn($p) => $p . '%', $prefixes));
        $t = $st->fetchColumn();
        return $t ? (int) strtotime((string) $t) : 0;
    } catch (Throwable $ex) {
        return 0;
    }
}

$pages = [
    // path,          settings prefixes,                                                    change freq, priority
    ['',            ['hero_', 'about_', 'hours_', 'reservation_', 'site_name', 'meta_'],      'weekly',  '1.0'],
    ['menu.php',    ['menu_'],                                                                 'weekly',  '0.9'],
    ['about.php',   ['aboutpage_'],                                                            'monthly', '0.8'],
    ['gallery.php', ['gallery_'],                                                              'weekly',  '0.7'],
    ['contact.php', ['contact_', 'phone', 'email', 'address_'],                                'monthly', '0.8'],
];

$gallery = get_gallery_images();

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // the sitemap itself shouldn't appear in search results

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">', "\n";
foreach ($pages as [$path, $prefixes, $freq, $priority]) {
    $file    = __DIR__ . '/' . ($path === '' ? 'index.php' : $path);
    $lastmod = max(settings_updated($prefixes), is_file($file) ? (int) filemtime($file) : 0);
    echo "  <url>\n";
    echo '    <loc>', htmlspecialchars(absolute_url($path), ENT_XML1), "</loc>\n";
    if ($lastmod) {
        echo '    <lastmod>', gmdate('Y-m-d', $lastmod), "</lastmod>\n";
    }
    echo "    <changefreq>$freq</changefreq>\n";
    echo "    <priority>$priority</priority>\n";
    if ($path === 'gallery.php') {
        foreach ($gallery as $g) {
            $src = make_absolute(preg_replace('~\?.*$~', '', img_url($g['image']))); // drop the ?v= cache-buster
            echo "    <image:image>\n";
            echo '      <image:loc>', htmlspecialchars($src, ENT_XML1), "</image:loc>\n";
            echo "    </image:image>\n";
        }
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
