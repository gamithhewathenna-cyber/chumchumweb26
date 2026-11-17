<?php
/**
 * Local SEO pages for the suburbs around Clayton.
 *   /sri-lankan-restaurant               → all areas (hub)
 *   /sri-lankan-restaurant/glen-waverley → "Sri Lankan Restaurant near Glen Waverley"
 * Content is built from real facts per suburb (distance, direction, drive time,
 * neighbours) plus the dishes on the menu. Owners can add text in Admin → Local SEO.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$s = get_settings();
if (!local_enabled($s)) {
    local_not_found();
}

$slug  = strtolower(trim((string) ($_GET['item'] ?? '')));
$areas = local_area_list($s);
if ($slug !== '' && !isset($areas[$slug])) {
    local_not_found();
}
local_canonical_redirect('sri-lankan-restaurant', $slug);

$siteName   = $s['site_name'];
$addrQuery  = local_address_query($s);
$addrShort  = local_address_short($s);
$dishes     = local_dishes($s);
$dishNames  = array_column($dishes, 'name');
$hours      = get_opening_hours();
$phone      = trim((string) $s['phone']);
$host       = (function () {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . preg_replace('~[^a-z0-9.:-]~i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
})();
$abs = fn(string $url) => preg_match('~^https?://~i', $url) ? $url : $host . '/' . ltrim($url, '/');

/* ================================================================== */
/*  HUB — all areas                                                   */
/* ================================================================== */
if ($slug === '') {
    $pageTitle       = $s['local_hub_title'] . ' | ' . $siteName . ' — Clayton, Melbourne';
    $pageDescription = 'Sri Lankan food near you in Melbourne\'s south-east. ' . $siteName . ' in Clayton serves '
                     . local_join(array_slice($dishNames, 0, 3)) . ' to ' . local_join(array_slice(array_column($areas, 'name'), 1, 4)) . ' and more.';
    $canonical       = $abs(area_url());
    [$photo, $photoCaption] = local_area_photo($s, 'hub');
    $heroPreload     = $photo;
    $schemaAdd       = ['areaServed' => array_values(array_map(fn($a) => ['@type' => 'Place', 'name' => $a['name'] . ' VIC ' . $a['postcode']], $areas))];
    $extraSchema     = [local_breadcrumb_schema([['Home', $abs(base_url(''))], [$s['local_hub_title'], $canonical]])];

    $rings = ['Within 5 km' => [], '5 – 10 km' => [], '10 – 15 km' => [], 'Around 15 km+' => []];
    foreach ($areas as $a) {
        $k = $a['km'] < 5 ? 'Within 5 km' : ($a['km'] < 10 ? '5 – 10 km' : ($a['km'] <= 15 ? '10 – 15 km' : 'Around 15 km+'));
        $rings[$k][] = $a;
    }
    [$pw, $ph] = local_img_size($photo);

    require __DIR__ . '/includes/partials/header.php';
    ?>
<main id="main" class="local-page">
  <nav class="local-crumbs" aria-label="Breadcrumb">
    <a href="<?= e(base_url('')) ?>">Home</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($s['local_hub_title']) ?></span>
  </nav>

  <section class="page-hero local-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title local-hero__title" id="page-title"><?= e($s['local_hub_title']) ?></h1>
    <p class="page-hero__intro"><?= e($s['local_hub_intro']) ?></p>
  </section>

  <section class="local-intro">
    <figure class="local-photo" data-reveal="zoom">
      <img src="<?= e(img_url($photo)) ?>" alt="<?= e($photoCaption) ?> — Sri Lankan restaurant in Clayton" width="<?= $pw ?>" height="<?= $ph ?>" fetchpriority="high">
    </figure>
    <div class="local-copy">
      <h2 data-reveal>Authentic Sri Lankan food in Clayton</h2>
      <p data-reveal style="--d:1"><?= e($siteName) ?> is at <?= e($addrShort) ?>, cooking <?= e(local_join(array_slice($dishNames, 0, 4))) ?> and more from our Clayton kitchen — dine in or take away.
        Pick your suburb below for directions, drive times and the dishes locals love.</p>
      <div class="local-actions" data-reveal style="--d:2">
        <a class="btn-pill" href="<?= e(base_url('menu.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">See the menu</span></a>
        <a class="btn-pill" href="<?= e(dish_url()) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Our dishes</span></a>
      </div>
    </div>
  </section>

  <section class="local-areas" aria-labelledby="areas-title">
    <h2 class="local-h2" id="areas-title" data-reveal>Suburbs we serve around Clayton</h2>
    <?php foreach ($rings as $label => $list): if (!$list) { continue; } ?>
      <h3 class="local-areas__ring" data-reveal><?= e($label) ?></h3>
      <ul class="local-chips" data-reveal>
        <?php foreach ($list as $a): ?>
          <li><a href="<?= e(area_url($a['slug'])) ?>">Sri Lankan food <?= e($a['name']) ?> <span><?= e((string) $a['km']) ?> km</span></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>
  </section>

  <section class="local-map" aria-labelledby="map-title">
    <h2 class="local-h2" id="map-title" data-reveal>Find us in Clayton</h2>
    <div class="contact__map local-map__frame" data-reveal="zoom">
      <iframe title="Map showing <?= e($siteName) ?> at <?= e($addrShort) ?>" src="<?= e(local_map_embed($addrQuery, 12)) ?>"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
  </section>
</main>
<?php
    require __DIR__ . '/includes/partials/footer.php';
    exit;
}

