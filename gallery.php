<?php
require_once __DIR__ . '/includes/bootstrap.php';

$s      = get_settings();
$photos = get_gallery_images();

$pageTitle       = $s['gallery_title'] . ' | ' . $s['site_name'] . ' — Sri Lankan Restaurant, Clayton';
$pageDescription = $s['gallery_meta_description'];

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="gallery-page">

  <!-- ============ PAGE TITLE ============ -->
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title" id="page-title"><?= e($s['gallery_title']) ?></h1>
    <?php if (trim($s['gallery_intro']) !== ''): ?>
      <p class="page-hero__intro"><?= e($s['gallery_intro']) ?></p>
    <?php endif; ?>
  </section>

  <!-- ============ PHOTO GRID ============ -->
  <section class="gallery" aria-label="Photo gallery">
    <?php if (!$photos): ?>
      <p class="gallery__empty"><?= e($s['gallery_empty_text']) ?></p>
    <?php else: ?>
      <ul class="gallery__grid">
        <?php foreach ($photos as $i => $p):
            $size = in_array($p['size'], ['wide', 'tall', 'big'], true) ? $p['size'] : 'normal';
            $src  = img_url($p['image']); ?>
          <li class="gallery__item gallery__item--<?= $size ?>" data-reveal="zoom" style="--d:<?= $i % 6 ?>">
            <a class="gallery__link" href="<?= e($src) ?>" data-gallery-index="<?= $i ?>" data-caption="<?= e($p['caption']) ?>">
              <img src="<?= e($src) ?>" alt="<?= e($p['caption']) ?>" loading="<?= $i < 6 ? 'eager' : 'lazy' ?>" decoding="async">
              <?php if (trim((string) $p['caption']) !== ''): ?>
                <span class="gallery__caption"><?= e($p['caption']) ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

</main>

<!-- Full-screen viewer (opened by main.js) -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
  <button class="lightbox__close" type="button" data-lightbox-close aria-label="Close">&times;</button>
  <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Previous photo"><?= icon_chevron('left') ?></button>
  <figure class="lightbox__figure">
    <img class="lightbox__img" src="" alt="">
    <figcaption class="lightbox__caption"><span class="lightbox__text"></span><span class="lightbox__count"></span></figcaption>
  </figure>
  <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Next photo"><?= icon_chevron('right') ?></button>
</div>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
