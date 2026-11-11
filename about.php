<?php
require_once __DIR__ . '/includes/bootstrap.php';

$s        = get_settings();
$features = get_about_features();

$pageTitle       = $s['aboutpage_title'] . ' | ' . $s['site_name'] . ' — Sri Lankan Restaurant, Clayton';
$pageDescription = $s['aboutpage_meta_description'];
$heroPreload     = $s['aboutpage_banner'];

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="about-page">

  <!-- ============ PAGE TITLE ============ -->
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title" id="page-title"><?= e($s['aboutpage_title']) ?></h1>
    <?php if (trim($s['aboutpage_intro']) !== ''): ?>
      <p class="page-hero__intro"><?= e($s['aboutpage_intro']) ?></p>
    <?php endif; ?>
  </section>

  <?php if (trim($s['aboutpage_banner']) !== ''): ?>
    <div class="page-banner" data-reveal="wipe">
      <img src="<?= e(img_url($s['aboutpage_banner'])) ?>" alt="" width="2416" height="675" fetchpriority="high">
    </div>
  <?php endif; ?>

  <!-- ============ INTRO: A TASTE OF SRI LANKA ============ -->
  <section class="ap-intro" aria-labelledby="ap-intro-title">
    <div class="ap-intro__content">
      <?php if (trim($s['aboutpage_intro_badge']) !== ''): ?>
        <span class="tag tag--gold" data-reveal><?= e($s['aboutpage_intro_badge']) ?></span>
      <?php endif; ?>
      <h2 class="ap-intro__title" id="ap-intro-title" data-reveal style="--d:1"><?= nl2br(e($s['aboutpage_intro_title']), false) ?></h2>
      <div class="ap-intro__text" data-reveal style="--d:2"><?= text_paragraphs($s['aboutpage_intro_text']) ?></div>
    </div>
    <?php if (trim($s['aboutpage_intro_image']) !== ''): ?>
      <div class="ap-intro__media" data-reveal="zoom" style="--d:1">
        <img src="<?= e(img_url($s['aboutpage_intro_image'])) ?>" alt="" width="720" height="1236" loading="lazy">
      </div>
    <?php endif; ?>
  </section>

  <!-- ============ OUR STORY ============ -->
  <section class="ap-story" aria-labelledby="ap-story-title">
    <?php if (trim($s['aboutpage_story_image']) !== ''): ?>
      <img class="ap-story__bg" src="<?= e(img_url($s['aboutpage_story_image'])) ?>" alt="" loading="lazy">
    <?php endif; ?>
    <h2 class="ap-story__title" id="ap-story-title" data-reveal><?= e($s['aboutpage_story_title']) ?></h2>
    <div class="ap-story__text" data-reveal style="--d:2"><?= text_paragraphs($s['aboutpage_story_text']) ?></div>
  </section>

  <!-- ============ WHY CHOOSE ============ -->
  <section class="ap-why" aria-labelledby="ap-why-title">
    <div class="ap-why__head">
      <?php if (trim($s['aboutpage_why_badge']) !== ''): ?>
        <span class="tag tag--gold" data-reveal><?= e($s['aboutpage_why_badge']) ?></span>
      <?php endif; ?>
      <h2 class="ap-why__title" id="ap-why-title" data-reveal style="--d:1"><?= e($s['aboutpage_why_title']) ?></h2>
      <?php if (trim($s['aboutpage_why_text']) !== ''): ?>
        <p class="ap-why__text" data-reveal style="--d:2"><?= e($s['aboutpage_why_text']) ?></p>
      <?php endif; ?>
    </div>
    <?php if ($features): ?>
      <ul class="ap-why__cards">
        <?php foreach ($features as $i => $f): ?>
          <li data-reveal style="--d:<?= $i + 2 ?>">
            <div class="ap-card">
              <h3><?= e($f['title']) ?></h3>
              <p><?= e($f['text']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <?php if (($s['aboutpage_show_reservation'] ?? '1') === '1'): ?>
    <!-- ============ RESERVATION (same as the homepage) ============ -->
    <?php require __DIR__ . '/includes/partials/reservation.php'; ?>
  <?php endif; ?>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
