<?php
/** Performance Meals — high-protein, fully cooked meals. Edited in Admin → Performance Meals. */
require_once __DIR__ . '/includes/bootstrap.php';

$s = get_settings();

/** The meal cards (slots 1–6; a slot with no name is skipped). */
$meals = [];
for ($i = 1; $i <= 6; $i++) {
    $title = trim((string) ($s["pm_meal{$i}_title"] ?? ''));
    if ($title !== '') {
        $meals[] = ['title' => $title, 'sides' => trim((string) ($s["pm_meal{$i}_sides"] ?? '')),
                    'text' => trim((string) ($s["pm_meal{$i}_text"] ?? '')), 'image' => trim((string) ($s["pm_meal{$i}_image"] ?? ''))];
    }
}

/** "Title — text" lines → [[title, text], …] */
$why = [];
foreach (preg_split('~\R~', trim((string) $s['pm_why_items'])) as $line) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }
    $parts = preg_split('~\s+[—–-]\s+|:\s+~u', $line, 2);
    $why[] = [trim($parts[0]), trim($parts[1] ?? '')];
}

$steps = [];
for ($i = 1; $i <= 3; $i++) {
    if (trim((string) $s["pm_step{$i}_title"]) !== '') {
        $steps[] = [$s["pm_step{$i}_title"], $s["pm_step{$i}_text"]];
    }
}
$tags = array_values(array_filter(array_map('trim', explode(',', (string) $s['pm_everyone_tags']))));

