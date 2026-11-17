<?php
/**
 * Dish pages — one page per Chum Chum's dish, linked to the nearby suburbs.
 *   /dishes                     → all dishes (hub)
 *   /dishes/chicken-kottu-roti  → "Chicken Kottu Roti in Clayton"
 * Dishes come from Home Page → Slider and the named Showcase photos.
 * Owners can add text and a photo per dish in Admin → Local SEO.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$s = get_settings();
if (!local_enabled($s)) {
    local_not_found();
}

$slug   = strtolower(trim((string) ($_GET['item'] ?? '')));
$dishes = local_dishes($s);
if ($slug !== '' && !isset($dishes[$slug])) {
    local_not_found();
}
local_canonical_redirect('dishes', $slug);

$siteName  = $s['site_name'];
$addrQuery = local_address_query($s);
$addrShort = local_address_short($s);
$areas     = local_area_list($s);
$closest   = array_slice(array_filter($areas, fn($a) => $a['straight'] >= 1.5), 0, 10, true);
$hours     = get_opening_hours();
$host      = (function () {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . preg_replace('~[^a-z0-9.:-]~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
})();
$abs = fn(string $url) => preg_match('~^https?://~i', $url) ? $url : $host . '/' . ltrim($url, '/');

/* ================================================================== */
/*  HUB — all dishes                                                   */
/* ================================================================== */
if ($slug === '') {
    $names           = array_column($dishes, 'name');
    $pageTitle       = $s['dishes_hub_title'] . ' | ' . $siteName . ' — Sri Lankan Food, Clayton';
    $pageDescription = 'Sri Lankan dishes at ' . $siteName . ' in Clayton: ' . local_join(array_slice($names, 0, 5))
                     . '. Dine in or take away — close to ' . local_join(array_slice(array_column($closest, 'name'), 0, 3)) . '.';
    $canonical       = $abs(dish_url());
    $heroPreload     = 'reservation.jpg';
    $extraSchema     = [local_breadcrumb_schema([['Home', $abs(base_url(''))], [$s['dishes_hub_title'], $canonical]])];

    require __DIR__ . '/includes/partials/header.php';
    ?>
<main id="main" class="local-page">
  <nav class="local-crumbs" aria-label="Breadcrumb">
    <a href="<?= e(base_url('')) ?>">Home</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($s['dishes_hub_title']) ?></span>
  </nav>
  <section class="page-hero local-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title local-hero__title" id="page-title"><?= e($s['dishes_hub_title']) ?></h1>
    <p class="page-hero__intro"><?= e($s['dishes_hub_intro']) ?></p>
  </section>

  <section class="local-dishes" aria-label="Dishes">
    <ul class="local-cards local-cards--photo">
      <?php foreach (array_values($dishes) as $i => $d): [$img] = local_dish_photo($d); [$w, $h] = local_img_size($img); ?>
        <li data-reveal style="--d:<?= $i % 4 ?>">
          <a href="<?= e(dish_url($d['slug'])) ?>">
            <img src="<?= e(img_url($img)) ?>" alt="<?= e($d['name']) ?> at <?= e($siteName) ?>, Clayton" width="<?= $w ?>" height="<?= $h ?>" loading="lazy">
            <strong><?= e($d['name']) ?></strong>
            <?php if ($d['subtitle'] !== ''): ?><span><?= e($d['subtitle']) ?></span><?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="local-actions" data-reveal>
      <a class="btn-pill" href="<?= e(base_url('menu.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">See the full menu</span></a>
      <a class="btn-pill" href="<?= e(area_url()) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Areas we serve</span></a>
    </div>
  </section>
</main>
<?php
    require __DIR__ . '/includes/partials/footer.php';
    exit;
}

/* ================================================================== */
/*  ONE DISH                                                           */
/* ================================================================== */
$d        = $dishes[$slug];
$D        = $d['name'];
[$photo, $photoCaption] = local_dish_photo($d);
[$pw, $ph] = local_img_size($photo);
$nearTop  = array_column(array_slice($closest, 0, 5), 'name');
$custom   = text_paragraphs((string) ($s['dish_text_' . $slug] ?? ''), ' data-reveal style="--d:2"');
$others   = array_filter($dishes, fn($x) => $x['slug'] !== $slug);
$intro    = $d['subtitle'] !== ''
    ? "{$d['subtitle']} — cooked fresh at {$siteName} in Clayton, close to " . local_join(array_slice($nearTop, 0, 3)) . '.'
    : "Cooked fresh at {$siteName} in Clayton, close to " . local_join(array_slice($nearTop, 0, 3)) . '.';
$story    = $d['story'] !== '' ? $d['story'] : "{$D} is one of the Sri Lankan favourites on our menu — made to order in our Clayton kitchen with real spices.";