/* ================================================================== */
/*  ONE SUBURB                                                         */
/* ================================================================== */
$a        = $areas[$slug];
$S        = $a['name'];
$km       = (string) $a['km'];
$min      = $a['minutes'];
$dir      = $a['direction'];
$isHome   = $a['straight'] < 1.5;                 // Clayton itself
$pool     = array_filter($dishes, fn($d) => $d['sri']);
$pool     = count($pool) >= 3 ? $pool : $dishes;
$picked   = array_map(fn($d) => $d['name'], local_pick($pool, $slug, min(4, count($pool))));
[$d1, $d2, $d3] = array_pad($picked, 3, 'Sri Lankan rice & curry');
$nearby   = local_nearest($s, $slug, 6);
$nearNames = array_column(array_slice($nearby, 0, 3), 'name');
$from     = $S . ' VIC ' . $a['postcode'] . ', Australia';
$custom   = text_paragraphs((string) ($s['area_text_' . $slug] ?? ''), ' data-reveal style="--d:3"');
[$photo, $photoCaption] = local_area_photo($s, $slug);
[$pw, $ph] = local_img_size($photo);

$v = local_variant($slug, 5);
if ($isHome) {
    $intro = "Right here in {$S}: {$siteName} at {$addrShort} cooks authentic Sri Lankan food — {$d1}, {$d2} and {$d3} — just minutes from anywhere in the suburb.";
} else {
    $intro = [
        "Craving real Sri Lankan food in {$S}? {$siteName} is about {$km} km away in Clayton — roughly {$min} minutes by car — serving {$d1}, {$d2} and {$d3}.",
        "{$S} locals don't have to go far for proper Sri Lankan flavour. Our Clayton kitchen is about {$km} km {$dir} of {$S}, around a {$min}-minute drive.",
        "Looking for a Sri Lankan restaurant near {$S} {$a['postcode']}? {$siteName} in Clayton is about {$km} km away, cooking {$d1} and {$d2} fresh every day.",
        "From {$S} it's an easy {$min}-minute drive to {$siteName} — the Clayton home of {$d1}, {$d2} and {$d3}.",
        "{$d1}, {$d2}, {$d3} — the Sri Lankan dishes {$S} loves are about {$km} km away at {$siteName} in Clayton.",
    ][$v];
}

$getHere = $isHome
    ? "You'll find us at {$addrShort}. Wherever you are in {$S}, we're a short drive, ride or walk away — perfect for a quick lunch, a relaxed dinner or takeaway on the way home."
    : [
        "Head {$dir} from {$S} and you'll find us at {$addrShort}. It's about {$km} km by road — around {$min} minutes — so it's easy for a weeknight dinner, a weekend feast or takeaway on the way home.",
        "We're about {$km} km {$dir} of {$S} at {$addrShort}. Allow around {$min} minutes by car, depending on traffic — then dine in, or take it home.",
        "Getting to us from {$S} takes around {$min} minutes: we're {$dir} of the suburb at {$addrShort}, about {$km} km by road. Eat in with us or grab takeaway.",
    ][local_variant($slug . 'g', 3)];