$pageTitle       = $s['pm_title'] . ' | High-Protein Ready Meals — ' . $s['site_name'] . ', Clayton';
$pageDescription = $s['pm_meta_description'];
$heroPreload     = $s['pm_banner'] !== '' ? $s['pm_banner'] : $s['hero_image'];
if ($meals) {
    $extraSchema = [[
        '@context' => 'https://schema.org',
        '@type'    => 'ItemList',
        'name'     => $s['pm_meals_title'],
        'itemListElement' => array_map(fn($m, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1,
            'item'  => ['@type' => 'MenuItem', 'name' => $m['title'], 'description' => trim($m['sides'] . '. ' . $m['text'], '. ')],
        ], $meals, array_keys($meals)),
    ]];
}

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="pm-page">

  <!-- ============ PAGE TITLE ============ -->
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title pm-hero__title" id="page-title"><?= e($s['pm_title']) ?></h1>
    <?php if (trim($s['pm_tagline']) !== ''): ?>
      <p class="page-hero__intro pm-hero__tagline"><?= e($s['pm_tagline']) ?></p>
    <?php endif; ?>
  </section>

  <?php if (trim($s['pm_banner']) !== ''): [$bw, $bh] = local_img_size($s['pm_banner']); ?>
    <div class="page-banner" data-reveal="wipe">
      <img src="<?= e(img_url($s['pm_banner'])) ?>" alt="" width="<?= $bw ?>" height="<?= $bh ?>" fetchpriority="high">
    </div>
  <?php endif; ?>

  <!-- ============ HEALTHY EATING MADE EASY ============ -->
  <section class="ap-intro pm-intro" aria-labelledby="pm-intro-title">
    <div class="ap-intro__content">
      <?php if (trim($s['pm_intro_badge']) !== ''): ?>
        <span class="tag tag--gold" data-reveal><?= e($s['pm_intro_badge']) ?></span>
      <?php endif; ?>
      <h2 class="ap-intro__title" id="pm-intro-title" data-reveal style="--d:1"><?= nl2br(e($s['pm_intro_title']), false) ?></h2>
      <div class="ap-intro__text" data-reveal style="--d:2"><?= text_paragraphs($s['pm_intro_text']) ?></div>
      <div class="pm-actions" data-reveal style="--d:3">
        <a class="btn-pill" href="#meals"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text"><?= e($s['pm_cta_button1']) ?></span></a>
      </div>
    </div>
    <?php if (trim($s['pm_intro_image']) !== ''): [$iw, $ih] = local_img_size($s['pm_intro_image']); ?>
      <div class="ap-intro__media" data-reveal="zoom" style="--d:1">
        <img src="<?= e(img_url($s['pm_intro_image'])) ?>" alt="" width="<?= $iw ?>" height="<?= $ih ?>" loading="lazy">
      </div>
    <?php endif; ?>
  </section>

  <!-- ============ GOOD FOOD FOR EVERY LIFESTYLE ============ -->
  <section class="ap-story pm-life" aria-labelledby="pm-life-title">
    <?php if (trim($s['pm_life_image']) !== ''): ?>
      <img class="ap-story__bg" src="<?= e(img_url($s['pm_life_image'])) ?>" alt="" loading="lazy">
    <?php endif; ?>
    <h2 class="ap-story__title" id="pm-life-title" data-reveal><?= e($s['pm_life_title']) ?></h2>
    <div class="ap-story__text" data-reveal style="--d:2"><?= text_paragraphs($s['pm_life_text']) ?></div>
  </section>

  <!-- ============ THE MEALS ============ -->
  <?php if ($meals): ?>
  <section class="pm-meals" id="meals" aria-labelledby="pm-meals-title">
    <div class="pm-head">
      <h2 class="pm-h2" id="pm-meals-title" data-reveal><?= e($s['pm_meals_title']) ?></h2>
      <?php if (trim($s['pm_meals_text']) !== ''): ?><p class="pm-lead" data-reveal style="--d:1"><?= e($s['pm_meals_text']) ?></p><?php endif; ?>
    </div>
    <ol class="pm-meals__grid">
      <?php foreach ($meals as $i => $m): ?>
        <li class="pm-meal" data-reveal style="--d:<?= $i % 3 ?>">
          <div class="pm-meal__media">
            <?php if ($m['image'] !== ''): [$mw, $mh] = local_img_size($m['image']); ?>
              <img src="<?= e(img_url($m['image'])) ?>" alt="<?= e($m['title']) ?><?= $m['sides'] !== '' ? ' with ' . e(strtolower($m['sides'])) : '' ?>" width="<?= $mw ?>" height="<?= $mh ?>" loading="lazy">
            <?php endif; ?>
            <span class="pm-meal__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
          </div>
          <div class="pm-meal__body">
            <h3><?= e($m['title']) ?></h3>
            <?php if ($m['sides'] !== ''): ?><p class="pm-meal__sides"><?= e($m['sides']) ?></p><?php endif; ?>
            <?php if ($m['text'] !== ''): ?><p class="pm-meal__text"><?= e($m['text']) ?></p><?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>
  <?php endif; ?>

  <!-- ============ WHY CHOOSE ============ -->
  <?php if ($why): ?>
  <section class="ap-why pm-why" aria-labelledby="pm-why-title">
    <div class="ap-why__head">
      <h2 class="ap-why__title" id="pm-why-title" data-reveal><?= e($s['pm_why_title']) ?></h2>
    </div>
    <ul class="ap-why__cards pm-why__cards">
      <?php foreach ($why as $i => [$t, $txt]): ?>
        <li data-reveal style="--d:<?= $i % 3 + 1 ?>">
          <div class="ap-card">
            <span class="pm-check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h3><?= e($t) ?></h3>
            <?php if ($txt !== ''): ?><p><?= e($txt) ?></p><?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <!-- ============ MADE FOR EVERYONE ============ -->
  <section class="pm-everyone" aria-labelledby="pm-everyone-title">
    <?php if (trim($s['pm_everyone_image']) !== ''): [$ew, $eh] = local_img_size($s['pm_everyone_image']); ?>
      <figure class="pm-everyone__media" data-reveal="zoom">
        <img src="<?= e(img_url($s['pm_everyone_image'])) ?>" alt="" width="<?= $ew ?>" height="<?= $eh ?>" loading="lazy">
      </figure>
    <?php endif; ?>
    <div class="pm-everyone__content">
      <?php if (trim($s['pm_everyone_badge']) !== ''): ?><span class="tag tag--gold" data-reveal><?= e($s['pm_everyone_badge']) ?></span><?php endif; ?>
      <h2 class="pm-h2" id="pm-everyone-title" data-reveal style="--d:1"><?= e($s['pm_everyone_title']) ?></h2>
      <div class="pm-text" data-reveal style="--d:2"><?= text_paragraphs($s['pm_everyone_text']) ?></div>
      <?php if ($tags): ?>
        <ul class="pm-tags" data-reveal style="--d:3"><?php foreach ($tags as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </div>
  </section>

  <!-- ============ HOW IT WORKS ============ -->
  <?php if ($steps): ?>
  <section class="pm-how" aria-labelledby="pm-how-title">
    <h2 class="pm-h2 pm-how__title" id="pm-how-title" data-reveal><?= e($s['pm_how_title']) ?></h2>
    <ol class="pm-steps">
      <?php foreach ($steps as $i => [$t, $txt]): ?>
        <li class="pm-step" data-reveal style="--d:<?= $i + 1 ?>">
          <span class="pm-step__num" aria-hidden="true"><?= $i + 1 ?></span>
          <h3><?= e($t) ?></h3>
          <p><?= e($txt) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>
  <?php endif; ?>

  <!-- ============ CALL TO ACTION ============ -->
  <section class="pm-cta" aria-labelledby="pm-cta-title">
    <h2 class="pm-cta__title" id="pm-cta-title" data-reveal><?= e($s['pm_cta_title']) ?></h2>
    <div class="pm-cta__text" data-reveal style="--d:1"><?= text_paragraphs($s['pm_cta_text']) ?></div>
    <?php if (trim($s['pm_tagline']) !== ''): ?><p class="pm-cta__tagline" data-reveal style="--d:2"><strong><?= e($s['pm_tagline']) ?></strong></p><?php endif; ?>
    <div class="pm-actions pm-actions--center" data-reveal style="--d:3">
      <?php if (trim($s['pm_cta_button1']) !== '' && $meals): ?>
        <a class="pm-btn pm-btn--gold" href="#meals"><?= e($s['pm_cta_button1']) ?></a>
      <?php endif; ?>
      <?php if (trim($s['pm_cta_button2']) !== ''): ?>
        <a class="pm-btn" href="<?= e(base_url('contact.php')) ?>"><?= e($s['pm_cta_button2']) ?></a>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
