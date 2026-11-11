<?php
/** Contact Page → Page Content: texts, banner, map and where notifications go. */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

$sections = [
    'page' => ['Page text & banner', [
        'contact_title'   => ['Page title', 'text', 'The big heading at the top, e.g. "Contact Us".'],
        'contact_intro'   => ['Intro text', 'textarea', 'The short paragraph beside the title.'],
        'contact_banner'  => ['Banner photo', 'image', 'Wide photo under the title, about 2400 × 680 px. Darker photos look best.'],
        'contact_heading' => ['Address heading', 'text', 'e.g. "Our Address".'],
        'contact_text'    => ['Address text', 'textarea', 'The line under the heading.'],
    ]],
    'map' => ['Map', [
        'contact_map_query' => ['Map location', 'text', 'The address (or place name) Google Maps should show, e.g. "47 Morton St, Clayton VIC 3168, Australia". Leave empty to hide the map.'],
    ]],
    'form' => ['Form & notifications', [
        'contact_success'        => ['Thank-you message', 'textarea', 'Shown after someone sends a message.'],
        'contact_notify_enabled' => ['Email me when a new message arrives', 'checkbox', 'Every message is also saved under Messages, even if emails are off.'],
        'contact_notify_email'   => ['Send notifications to', 'email', 'Leave empty to use the restaurant email from Site Settings.'],
    ]],
    'seo' => ['Search (SEO)', [
        'contact_meta_description' => ['Meta description', 'textarea', 'Shown under the page title in Google results. Aim for 120–160 characters.'],
    ]],
];

$tab = (string) ($_GET['tab'] ?? '');
$tab = isset($sections[$tab]) ? $tab : 'page';
[$label, $fields] = $sections[$tab];

$current = settings_form_handle($pdo, $fields, 'contact-page.php?tab=' . $tab, $label . ' saved.');

admin_page_start('Contact Page', 'messages');
contact_tabs('content');
?>
  <p class="muted">The phone number, email and address shown on the Contact page come from <a href="settings.php?tab=general">Site Settings → General &amp; Contact</a>.</p>
  <nav class="subtabs" aria-label="Page content sections">
    <?php foreach ($sections as $key => [$l]): ?>
      <a href="contact-page.php?tab=<?= e($key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= e($l) ?></a>
    <?php endforeach; ?>
  </nav>
<?php
settings_form_render($fields, $current, $label,
    '<a class="btn btn--ghost" href="' . e(base_url('contact.php')) . '" target="_blank" rel="noopener">View contact page ↗</a>');
admin_page_end();
