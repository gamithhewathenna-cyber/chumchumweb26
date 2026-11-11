<?php
/** @var array $s  site settings */
$maintenanceBypass = maintenance_gate($s); // visitors stop here when maintenance mode is on
$pageTitle      = $pageTitle ?? $s['meta_title'];
$pageDescription = $pageDescription ?? $s['meta_description'];
$nav             = get_navigation();
$current         = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

// Absolute addresses: site root (e.g. "https://www.chumchums.com.au") + this page's canonical URL
$siteRoot = (function () {
    if (preg_match('~^(https?://[^/]+)~i', base_url(''), $m)) {
        return $m[1];
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . preg_replace('~[^a-z0-9.:-]~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
})();
/** "/menu.php" or "https://…/menu.php?v=1" → "https://host/menu.php" (cache-buster removed) */
$absolute = fn(string $url) => $siteRoot . '/' . ltrim(preg_replace(['~^https?://[^/]+~i', '~\?.*$~'], '', $url), '/');
// The homepage's canonical address is "/", never "/index.php". Pages may set $canonical = '' to skip it (e.g. 404).
$canonical  = $canonical ?? $absolute(base_url($current === 'index.php' ? '' : $current));
$shareImage = $absolute(img_url(plate_image((string) ($heroPreload ?? $s['hero_image']))));

// Restaurant details for Google (structured data)
$schema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Restaurant',
    'name'          => $s['site_name'],
    'url'           => $absolute(base_url('')),
    'image'         => $shareImage,
    'logo'          => $absolute(asset('img/logo.png')),
    'servesCuisine' => ['Sri Lankan', 'Fusion'],
    'telephone'     => $s['phone'],
    'email'         => $s['email'],
    'address'       => ['@type' => 'PostalAddress', 'streetAddress' => rtrim(trim($s['address_line1']), ','),
                        'addressLocality' => 'Clayton', 'addressRegion' => 'VIC', 'addressCountry' => 'AU'],
    'hasMenu'       => $absolute(base_url('menu.php')),
];
$sameAs = array_values(array_filter([$s['facebook_url'] ?? '', $s['instagram_url'] ?? ''], 'has_link'));
if ($sameAs) {
    $schema['sameAs'] = $sameAs;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#000000">
<meta name="google-site-verification" content="cuSIctaQ66p265EZgNNCAQYhP_QxzRI16NgKXT2ZPqk" />
<?php if (!empty($robots)): ?><meta name="robots" content="<?= e($robots) ?>">
<?php endif; ?>
<?php if ($canonical): ?><link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= e($s['site_name']) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?= e($shareImage) ?>">
<meta property="og:locale" content="en_AU">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('img/logo.png')) ?>">
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<link rel="preload" href="<?= e(asset('fonts/dm-sans-latin-opsz-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(img_url($heroPreload ?? $s['hero_image'])) ?>" as="image">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<script>
  /* Turn on load + scroll animations before first paint. If main.js never runs, show everything after 3s. */
  document.documentElement.classList.add('js', 'intro');
  setTimeout(function () { if (!window.ccReady) document.documentElement.classList.remove('js', 'intro'); }, 3000);
</script>
</head>
<body>
<?php if ($maintenanceBypass): ?>
<div class="maintenance-bar" role="status">
  Maintenance mode is <strong>ON</strong> — visitors see the “back soon” page. You can see the site because you're logged in.
  <a href="<?= e(base_url('admin/settings.php?tab=maintenance')) ?>">Turn off</a>
</div>
<?php endif; ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="top">
  <div class="site-header__inner">
    <a class="site-header__logo" href="<?= e(base_url('index.php')) ?>" aria-label="<?= e($s['site_name']) ?> — Home">
      <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e($s['site_name']) ?>" width="204" height="159">
    </a>

    <!-- Phones & tablets only: opens the navigation drawer (desktop shows the links in the header) -->
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-drawer" aria-label="Open navigation">
      <span class="menu-toggle__icon" aria-hidden="true"><i></i><i></i></span>
    </button>

    <nav class="main-nav" aria-label="Main">
      <ul>
        <?php foreach ($nav as $item): ?>
          <li><a href="<?= e(nav_url($item['url'])) ?>"<?= ($item['url'] === $current) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <?php /* Buttons only appear once a real link is set in Admin → Site Settings → Links (no dead "#" buttons) */ ?>
    <div class="site-header__actions">
      <?php if (has_link($s['customer_url'])): ?>
        <a class="current-customer" href="<?= e(link_url($s['customer_url'])) ?>">Current Customer?</a>
      <?php endif; ?>
      <?php if (has_link($s['order_url'])): ?>
        <a class="btn-pill btn-pill--order" href="<?= e(link_url($s['order_url'])) ?>">
          <span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span>
          <span class="btn-pill__text">Order Now</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="drawer" id="site-drawer" aria-hidden="true">
  <div class="drawer__backdrop" data-drawer-close></div>
  <aside class="drawer__panel" role="dialog" aria-modal="true" aria-label="Navigation">
    <button class="drawer__close" type="button" data-drawer-close aria-label="Close navigation">&times;</button>
    <img class="drawer__logo" src="<?= e(asset('img/logo.png')) ?>" alt="" width="204" height="159" loading="lazy" decoding="async">
    <nav aria-label="Mobile">
      <ul class="drawer__nav">
        <?php foreach ($nav as $item): ?>
          <li><a href="<?= e(nav_url($item['url'])) ?>" data-drawer-close><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="drawer__actions">
      <?php if (has_link($s['customer_url'])): ?>
        <a class="current-customer" href="<?= e(link_url($s['customer_url'])) ?>">Current Customer?</a>
      <?php endif; ?>
      <?php if (has_link($s['order_url'])): ?>
        <a class="btn-pill btn-pill--order" href="<?= e(link_url($s['order_url'])) ?>">
          <span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span>
          <span class="btn-pill__text">Order Now</span>
        </a>
      <?php endif; ?>
      <a class="drawer__phone" href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a>
    </div>
  </aside>
</div>