$story = '';
foreach ($dishes as $d) {
    if ($d['name'] === $d1 && $d['story'] !== '') {
        $story = preg_split('~(?<=[.!?])\s+~', $d['story'])[0];
    }
}
$foodPara = [
    "Everything is cooked to order in our Clayton kitchen with real Sri Lankan spices. " . ($story !== '' ? $story . ' ' : '') . "Pair it with {$d2} for the table.",
    "Our menu is Sri Lankan through and through. " . ($story !== '' ? $story . ' ' : '') . "Many of our {$S} regulars also swear by the {$d2}.",
    ($story !== '' ? $story . ' ' : '') . "It's one of the dishes that brings people from {$S} back again — along with our {$d2} and {$d3}.",
][local_variant($slug . 'f', 3)];

$nearPara = $nearNames
    ? "We also welcome plenty of diners from nearby " . local_join($nearNames) . ", so if you're catching up with friends from around the south-east, {$siteName} is an easy place to meet."
    : '';

$faq = [
    ["Is there a Sri Lankan restaurant near {$S}?",
     $isHome ? "Yes — {$siteName} is right here in {$S} at {$addrShort}."
             : "Yes. {$siteName} is at {$addrShort}, about {$km} km ({$min} minutes' drive) from {$S}."],
    ["What Sri Lankan food can I get near {$S}?",
     'Our menu includes ' . local_join($dishNames) . '. See the Menu page for everything we cook.'],
    ["Can I get takeaway near {$S}?",
     "Yes — enjoy your favourite dishes at our Clayton restaurant or take them with you" . ($phone !== '' ? ". Call {$phone} to order ahead." : '.')],
];
if ($hours) {
    $faq[] = ["What are your opening hours?", local_hours_text() . '.'];
}
if ($phone !== '') {
    $faq[] = ["How do I contact {$siteName} from {$S}?", "Call us on {$phone}, or send a message through our Contact page."];
}

$pageTitle       = "Sri Lankan Restaurant near {$S} {$a['postcode']} | {$siteName} Clayton";
$pageDescription = $isHome
    ? "Sri Lankan restaurant in {$S}: {$d1}, {$d2} & more at {$siteName}, {$addrShort}. Dine in or takeaway — menu, hours & map."
    : "Sri Lankan food near {$S}: {$d1}, {$d2} & more at {$siteName}, {$km} km away in Clayton (~{$min} min drive). Dine in or takeaway.";
