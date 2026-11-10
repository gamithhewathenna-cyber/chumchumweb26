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

$cards = [
    ['settings.php',     'Site Settings',   'Headings, text, phone, address, links and main images.', null],
    ['categories.php',   'Menu Categories', 'The dish pills around the hero bowl.', count_rows($pdo, 'SELECT COUNT(*) FROM menu_categories WHERE is_active = 1') . ' shown'],
    ['hours.php',        'Opening Hours',   'Rows in the yellow Opening Hours card.', count_rows($pdo, 'SELECT COUNT(*) FROM opening_hours') . ' rows'],
    ['showcase.php',     'Showcase Grid',   'The 7 tiles under the About section.', '7 tiles'],
    ['testimonials.php', 'Testimonials',    'Guest reviews in the yellow slider.', count_rows($pdo, 'SELECT COUNT(*) FROM testimonials WHERE is_active = 1') . ' shown'],
    ['account.php',      'Account & Users', 'Change your password and manage admin users.', null],
];

admin_page_start('Dashboard', 'index');
?>
  <p class="lead">Welcome back, <strong><?= e($user['username']) ?></strong>. Choose what you'd like to update — changes appear on the website immediately.</p>

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
