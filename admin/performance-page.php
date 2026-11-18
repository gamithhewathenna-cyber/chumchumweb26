<?php
/** Performance Meals page → text, meals and photos for each section. */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_settings_mediumtext($pdo);

$para = 'Leave an empty line between paragraphs.';
$mealFields = [];
for ($i = 1; $i <= 6; $i++) {
    $n = sprintf('%02d', $i);
    $mealFields["pm_meal{$i}_title"] = ["Meal {$n} — name", 'text', $i === 6 ? 'Optional extra meal. Leave the name empty to hide a meal.' : 'Leave empty to hide this meal.'];
    $mealFields["pm_meal{$i}_sides"] = ["Meal {$n} — served with", 'text', 'e.g. "Garlic Rice & Beans Curry".'];
    $mealFields["pm_meal{$i}_text"]  = ["Meal {$n} — description", 'textarea', ''];
    $mealFields["pm_meal{$i}_image"] = ["Meal {$n} — photo", 'image', 'Landscape photo, about 1200 × 900 px (4:3).'];
}

$sections = [
    'header' => ['Title & Banner', 'The big title at the top, the gold tagline beside it and the wide photo underneath.', [
        'pm_title'   => ['Page title', 'text', 'e.g. "Chums Performance Meals".'],
        'pm_tagline' => ['Tagline', 'text', 'Shown in gold beside the title and again at the bottom, e.g. "Real Food. Smart Fuel. Better Performance."'],
        'pm_banner'  => ['Banner photo', 'image', 'Wide photo under the title, about 2400 × 680 px. Darker photos look best.'],
    ]],
    'intro' => ['Intro', 'The "Healthy Eating Made Easy." section with the tall photo beside it.', [
        'pm_intro_badge' => ['Badge', 'text', 'The small gold pill above the heading.'],
        'pm_intro_title' => ['Heading', 'textarea', 'Press Enter where you want the line to break.'],
        'pm_intro_text'  => ['Text', 'textarea', $para . ' The last paragraph is shown in bold.'],
        'pm_intro_image' => ['Tall photo', 'image', 'Portrait photo, about 720 × 1000 px.'],
    ]],
    'life' => ['Lifestyle', 'The "Good Food for Every Lifestyle" section — text over a dark, softly blurred photo.', [
        'pm_life_title' => ['Heading', 'text', ''],
        'pm_life_text'  => ['Text', 'textarea', $para . ' The last paragraph is shown in gold.'],
        'pm_life_image' => ['Background photo', 'image', 'Wide photo, about 2400 × 1100 px. It is darkened automatically so the text stays readable.'],
    ]],
    'meals' => ['Meals', 'The numbered meal cards (01, 02 …). Up to 6 meals.', [
        'pm_meals_title' => ['Section heading', 'text', ''],
        'pm_meals_text'  => ['Section intro', 'textarea', 'Optional line under the heading.'],
    ] + $mealFields],
    'why' => ['Why Choose', 'The cards with the gold ticks.', [
        'pm_why_title' => ['Heading', 'text', ''],
        'pm_why_items' => ['Cards', 'textarea', 'One card per line: Title — text. Example: "Fully Cooked — Ready to heat and enjoy."'],
    ]],
    'everyone' => ['Made for Everyone', 'The section with the photo, text and the list of who the meals are for.', [
        'pm_everyone_badge' => ['Badge', 'text', ''],
        'pm_everyone_title' => ['Heading', 'text', ''],
        'pm_everyone_text'  => ['Text', 'textarea', $para],
        'pm_everyone_tags'  => ['Who it\'s for', 'text', 'Shown as small pills. Separate with commas.'],
        'pm_everyone_image' => ['Photo', 'image', 'About 1200 × 960 px.'],
    ]],
    'how' => ['How It Works', 'The three numbered steps.', [
        'pm_how_title'   => ['Heading', 'text', ''],
        'pm_step1_title' => ['Step 1 — title', 'text', ''],
        'pm_step1_text'  => ['Step 1 — text', 'textarea', ''],
        'pm_step2_title' => ['Step 2 — title', 'text', ''],
        'pm_step2_text'  => ['Step 2 — text', 'textarea', ''],
        'pm_step3_title' => ['Step 3 — title', 'text', ''],
        'pm_step3_text'  => ['Step 3 — text', 'textarea', ''],
    ]],
    'cta' => ['Call to Action', 'The gold-lit box at the bottom of the page.', [
        'pm_cta_title'   => ['Heading', 'text', ''],
        'pm_cta_text'    => ['Text', 'textarea', $para],
        'pm_cta_button1' => ['First button', 'text', 'Jumps down to the meals. Leave empty to hide.'],
        'pm_cta_button2' => ['Second button', 'text', 'Goes to the Contact page. Leave empty to hide.'],
    ]],
    'seo' => ['SEO', 'How the page appears in Google.', [
        'pm_meta_description' => ['Meta description', 'textarea', 'Shown under the page title in Google results. Aim for 120–160 characters.'],
    ]],
];

$section = (string) ($_GET['section'] ?? '');
$section = isset($sections[$section]) ? $section : 'header';
[$label, $intro, $fields] = $sections[$section];

$current = settings_form_handle($pdo, $fields, 'performance-page.php?section=' . $section, $label . ' saved.');

admin_page_start('Performance Meals', 'performance-page');
?>
  <nav class="tabs" aria-label="Performance Meals page sections">
    <?php foreach ($sections as $key => [$l]): ?>
      <a href="performance-page.php?section=<?= e($key) ?>"<?= $key === $section ? ' aria-current="page"' : '' ?>><?= e($l) ?></a>
    <?php endforeach; ?>
  </nav>
  <p class="muted"><?= e($intro) ?></p>
<?php
settings_form_render($fields, $current, $label,
    '<a class="btn btn--ghost" href="' . e(base_url('performance-meals.php')) . ($section === 'meals' ? '#meals' : '') . '" target="_blank" rel="noopener">View page ↗</a>');
admin_page_end();