$faq = [
    ["Where can I get {$D} near me?",
     "{$siteName} serves {$D} at {$addrShort} — dine in or take away, handy for " . local_join($nearTop) . ' and the rest of Melbourne\'s south-east.'],
    ["What is {$D}?", $story],
];
if ($hours) {
    $faq[] = ["When can I order {$D}?", 'We\'re open ' . local_hours_text() . '.'];
}

$pageTitle       = "{$D} in Clayton | Sri Lankan Food near " . local_join(array_slice($nearTop, 0, 2)) . " | {$siteName}";
$pageDescription = ($d['subtitle'] !== '' ? "{$D}: {$d['subtitle']}. " : "{$D} at {$siteName}. ")
                 . "Authentic Sri Lankan food in Clayton, near " . local_join(array_slice($nearTop, 0, 4)) . '. Dine in or take away.';
$canonical       = $abs(dish_url($slug));
$heroPreload     = $photo;
$extraSchema     = [
    local_breadcrumb_schema([['Home', $abs(base_url(''))], [$s['dishes_hub_title'], $abs(dish_url())], [$D, $canonical]]),
    local_faq_schema($faq),
];

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="local-page">
  <nav class="local-crumbs" aria-label="Breadcrumb">
    <a href="<?= e(base_url('')) ?>">Home</a><span aria-hidden="true">›</span>
    <a href="<?= e(dish_url()) ?>"><?= e($s['dishes_hub_title']) ?></a><span aria-hidden="true">›</span>
    <span aria-current="page"><?= e($D) ?></span>
  </nav>

  <section class="page-hero local-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title local-hero__title" id="page-title"><?= e($D) ?> in Clayton</h1>
    <p class="page-hero__intro"><?= e($intro) ?></p>
  </section>

  <section class="local-intro">
    <figure class="local-photo" data-reveal="zoom">
      <img src="<?= e(img_url($photo)) ?>" alt="<?= e($photoCaption) ?> — Sri Lankan food at <?= e($siteName) ?>, Clayton" width="<?= $pw ?>" height="<?= $ph ?>" fetchpriority="high">
      <figcaption><?= e($photoCaption) ?></figcaption>
    </figure>
    <div class="local-copy">
      <h2 data-reveal>About our <?= e($D) ?></h2>
      <p data-reveal style="--d:1"><?= e($story) ?></p>
      <?= $custom ?>
      <p data-reveal style="--d:2">Find it at <?= e($siteName) ?>, <?= e($addrShort) ?> — dine in or take away, a short drive from <?= e(local_join($nearTop)) ?>.</p>
      <div class="local-actions" data-reveal style="--d:3">
        <a class="btn-pill" href="<?= e(base_url('menu.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">See the menu</span></a>
        <a class="btn-pill" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($addrQuery)) ?>" target="_blank" rel="noopener"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Get directions</span></a>
        <?php if (trim((string) $s['phone']) !== ''): ?>
          <a class="btn-pill" href="<?= e(tel_link($s['phone'])) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_phone() ?></span><span class="btn-pill__text">Call <?= e($s['phone']) ?></span></a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="local-areas" aria-labelledby="near-title">
    <h2 class="local-h2" id="near-title" data-reveal><?= e($D) ?> near you</h2>
    <ul class="local-chips" data-reveal>
      <?php foreach ($closest as $a): ?>
        <li><a href="<?= e(area_url($a['slug'])) ?>">Sri Lankan food <?= e($a['name']) ?> <span><?= e((string) $a['km']) ?> km</span></a></li>
      <?php endforeach; ?>
      <li><a href="<?= e(area_url()) ?>">All areas</a></li>
    </ul>
  </section>

  <section class="local-map" aria-labelledby="map-title">
    <h2 class="local-h2" id="map-title" data-reveal>Get <?= e($D) ?> in Clayton</h2>
    <div class="contact__map local-map__frame" data-reveal="zoom">
      <iframe title="Map showing <?= e($siteName) ?> at <?= e($addrShort) ?>" src="<?= e(local_map_embed($addrQuery, 13)) ?>"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
    <p class="local-map__addr"><?= icon_pin() ?><span><?= e($siteName) ?>, <?= e($addrShort) ?></span></p>
  </section>

  <section class="local-bottom">
    <div class="local-faq" data-reveal>
      <h2 class="local-h2">About <?= e($D) ?></h2>
      <?php foreach ($faq as [$q, $ans]): ?>
        <details><summary><?= e($q) ?></summary><p><?= e($ans) ?></p></details>
      <?php endforeach; ?>
    </div>
    <?php if ($others): ?>
      <div class="local-more" data-reveal style="--d:1">
        <h2 class="local-h2">More Sri Lankan favourites</h2>
        <ul class="local-chips">
          <?php foreach ($others as $o): ?><li><a href="<?= e(dish_url($o['slug'])) ?>"><?= e($o['name']) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
