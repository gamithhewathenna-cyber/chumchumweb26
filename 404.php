<?php
/** "Page not found" — shown for any address that doesn't exist (see ErrorDocument in .htaccess). */
require_once __DIR__ . '/includes/bootstrap.php';

http_response_code(404);
$s = get_settings();

$pageTitle       = 'Page not found | ' . $s['site_name'];
$pageDescription = $s['meta_description'];
$canonical       = '';                 // no canonical address for an error page
$robots          = 'noindex, follow';  // keep it out of Google

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="notfound-page">
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title" id="page-title">Oops!</h1>
    <p class="page-hero__intro">We couldn't find that page — it may have moved, or the link may be mistyped. Let's get you back to something delicious.</p>
  </section>

  <section class="notfound" aria-label="Helpful links">
    <p class="notfound__code" aria-hidden="true">404</p>
    <div class="notfound__links">
      <a class="btn-pill" href="<?= e(base_url('')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Home</span></a>
      <a class="btn-pill" href="<?= e(base_url('menu.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">See the menu</span></a>
      <a class="btn-pill" href="<?= e(base_url('contact.php')) ?>"><span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span><span class="btn-pill__text">Contact us</span></a>
    </div>
    <?php if ($s['phone'] !== ''): ?>
      <p class="notfound__call">Or call us: <a href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a></p>
    <?php endif; ?>
  </section>
</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
