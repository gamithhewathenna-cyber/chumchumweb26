<?php /** @var array $s */ ?>
<footer class="site-footer" id="contact">
  <div class="site-footer__inner">
    <div class="site-footer__brand" data-reveal>
      <a href="<?= e(base_url('index.php')) ?>" class="site-footer__logo" aria-label="<?= e($s['site_name']) ?> — Home">
        <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e($s['site_name']) ?>" width="204" height="159" loading="lazy">
      </a>
      <p><?= e($s['footer_text_1']) ?></p>
      <p><?= e($s['footer_text_2']) ?></p>
    </div>

    <div class="site-footer__col site-footer__links" data-reveal style="--d:1">
      <h2>Link</h2>
      <ul>
        <?php foreach (get_footer_links() as $link): ?>
          <li><a href="<?= e(link_url($link['url'])) ?>"><?= e($link['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="site-footer__col site-footer__contact" data-reveal style="--d:2">
      <h2>Connect us</h2>
      <address>
        <span><?= e($s['address_line1']) ?><br><?= e($s['address_line2']) ?></span>
        <a href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a>
        <a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a>
      </address>
      <div class="social">
        <a href="<?= e(link_url($s['facebook_url'])) ?>" aria-label="Facebook" target="_blank" rel="noopener"><?= icon_facebook() ?></a>
        <a href="<?= e(link_url($s['instagram_url'])) ?>" aria-label="Instagram" target="_blank" rel="noopener"><?= icon_instagram() ?></a>
      </div>
    </div>
  </div>
  <div class="site-footer__bottom">
    <p>&copy; <?= date('Y') ?> <?= e($s['copyright_name']) ?>. All rights reserved.</p>
  </div>
</footer>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