$canonical       = $abs(area_url($slug));
$heroPreload     = $photo;
$schemaAdd       = ['areaServed' => ['@type' => 'Place', 'name' => "{$S} VIC {$a['postcode']}"]];
$extraSchema     = [
    local_breadcrumb_schema([['Home', $abs(base_url(''))], [$s['local_hub_title'], $abs(area_url())], [$S, $canonical]]),
    local_faq_schema($faq),
];

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="local-page">
  <nav class="local-crumbs" aria-label="Breadcrumb">
    <a href="<?= e(base_url('')) ?>">Home</a><span aria-hidden="true">›</span>
    <a href="<?= e(area_url()) ?>"><?= e($s['local_hub_title']) ?></a><span aria-hidden="true">›</span>
    <span aria-current="page"><?= e($S) ?></span>
  </nav>

  <section class="page-hero local-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title local-hero__title" id="page-title">Sri Lankan Restaurant near <?= e($S) ?></h1>
    <p class="page-hero__intro"><?= e($intro) ?></p>
  </section>

  <section class="local-intro">
    <figure class="local-photo" data-reveal="zoom">
      <img src="<?= e(img_url($photo)) ?>" alt="<?= e($photoCaption) ?> — Sri Lankan food near <?= e($S) ?>" width="<?= $pw ?>" height="<?= $ph ?>" fetchpriority="high">
      <figcaption><?= e($photoCaption) ?> at <?= e($siteName) ?>, Clayton</figcaption>
    </figure>
    <div class="local-copy">
      <h2 data-reveal>Sri Lankan food close to <?= e($S) ?></h2>
      <ul class="local-facts" data-reveal style="--d:1">
        <?php if (!$isHome): ?>
          <li><strong><?= e($km) ?> km</strong> from <?= e($S) ?></li>
          <li><strong>~<?= (int) $min ?> min</strong> by car</li>
        <?php else: ?>
          <li><strong>In <?= e($S) ?></strong> <?= e($a['postcode']) ?></li>
        <?php endif; ?>
        <li><strong>Dine in</strong> or takeaway</li>
      </ul>
      <p data-reveal style="--d:2"><?= e($getHere) ?></p>
      <p data-reveal style="--d:3"><?= e($foodPara) ?></p>
      <?= $custom ?>
      <?php if ($nearPara !== ''): ?><p data-reveal style="--d:4"><?= e($nearPara) ?></p><?php endif; ?>
      <div class="local-actions" data-reveal style="--d:4">
        <a class="btn-pill" href="<?= e(local_directions_link($from, $addrQuery)) ?>" target="_blank" rel="noopener"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Directions from <?= e($S) ?></span></a>
        <a class="btn-pill" href="<?= e(base_url('menu.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">See the menu</span></a>
        <?php if ($phone !== ''): ?>
          <a class="btn-pill" href="<?= e(tel_link($phone)) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_phone() ?></span><span class="btn-pill__text">Call <?= e($phone) ?></span></a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="local-map" aria-labelledby="map-title">
    <h2 class="local-h2" id="map-title" data-reveal><?= $isHome ? 'Find us in ' . e($S) : 'Directions from ' . e($S) . ' to ' . e($siteName) ?></h2>
    <div class="contact__map local-map__frame" data-reveal="zoom">
      <iframe title="Map: <?= e($isHome ? $siteName . ' in ' . $S : 'route from ' . $S . ' to ' . $siteName) ?>"
              src="<?= e($isHome ? local_map_embed($addrQuery, 15) : local_directions_embed($from, $addrQuery)) ?>"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
    <p class="local-map__addr"><?= icon_pin() ?><span><?= e($siteName) ?>, <?= e($addrShort) ?></span></p>
  </section>

  <?php if ($dishes): ?>
  <section class="local-dishes" aria-labelledby="dishes-title">
    <h2 class="local-h2" id="dishes-title" data-reveal>What <?= e($S) ?> orders at <?= e($siteName) ?></h2>
    <ul class="local-cards">
      <?php foreach (local_pick($dishes, $slug . 'cards', min(6, count($dishes))) as $i => $d): ?>
        <li data-reveal style="--d:<?= $i ?>">
          <a href="<?= e(dish_url($d['slug'])) ?>">
            <strong><?= e($d['name']) ?></strong>
            <?php if ($d['subtitle'] !== ''): ?><span><?= e($d['subtitle']) ?></span><?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <section class="local-bottom">
    <?php if ($hours): ?>
      <div class="local-hours" data-reveal>
        <h2 class="local-h2"><?= e($s['hours_title'] ?: 'Opening Hours') ?></h2>
        <dl>
          <?php foreach ($hours as $h): ?><div><dt><?= e($h['label']) ?></dt><dd><?= e($h['hours']) ?></dd></div><?php endforeach; ?>
        </dl>
      </div>
    <?php endif; ?>
    <div class="local-faq" data-reveal style="--d:1">
      <h2 class="local-h2">Questions from <?= e($S) ?></h2>
      <?php foreach ($faq as [$q, $ans]): ?>
        <details><summary><?= e($q) ?></summary><p><?= e($ans) ?></p></details>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="local-areas" aria-labelledby="near-title">
    <h2 class="local-h2" id="near-title" data-reveal>Also near <?= e($S) ?></h2>
    <ul class="local-chips" data-reveal>
      <?php foreach ($nearby as $n): ?>
        <li><a href="<?= e(area_url($n['slug'])) ?>">Sri Lankan restaurant <?= e($n['name']) ?> <span><?= e((string) $n['km']) ?> km</span></a></li>
      <?php endforeach; ?>
      <li><a href="<?= e(area_url()) ?>">All areas we serve</a></li>
    </ul>
  </section>
</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
