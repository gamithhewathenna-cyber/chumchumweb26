<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/menu.php';

$s    = get_settings();
$menu = menu_prepare((string) $s['menu_html'], (string) $s['menu_css']);

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
    <?php if ($menu['html'] === ''): ?>
      <div class="menu-empty" data-reveal>
        <p><?= e($s['menu_empty_text']) ?></p>
        <?php if ($s['phone'] !== ''): ?>
          <a class="reservation__phone" href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php if ($menu['page_css'] !== ''): ?>
        <style><?= $menu['page_css'] /* fonts used by the menu */ ?></style>
      <?php endif; ?>
      <div class="menu-embed" data-reveal>
        <template shadowrootmode="open">
          <style>
            :host { display: block; color: #fff; font-family: "DM Sans", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.45; }
            *, *::before, *::after { box-sizing: border-box; }
            img, video, svg { max-width: 100%; height: auto; }
            a { color: #e7af27; }
            table { border-collapse: collapse; }
          </style>
          <style><?= $menu['css'] /* pasted menu CSS — only affects the menu */ ?></style>
          <?= $menu['html'] /* pasted menu HTML (cleaned in includes/menu.php) */ ?>
        </template>
      </div>
    <?php endif; ?>
  </section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
