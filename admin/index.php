<?php
require __DIR__ . '/includes/admin.php';
$user = require_login();
$pdo  = admin_db();

function count_rows(PDO $pdo, string $sql): int
{
    try {
        return (int) $pdo->query($sql)->fetchColumn();
    } catch (Throwable $ex) {
        return 0;
    }
}

$homeCards = [
    ['home.php?section=hero',        'Hero',            'Default badge and photo for the dish slider.', null],
    ['categories.php',               'Slider',          'Dish slides: photo, title, subtitle and pill button.', count_rows($pdo, 'SELECT COUNT(*) FROM menu_categories WHERE is_active = 1') . ' slides'],
    ['home.php?section=about',       'About',           'Story text, chef and interior photos.', null],
    ['hours.php',                    'Opening Hours',   'Rows in the yellow Opening Hours card.', count_rows($pdo, 'SELECT COUNT(*) FROM opening_hours') . ' rows'],
    ['showcase.php',                 'Showcase Grid',   'The 7 tiles under the About section.', '7 tiles'],
    ['home.php?section=reservation', 'Reservation',     'Booking heading, text and photo.', null],
    ['testimonials.php',             'Testimonials',    'Guest reviews in the yellow slider.', count_rows($pdo, 'SELECT COUNT(*) FROM testimonials WHERE is_active = 1') . ' shown'],
];
$aboutCards = [
    ['about-page.php?section=header', 'Title & Banner', 'Page title, intro and the wide banner photo.', null],
    ['about-page.php?section=intro',  'Intro',          '"A Taste Of Sri Lanka" text and the tall photo.', null],
    ['about-page.php?section=story',  'Our Story',      'Story text and background photo.', null],
    ['about-features.php',           'Why Choose Us',  'Heading and the feature cards.', null],
];
$menuCards = [
    ['menu-page.php',            'Menu Code',           'Paste your menu (HTML with its CSS), preview it live, restore the previous version.', null],
    ['menu-page.php?tab=images', 'Menu Images',         'Upload photos for the menu and copy their address.', null],
    ['menu-page.php?tab=page',   'Title, Banner & SEO', 'Page heading, intro text and banner photo.', null],
];
$galleryCards = [
    ['gallery-page.php',          'Photos',      'Upload photos, set captions and tile sizes, reorder or hide them.', count_rows($pdo, 'SELECT COUNT(*) FROM gallery_images WHERE is_active = 1') . ' shown'],
    ['gallery-page.php?tab=page', 'Title & SEO', 'Page heading, intro text and Google description.', null],
];
$unreadMsgs   = unread_message_count();
$contactCards = [
    ['messages.php',     'Messages',     'Everything sent through the website contact form.',
        $unreadMsgs ? $unreadMsgs . ' new' : count_rows($pdo, 'SELECT COUNT(*) FROM contact_messages') . ' total'],
    ['contact-page.php', 'Page Content', 'Title, intro, banner photo, map and email notifications.', null],
];
$cards = [
    ['settings.php', 'Site Settings',   'Phone, email, address, links, footer, SEO and maintenance mode.', null],
    ['account.php',  'Account & Users', 'Change your password and manage admin users.', null],
];

admin_page_start('Dashboard', 'index');
?>
  <p class="lead">Welcome back, <strong><?= e($user['username']) ?></strong>. Choose what you'd like to update — changes appear on the website immediately.</p>

  <?php
  $checks = golive_checks($pdo);
  $done   = count(array_filter($checks, fn($c) => $c[0]));
  $todo   = array_filter($checks, fn($c) => !$c[0]);
  ?>
  <section class="panel golive<?= $todo ? '' : ' golive--done' ?>" aria-labelledby="golive-title">
    <div class="golive__head">
      <h2 class="panel__title" id="golive-title"><?= $todo ? 'Go-live checklist' : 'Ready to go live ✓' ?></h2>
      <span class="golive__score"><?= $done ?> / <?= count($checks) ?> ready</span>
    </div>
    <div class="golive__bar" role="progressbar" aria-valuemin="0" aria-valuemax="<?= count($checks) ?>" aria-valuenow="<?= $done ?>"><span style="width:<?= round($done / max(1, count($checks)) * 100) ?>%"></span></div>
    <?php if ($todo): ?>
      <ul class="golive__list">
        <?php foreach ($todo as [$ok, $title, $detail, $link]): ?>
          <li class="golive__item">
            <span class="golive__mark" aria-hidden="true">!</span>
            <span class="golive__text"><strong><?= e($title) ?></strong><?php if ($detail !== ''): ?><br><span class="muted small"><?= e($detail) ?></span><?php endif; ?></span>
            <?php if ($link): ?><a class="btn btn--small" href="<?= e($link) ?>">Fix →</a><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <details class="golive__done">
      <summary><?= $done ?> item<?= $done === 1 ? '' : 's' ?> done</summary>
      <ul class="golive__list">
        <?php foreach ($checks as [$ok, $title]): if (!$ok) continue; ?>
          <li class="golive__item is-ok"><span class="golive__mark" aria-hidden="true">✓</span><span class="golive__text"><?= e($title) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </details>
  </section>

  <h2 class="section-title">Home Page</h2>
  <div class="cards">
    <?php foreach ($homeCards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
        <?php if ($meta !== null): ?><span class="badge"><?= e($meta) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">About Page</h2>
  <div class="cards">
    <?php foreach ($aboutCards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Menu Page</h2>
  <div class="cards">
    <?php foreach ($menuCards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Gallery Page</h2>
  <div class="cards">
    <?php foreach ($galleryCards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
        <?php if ($meta !== null): ?><span class="badge"><?= e($meta) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Contact Page</h2>
  <div class="cards">
    <?php foreach ($contactCards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
        <?php if ($meta !== null): ?><span class="badge<?= $href === 'messages.php' && $unreadMsgs ? ' badge--warn' : '' ?>"><?= e($meta) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Website</h2>
  <div class="cards">
    <?php foreach ($cards as [$href, $title, $text, $meta]): ?>
      <a class="card card--link" href="<?= e($href) ?>">
        <h2><?= e($title) ?></h2>
        <p><?= e($text) ?></p>
        <?php if ($meta !== null): ?><span class="badge"><?= e($meta) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <p class="muted small">
    <?php if (!empty($user['last_login_at'])): ?>Last login: <?= e(date('j M Y, g:ia', strtotime((string) $user['last_login_at']))) ?> · <?php endif; ?>
    <a href="<?= e(base_url('')) ?>" target="_blank" rel="noopener">View website ↗</a>
  </p>
<?php admin_page_end();
