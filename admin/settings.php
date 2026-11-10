<?php
/** Site Settings → options shared by every page (contact, links, footer, SEO, maintenance). */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

/* Old links to home-page tabs now live under Home Page */
$moved = ['hero' => 'hero', 'about' => 'about', 'reservation' => 'reservation'];
if (isset($_GET['tab']) && is_string($_GET['tab']) && isset($moved[$_GET['tab']])) {
    redirect('home.php?section=' . $moved[$_GET['tab']]);
}

/** key => [label, type (text|textarea|email|link|checkbox|image), help] */
$sections = [
    'general' => ['General & Contact', [
        'site_name'      => ['Restaurant name', 'text', ''],
        'phone'          => ['Phone number', 'text', 'Used in the header, reservation section and footer. Tapping it on a phone starts a call.'],
        'email'          => ['Email address', 'email', ''],
        'address_line1'  => ['Address line 1', 'text', ''],
        'address_line2'  => ['Address line 2', 'text', ''],
        'copyright_name' => ['Copyright name', 'text', 'Footer shows: © ' . date('Y') . ' <this name>. All rights reserved.'],
    ]],
    'links' => ['Links', [
        'order_url'     => ['"Order Now" button link', 'link', 'Full web address, e.g. https://… — use # until it is ready.'],
        'customer_url'  => ['"Current Customer?" link', 'link', ''],
        'facebook_url'  => ['Facebook page', 'link', ''],
        'instagram_url' => ['Instagram profile', 'link', ''],
    ]],
    'footer' => ['Footer', [
        'footer_text_1' => ['Footer paragraph 1', 'textarea', ''],
        'footer_text_2' => ['Footer paragraph 2', 'textarea', ''],
    ]],
    'seo' => ['Search (SEO)', [
        'meta_title'       => ['Page title', 'text', 'Shown in the browser tab and Google results. Aim for under 65 characters.'],
        'meta_description' => ['Meta description', 'textarea', 'Shown under the title in Google results. Aim for 120–160 characters.'],
    ]],
    'maintenance' => ['Maintenance Mode', [
        'maintenance_mode'    => ['Turn on maintenance mode', 'checkbox', 'Visitors will see a "back soon" page instead of the website. You will still see the full site while logged in to the admin.'],
        'maintenance_title'   => ['Heading', 'text', ''],
        'maintenance_message' => ['Message', 'textarea', 'Your phone number and email are shown underneath automatically.'],
    ]],
];

$tab = (string) ($_GET['tab'] ?? '');
$tab = isset($sections[$tab]) ? $tab : 'general';
[$tabLabel, $fields] = $sections[$tab];

$success = $tab === 'maintenance'
    ? function (array $saved): string {
        return ($saved['maintenance_mode'] ?? '0') === '1'
            ? 'Maintenance mode is ON. Visitors now see the "back soon" page.'
            : 'Maintenance mode is OFF. The website is live for everyone.';
    }
    : $tabLabel . ' settings saved.';

$current = settings_form_handle($pdo, $fields, 'settings.php?tab=' . $tab, $success);

admin_page_start('Site Settings', 'settings');
?>
  <nav class="tabs" aria-label="Settings sections">
    <?php foreach ($sections as $key => [$label]): ?>
      <a href="settings.php?tab=<?= e($key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
<?php
settings_form_render($fields, $current, $tabLabel, $tab === 'maintenance'
    ? '<a class="btn btn--ghost" href="' . e(base_url('index.php?preview_maintenance=1')) . '" target="_blank" rel="noopener">Preview page ↗</a>'
    : '');
admin_page_end();
