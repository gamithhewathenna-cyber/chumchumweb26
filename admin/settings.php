<?php
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

/**
 * Editable settings, grouped into tabs.
 * Each field: key => [label, type (text|textarea|email|image), help]
 */
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
        'order_url'     => ['"Order Now" button link', 'text', 'Full web address, e.g. https://… — use # until it is ready.'],
        'customer_url'  => ['"Current Customer?" link', 'text', ''],
        'facebook_url'  => ['Facebook page', 'text', ''],
        'instagram_url' => ['Instagram profile', 'text', ''],
    ]],
    'hero' => ['Hero', [
        'hero_badge'    => ['Small badge above the title', 'text', ''],
        'hero_title'    => ['Main title', 'text', ''],
        'hero_subtitle' => ['Subtitle', 'text', ''],
        'hero_image'    => ['Hero dish image', 'image', 'Best as a transparent PNG/WebP, about 1215 × 747 px.'],
    ]],
    'about' => ['About', [
        'about_badge'    => ['Badge', 'text', ''],
        'about_title'    => ['Heading', 'textarea', ''],
        'about_text'     => ['Paragraph', 'textarea', ''],
        'about_image'    => ['Tall photo (left)', 'image', 'Portrait, about 930 × 1330 px.'],
        'interior_image' => ['Interior photo', 'image', 'About 560 × 550 px.'],
        'hours_title'    => ['Opening hours card title', 'text', 'Edit the hours themselves under Opening Hours.'],
    ]],
    'reservation' => ['Reservation', [
        'reservation_title' => ['Heading', 'text', ''],
        'reservation_text'  => ['Paragraph', 'textarea', ''],
        'reservation_label' => ['Label above the phone number', 'text', ''],
        'reservation_image' => ['Photo', 'image', 'Landscape, about 1066 × 614 px.'],
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
$current = get_settings();

if (is_post()) {
    verify_csrf();
    $input = $_POST['s'] ?? [];
    $save  = [];
    try {
        foreach ($fields as $key => [$label, $type]) {
            if ($type === 'image') {
                $uploaded = handle_upload('img_' . $key);
                if ($uploaded !== null) {
                    $save[$key] = $uploaded;
                }
                continue;
            }
            if ($type === 'checkbox') {
                $save[$key] = is_array($input) && !empty($input[$key]) ? '1' : '0';
                continue;
            }
            $value = is_array($input) && isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
            if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Please enter a valid email address.');
            }
            if ($tab === 'links') {
                $value = clean_link($value);
            }
            $save[$key] = $value;
        }
        $st = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($save as $key => $value) {
            $st->execute([$key, $value]);
        }
        if ($tab === 'maintenance') {
            flash('success', ($save['maintenance_mode'] ?? '0') === '1'
                ? 'Maintenance mode is ON. Visitors now see the "back soon" page.'
                : 'Maintenance mode is OFF. The website is live for everyone.');
        } else {
            flash('success', $tabLabel . ' settings saved.');
        }
        redirect('settings.php?tab=' . $tab);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        // Keep what was typed so nothing is lost
        foreach ($fields as $key => [$label, $type]) {
            if ($type !== 'image' && is_array($input) && isset($input[$key]) && is_string($input[$key])) {
                $current[$key] = $input[$key];
            }
        }
    }
}

admin_page_start('Site Settings', 'settings');
?>
  <nav class="tabs" aria-label="Settings sections">
    <?php foreach ($sections as $key => [$label]): ?>
      <a href="settings.php?tab=<?= e($key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

  <form method="post" enctype="multipart/form-data" class="panel">
    <?= csrf_field() ?>
    <h2 class="panel__title"><?= e($tabLabel) ?></h2>
    <?php foreach ($fields as $key => [$label, $type, $help]): ?>
      <?php if ($type === 'image'): ?>
        <?= field_image('img_' . $key, $label, $current[$key] ?? '', $help) ?>
      <?php elseif ($type === 'checkbox'): ?>
        <?= field_checkbox('s[' . $key . ']', $label, ($current[$key] ?? '0') === '1', $help) ?>
      <?php else: ?>
        <?= field_text('s[' . $key . ']', $label, $current[$key] ?? '', [
            'type' => $type, 'help' => $help, 'rows' => $key === 'about_text' ? 6 : 3,
        ]) ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <div class="actions">
      <button class="btn btn--primary" type="submit">Save changes</button>
      <?php if ($tab === 'maintenance'): ?>
        <a class="btn btn--ghost" href="<?= e(base_url('index.php?preview_maintenance=1')) ?>" target="_blank" rel="noopener">Preview page ↗</a>
      <?php endif; ?>
    </div>
  </form>
<?php admin_page_end();
