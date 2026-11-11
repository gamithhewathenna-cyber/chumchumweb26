<?php
/** Reservation section — shared by the homepage and the About page. @var array $s settings */
?>
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
