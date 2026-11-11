<?php
/**
 * Home Page → Hero / About / Reservation text & images.
 * (Menu Categories, Opening Hours, Showcase Grid and Testimonials have their
 * own pages, all linked from the same Home Page tab bar.)
 */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

$sections = [
    'hero' => ['Hero', 'The hero is a slider: each dish in Menu Categories is one slide, with its own title, subtitle and photo (edit them under Menu Categories). The values below are the defaults used when a dish leaves a field empty.', [
        'hero_badge'    => ['Default badge', 'text', 'The small pill above the title on every slide (unless a dish sets its own).'],
        'hero_title'    => ['Fallback title', 'text', 'Only shown if there are no menu categories at all.'],
        'hero_subtitle' => ['Fallback subtitle', 'text', 'Only shown if there are no menu categories at all.'],
        'hero_image'    => ['Default dish photo', 'image', 'Shown for any dish that has no slide photo yet. Transparent PNG/WebP, about 1215 × 747 px.'],
    ]],
    'about' => ['About', 'The chef photo, story text and interior photo. The hours themselves are under Opening Hours.', [
        'about_badge'    => ['Badge', 'text', ''],
        'about_title'    => ['Heading', 'textarea', ''],
        'about_text'     => ['Paragraph', 'textarea', ''],
        'about_image'    => ['Tall photo (left)', 'image', 'Portrait, about 930 × 1330 px.'],
        'interior_image' => ['Interior photo', 'image', 'About 560 × 550 px.'],
        'hours_title'    => ['Opening hours card title', 'text', ''],
    ]],
    'reservation' => ['Reservation', 'The booking section with the large phone number. The number itself is set in Site Settings → General & Contact.', [
        'reservation_title' => ['Heading', 'text', ''],
        'reservation_text'  => ['Paragraph', 'textarea', ''],
        'reservation_label' => ['Label above the phone number', 'text', ''],
        'reservation_image' => ['Photo', 'image', 'Landscape, about 1066 × 614 px.'],
    ]],
];

$section = (string) ($_GET['section'] ?? '');
$section = isset($sections[$section]) ? $section : 'hero';
[$label, $intro, $fields] = $sections[$section];

$current = settings_form_handle($pdo, $fields, 'home.php?section=' . $section, $label . ' section saved.');

admin_page_start('Home Page', 'home');
home_tabs($section);
?>
  <p class="muted"><?= e($intro) ?></p>
<?php
settings_form_render($fields, $current, $label,
    '<a class="btn btn--ghost" href="' . e(base_url('index.php')) . '" target="_blank" rel="noopener">View home page ↗</a>');
admin_page_end();
