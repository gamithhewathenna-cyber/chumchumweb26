<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/menu.php';

$s    = get_settings();
$code = menu_combine((string) $s['menu_html'], (string) $s['menu_css']);

$pageTitle       = $s['menu_title'] . ' | ' . $s['site_name'] . ' — Sri Lankan Restaurant, Clayton';
$pageDescription = $s['menu_meta_description'];
if (trim($s['menu_banner']) !== '') {
    $heroPreload = $s['menu_banner'];
}

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="menu-page">

  <!-- ============ PAGE TITLE ============ -->
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title" id="page-title"><?= e($s['menu_title']) ?></h1>
    <?php if (trim($s['menu_intro']) !== ''): ?>
      <p class="page-hero__intro"><?= e($s['menu_intro']) ?></p>
    <?php endif; ?>
  </section>

  <?php if (trim($s['menu_banner']) !== ''): ?>
    <div class="page-banner" data-reveal="wipe">
      <img src="<?= e(img_url($s['menu_banner'])) ?>" alt="" width="2416" height="675" fetchpriority="high">
    </div>
  <?php endif; ?>

  <!-- ============ THE MENU (pasted in Admin → Menu Page) ============ -->
  <section class="menu-body" aria-label="Our menu">
    <?php if (trim($code) === ''): ?>
      <div class="menu-empty" data-reveal>
        <p><?= e($s['menu_empty_text']) ?></p>
        <?php if ($s['phone'] !== ''): ?>
          <a class="reservation__phone" href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="menu-embed" data-reveal>
        <!-- The pasted menu runs in a sealed (sandboxed) frame: its scripts work, but it can't touch the site -->
        <iframe class="menu-frame" title="<?= e($s['menu_title']) ?>" sandbox="<?= MENU_SANDBOX ?>"
                srcdoc="<?= e(menu_document($code)) ?>"></iframe>
      </div>
    <?php endif; ?>
  </section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
