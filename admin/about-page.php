<?php
/** About Page → text and photos for each section (the cards have their own tab: about-features.php). */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

$para = 'Leave an empty line between paragraphs.';
$sections = [
    'header' => ['Title & Banner', 'The big title at the top of the page, the text beside it, and the wide photo underneath.', [
        'aboutpage_title'  => ['Page title', 'text', 'e.g. "About Us".'],
        'aboutpage_intro'  => ['Intro text', 'textarea', 'The short paragraph beside the title.'],
        'aboutpage_banner' => ['Banner photo', 'image', 'Wide photo under the title, about 2400 × 680 px. Darker photos look best.'],
    ]],
    'intro' => ['Intro', 'The "A Taste Of Sri Lanka" section with the tall photo beside it.', [
        'aboutpage_intro_badge' => ['Badge', 'text', 'The small gold pill above the heading.'],
        'aboutpage_intro_title' => ['Heading', 'textarea', 'Press Enter where you want the line to break.'],
        'aboutpage_intro_text'  => ['Text', 'textarea', $para],
        'aboutpage_intro_image' => ['Tall photo', 'image', 'Portrait photo, about 720 × 1240 px, on a dark background.'],
    ]],
    'story' => ['Our Story', 'The "Our Story." section — text over a dark, softly blurred background photo.', [
        'aboutpage_story_title' => ['Heading', 'text', ''],
        'aboutpage_story_text'  => ['Text', 'textarea', $para],
        'aboutpage_story_image' => ['Background photo', 'image', 'A wide photo of the restaurant works best, about 2400 × 1100 px. It is darkened and slightly blurred automatically so the text stays readable.'],
    ]],
    'why' => ['Why Choose Us', 'The heading above the cards. Edit the cards themselves in the next tab.', [
        'aboutpage_why_badge' => ['Badge', 'text', 'The small gold pill above the heading.'],
        'aboutpage_why_title' => ['Heading', 'text', ''],
        'aboutpage_why_text'  => ['Text', 'textarea', ''],
    ]],
    'more' => ['Reservation & SEO', 'Extra page options.', [
        'aboutpage_show_reservation' => ['Show the Reservation section at the bottom', 'checkbox', 'The same section as on the home page — edit its text and photo under Home Page → Reservation.'],
        'aboutpage_meta_description' => ['Meta description', 'textarea', 'Shown under the page title in Google results. Aim for 120–160 characters.'],
    ]],
];

$section = (string) ($_GET['section'] ?? '');
$section = isset($sections[$section]) ? $section : 'header';
[$label, $intro, $fields] = $sections[$section];

$current = settings_form_handle($pdo, $fields, 'about-page.php?section=' . $section, $label . ' saved.');

admin_page_start('About Page', 'about-page');
about_tabs($section);
?>
  <p class="muted"><?= e($intro) ?></p>
<?php
settings_form_render($fields, $current, $label,
    '<a class="btn btn--ghost" href="' . e(base_url('about.php')) . '" target="_blank" rel="noopener">View about page ↗</a>');
admin_page_end();
