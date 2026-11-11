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
