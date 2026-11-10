<?php
require_once __DIR__ . '/includes/bootstrap.php';

$s            = get_settings();
$categories   = get_menu_categories();
$hours        = get_opening_hours();
$showcase     = get_showcase();
$testimonials = get_testimonials();

$leftCats  = array_values(array_filter($categories, fn($c) => $c['side'] === 'left'));
$rightCats = array_values(array_filter($categories, fn($c) => $c['side'] !== 'left'));

require __DIR__ . '/includes/partials/header.php';

/** Discover More button */
function discover_btn(string $url, string $label = 'Discover More'): string
{
    return '<a class="btn-pill" href="' . e(link_url($url)) . '"><span class="btn-pill__icon" aria-hidden="true">'
        . icon_arrow() . '</span><span class="btn-pill__text">' . e($label) . '</span></a>';
}

function category_pill(array $cat): string
{
    return '<li><a class="cat-pill" href="' . e(link_url($cat['link_url'])) . '"><img src="' . e(img_url($cat['thumbnail']))
        . '" alt="" width="60" height="37">' . e($cat['name']) . '</a></li>';
}
?>
<main id="main">

  <!-- ============ HERO ============ -->
  <section class="hero" id="menu" aria-labelledby="hero-title">
    <svg class="hero__arc" viewBox="0 0 1440 800" preserveAspectRatio="xMidYMin meet" aria-hidden="true">
      <defs>
        <linearGradient id="arcFade" x1="0" y1="0" x2="0" y2="800" gradientUnits="userSpaceOnUse">
          <stop offset="0.36" stop-color="#fff" stop-opacity="0"/>
          <stop offset="0.52" stop-color="#fff" stop-opacity="0.3"/>
          <stop offset="0.75" stop-color="#fff" stop-opacity="0.22"/>
          <stop offset="0.95" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
      </defs>
      <circle cx="720" cy="744" r="516" fill="none" stroke="url(#arcFade)" stroke-width="1.2"/>
    </svg>

    <div class="hero__head">
      <span class="tag"><?= e($s['hero_badge']) ?></span>
      <h1 class="hero__title" id="hero-title"><?= e($s['hero_title']) ?></h1>
      <p class="hero__subtitle"><?= e($s['hero_subtitle']) ?></p>
    </div>

    <div class="hero__dish">
      <img src="<?= e(img_url($s['hero_image'])) ?>" alt="<?= e($s['hero_title'] . ' ' . $s['hero_subtitle']) ?>" width="1215" height="747" fetchpriority="high">
    </div>

    <div class="hero__cats-wrap">
      <ul class="hero__cats hero__cats--left" aria-label="Menu categories">
        <?php foreach ($leftCats as $cat) echo category_pill($cat); ?>
      </ul>
      <ul class="hero__cats hero__cats--right" aria-label="More menu categories">
        <?php foreach ($rightCats as $cat) echo category_pill($cat); ?>
      </ul>
    </div>
  </section>

  <!-- ============ ABOUT ============ -->
  <section class="about" id="about" aria-labelledby="about-title">
    <div class="about__media">
      <img src="<?= e(img_url($s['about_image'])) ?>" alt="Chef plating a dish at Chum Chum's" width="931" height="1330" loading="lazy">
    </div>
    <div class="about__content">
      <span class="tag tag--gold"><?= e($s['about_badge']) ?></span>
      <h2 class="about__title" id="about-title"><?= e($s['about_title']) ?></h2>
      <p class="about__text"><?= e($s['about_text']) ?></p>

      <div class="about__visit">
        <img class="about__interior" src="<?= e(img_url($s['interior_image'])) ?>" alt="Inside the Chum Chum's restaurant" width="560" height="552" loading="lazy">
        <div class="hours-card">
          <h3><?= e($s['hours_title']) ?></h3>
          <ul>
            <?php foreach ($hours as $h): ?>
              <li><?= e($h['label']) ?> : <?= e($h['hours']) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ SHOWCASE GRID ============ -->
  <section class="showcase" id="gallery" aria-label="Featured dishes">
    <?php foreach ($showcase as $slot => $item): ?>
      <?php if ($slot === 1): ?>
        <article class="showcase__item showcase__item--feature">
          <img src="<?= e(img_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="811" height="934">
          <div class="showcase__feature-body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['description']) ?></p>
            <?= discover_btn($item['link_url'] ?: '#menu') ?>
          </div>
        </article>
      <?php elseif (in_array($slot, [2, 4, 6], true)): ?>
        <article class="showcase__item showcase__item--text showcase__item--s<?= $slot ?>">
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['description']) ?></p>
          <?= discover_btn($item['link_url'] ?: '#menu') ?>
        </article>
      <?php else: ?>
        <figure class="showcase__item showcase__item--image showcase__item--s<?= $slot ?>">
          <img src="<?= e(img_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="535" height="467">
        </figure>
      <?php endif; ?>
    <?php endforeach; ?>
  </section>

  <!-- ============ RESERVATION ============ -->
  <section class="reservation" id="reservation" aria-labelledby="reservation-title">
    <div class="reservation__box">
      <div class="reservation__media">
        <img src="<?= e(img_url($s['reservation_image'])) ?>" alt="Guest enjoying a meal at Chum Chum's" width="1066" height="614" loading="lazy">
      </div>
      <div class="reservation__content">
        <h2 id="reservation-title"><?= e($s['reservation_title']) ?></h2>
        <p><?= e($s['reservation_text']) ?></p>
        <div class="reservation__call">
          <span class="reservation__label"><?= e($s['reservation_label']) ?></span>
          <a class="reservation__phone" href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ TESTIMONIALS ============ -->
  <section class="testimonials" aria-label="What our guests say">
    <div class="testimonials__stage">
      <img class="testimonials__brush" src="<?= e(asset('img/testimonial-brush.jpg')) ?>" alt="" width="1560" height="485" loading="lazy">
      <div class="testimonials__viewport" aria-live="polite">
        <?php foreach ($testimonials as $i => $t): ?>
          <blockquote class="testimonial<?= $i === 0 ? ' is-active' : '' ?>"<?= $i === 0 ? '' : ' hidden' ?>>
            <span class="testimonial__mark" aria-hidden="true"><?= icon_quote() ?></span>
            <h3><?= e($t['title']) ?></h3>
            <p><?= e($t['content']) ?></p>
          </blockquote>
        <?php endforeach; ?>
      </div>
      <?php if (count($testimonials) > 1): ?>
        <button class="testimonials__nav testimonials__nav--prev" type="button" aria-label="Previous testimonial"><?= icon_chevron('left') ?></button>
        <button class="testimonials__nav testimonials__nav--next" type="button" aria-label="Next testimonial"><?= icon_chevron('right') ?></button>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
