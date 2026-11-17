<?php
/**
 * Local SEO: the suburb pages (/sri-lankan-restaurant/…) and dish pages (/dishes/…).
 * Each page is written automatically from real facts; here the owner can switch them
 * on/off, set the map location and add their own words + photos — local, first-hand
 * text is what makes these pages rank best.
 */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_settings_mediumtext($pdo);

$s      = get_settings();
$areas  = local_area_list($s);
$dishes = local_dishes($s);

$tabs = [
    'settings' => 'Settings',
    'areas'    => 'Suburb pages (' . count($areas) . ')',
    'dishes'   => 'Dish pages (' . count($dishes) . ')',
];
$tab = (string) ($_GET['tab'] ?? '');
$tab = isset($tabs[$tab]) ? $tab : 'settings';

if ($tab === 'settings') {
    $title  = 'Local SEO settings';
    $fields = [
        'local_enabled'    => ['Show the suburb & dish pages', 'checkbox', 'Turn off to hide every local page (and their links on the Contact page and in the sitemap).'],
        'seo_lat'          => ['Restaurant latitude', 'text', 'Used for distances and for Google. In Google Maps, right-click your restaurant pin — the first number is the latitude (e.g. -37.9196).'],
        'seo_lng'          => ['Restaurant longitude', 'text', 'The second number (e.g. 145.1222).'],
        'local_image'      => ['Photo for all suburb pages (optional)', 'image', 'Leave empty to show a different food photo on each suburb page.'],
        'local_hub_title'  => ['"Areas" page heading', 'text', 'Heading of the page that lists every suburb.'],
        'local_hub_intro'  => ['"Areas" page intro', 'textarea', ''],
        'dishes_hub_title' => ['"Dishes" page heading', 'text', ''],
        'dishes_hub_intro' => ['"Dishes" page intro', 'textarea', ''],
    ];
    if (is_post()) {
        foreach (['seo_lat' => [-90, 90], 'seo_lng' => [-180, 180]] as $k => [$lo, $hi]) {
            $v = trim((string) ($_POST['s'][$k] ?? ''));
            if ($v !== '' && (!is_numeric($v) || (float) $v < $lo || (float) $v > $hi)) {
                verify_csrf();
                flash('error', 'Please enter the latitude and longitude as plain numbers, e.g. -37.9196 and 145.1222.');
                redirect('local-seo.php');
            }
        }
    }
} elseif ($tab === 'areas') {
    $title  = 'Your own words for each suburb page';
    $fields = [];
    foreach ($areas as $a) {
        $fields['area_text_' . $a['slug']] = [$a['name'] . ' (' . $a['km'] . ' km)', 'textarea',
            'Optional. Added to the page — e.g. a regular from ' . $a['name'] . ', a local event you cater, the best parking tip. Leave a blank line between paragraphs.'];
    }
} else {
    $title  = 'Your own words and photo for each dish page';
    $fields = [];
    foreach ($dishes as $d) {
        $fields['dish_image_' . $d['slug']] = [$d['name'] . ' — photo', 'image', 'Optional. A real photo of this dish (about 1200 × 900 px).'];
        $fields['dish_text_' . $d['slug']]  = [$d['name'] . ' — extra text', 'textarea', 'Optional. What makes yours special — ingredients, spice level, the family recipe behind it.'];
    }
}

$current = settings_form_handle($pdo, $fields, 'local-seo.php?tab=' . $tab, $title . ' saved.');

admin_page_start('Local SEO', 'local-seo');
?>
  <nav class="tabs" aria-label="Local SEO sections">
    <?php foreach ($tabs as $key => $label): ?>
      <a href="local-seo.php?tab=<?= e($key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

<?php if ($tab === 'settings'): ?>
  <section class="panel">
    <h2 class="panel__title">How these pages work</h2>
    <p>The website has a page for every suburb within about 15 km — e.g. <em>"Sri Lankan Restaurant near Glen Waverley"</em> — with the distance,
       drive time, a map with directions, your dishes, opening hours and questions people search for. There's also a page for each dish.
       They're linked from the Contact page and listed in your sitemap, so Google finds them.</p>
    <p class="muted">Tip: the pages are written automatically, but adding a sentence or two of your own on the <strong>Suburb pages</strong> and
       <strong>Dish pages</strong> tabs makes each one unique — Google rewards that. The biggest local ranking factor is your
       <strong>Google Business Profile</strong> (reviews, photos, correct hours), so keep that up to date too.</p>
    <p>
      <a class="btn btn--ghost" href="<?= e(area_url()) ?>" target="_blank" rel="noopener">View suburb pages ↗</a>
      <a class="btn btn--ghost" href="<?= e(dish_url()) ?>" target="_blank" rel="noopener">View dish pages ↗</a>
    </p>
  </section>
<?php elseif ($tab === 'areas'): ?>
  <p class="muted">Pages are listed nearest first. Open any page:
    <?php foreach (array_slice($areas, 0, 6) as $a): ?><a href="<?= e(area_url($a['slug'])) ?>" target="_blank" rel="noopener"><?= e($a['name']) ?></a> · <?php endforeach; ?>
    <a href="<?= e(area_url()) ?>" target="_blank" rel="noopener">all suburbs ↗</a></p>
<?php else: ?>
  <p class="muted">Dishes come from <a href="categories.php">Home Page → Slider</a> and the named photos in the <a href="showcase.php">Showcase Grid</a>.
    Rename a dish there and its page follows. <a href="<?= e(dish_url()) ?>" target="_blank" rel="noopener">View all dishes ↗</a></p>
<?php endif; ?>

<?php
settings_form_render($fields, $current, $title);
admin_page_end();
