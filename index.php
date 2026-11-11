<?php
require_once __DIR__ . '/includes/bootstrap.php';

$s            = get_settings();
$categories   = get_menu_categories();
$hours        = get_opening_hours();
$showcase     = get_showcase();
$testimonials = get_testimonials();

$leftCats  = array_values(array_filter($categories, fn($c) => $c['side'] === 'left'));
$rightCats = array_values(array_filter($categories, fn($c) => $c['side'] !== 'left'));

/* Hero slider: one slide per category, in pill order (left pills, then right pills) */
[$heroSlides, $heroStart] = get_hero_slides($s, array_merge($leftCats, $rightCats));
$heroPreload = $heroSlides[$heroStart]['image'];

require __DIR__ . '/includes/partials/header.php';

/** Discover More button */
function discover_btn(string $url, string $label = 'Discover More'): string
{
    return '<a class="btn-pill" href="' . e(link_url($url)) . '"><span class="btn-pill__icon" aria-hidden="true">'
        . icon_arrow() . '</span><span class="btn-pill__text">' . e($label) . '</span></a>';
}

/** Category pill — a button that switches the hero slider to that dish. */
function category_pill(array $cat, int $index, bool $active, int $order = 0): string
{
    return '<li><button class="cat-pill' . ($active ? ' is-active' : '') . '" type="button" data-slide="' . $index . '"'
        . ' aria-controls="hero-slide-' . $index . '" aria-pressed="' . ($active ? 'true' : 'false') . '" style="--i:' . $order . '">'
        . '<img src="' . e(img_url($cat['thumbnail'])) . '" alt="" width="60" height="37">' . e($cat['name']) . '</button></li>';
}
?>
<main id="main">

  <!-- ============ HERO (dish slider) ============ -->
  <section class="hero" id="menu" aria-roledescription="carousel" aria-label="Our dishes">
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

    <h1 class="sr-only"><?= e($s['meta_title']) ?></h1>

    <div class="hero__slides" aria-live="polite">
      <?php foreach ($heroSlides as $i => $slide): $active = $i === $heroStart; ?>
        <div class="hero__slide<?= $active ? ' is-active' : '' ?>" id="hero-slide-<?= $i ?>" role="group"
             aria-roledescription="slide" aria-label="<?= e($slide['name'] ?: $slide['title']) ?>"<?= $active ? '' : ' aria-hidden="true"' ?>>
          <div class="hero__head">
            <?php if ($slide['badge'] !== ''): ?><span class="tag"><?= e($slide['badge']) ?></span><?php endif; ?>
            <h2 class="hero__title"><?= e($slide['title']) ?></h2>
            <?php if ($slide['subtitle'] !== ''): ?><p class="hero__subtitle"><?= e($slide['subtitle']) ?></p><?php endif; ?>
          </div>
          <div class="hero__dish">
            <span class="hero__steam" aria-hidden="true"><i></i><i></i><i></i></span>
            <img src="<?= e(img_url($slide['image'])) ?>" alt="<?= e(trim($slide['title'] . ' ' . $slide['subtitle'])) ?>"
                 width="1215" height="747"<?= $active ? ' fetchpriority="high"' : ' decoding="async"' ?>>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (count($categories) > 0): ?>
    <div class="hero__cats-wrap">
      <ul class="hero__cats hero__cats--left" aria-label="Choose a dish">
        <?php foreach ($leftCats as $n => $cat) echo category_pill($cat, $n, $n === $heroStart, $n); ?>
      </ul>
      <ul class="hero__cats hero__cats--right" aria-label="Choose a dish">
        <?php foreach ($rightCats as $n => $cat) { $idx = count($leftCats) + $n; echo category_pill($cat, $idx, $idx === $heroStart, $n); } ?>
      </ul>
    </div>
    <?php endif; ?>
  </section>

  <!-- ============ ABOUT ============ -->
  <section class="about" id="about" aria-labelledby="about-title">
    <div class="about__media" data-reveal="wipe">
      <img src="<?= e(img_url($s['about_image'])) ?>" alt="Chef plating a dish at Chum Chum's" width="931" height="1330" loading="lazy">
    </div>
    <div class="about__content">
      <span class="tag tag--gold" data-reveal style="--d:1"><?= e($s['about_badge']) ?></span>
      <h2 class="about__title" id="about-title" data-reveal style="--d:2"><?= e($s['about_title']) ?></h2>
      <p class="about__text" data-reveal style="--d:3"><?= e($s['about_text']) ?></p>

      <div class="about__visit" data-reveal="zoom" style="--d:4">
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
        <article class="showcase__item showcase__item--feature" data-reveal="zoom">
          <img src="<?= e(img_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="811" height="934">
          <div class="showcase__feature-body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['description']) ?></p>
            <?= discover_btn($item['link_url'] ?: '#menu') ?>
          </div>
        </article>
      <?php elseif (in_array($slot, [2, 4, 6], true)): ?>
        <article class="showcase__item showcase__item--text showcase__item--s<?= $slot ?>" data-reveal style="--d:<?= $slot - 1 ?>">
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['description']) ?></p>
          <?= discover_btn($item['link_url'] ?: '#menu') ?>
        </article>
      <?php else: ?>
        <figure class="showcase__item showcase__item--image showcase__item--s<?= $slot ?>" data-reveal="zoom" style="--d:<?= $slot - 1 ?>">
          <img src="<?= e(img_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="535" height="467">
        </figure>
      <?php endif; ?>
    <?php endforeach; ?>
  </section>

  <!-- ============ RESERVATION ============ -->
  <section class="reservation" id="reservation" aria-labelledby="reservation-title">
    <div class="reservation__box" data-reveal="zoom">
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
  <?php if ($testimonials): ?>
  <section class="testimonials" aria-label="What our guests say">
    <div class="testimonials__stage" data-reveal>
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
  <?php endif; ?>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
