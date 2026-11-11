<?php
/**
 * Content layer. Every function reads from MySQL and falls back to the
 * defaults below if the database is unavailable or a table is empty.
 */

function default_settings(): array
{
    return [
        'site_name'         => "Chum Chum's",
        'meta_title'        => "Chum Chum's | Modern Sri Lankan Cuisine Restaurant – Clayton, Melbourne",
        'meta_description'  => "Chum Chum's Sri Lankan Cuisine Restaurant in Clayton, Melbourne. Modern Sri Lankan flavours, fusion dishes, rice & curry, kottu, biryani and more — open six days a week till midnight.",
        'phone'             => '047 593 5804',
        'email'             => 'chumchumscafe@gmail.com',
        'address_line1'     => '47 Morton st clayton VIC 3168,',
        'address_line2'     => 'Clayton, VIC, Australia, Victoria',
        'facebook_url'      => '#',
        'instagram_url'     => '#',
        'order_url'         => '#',
        'customer_url'      => '#',
        'hero_badge'        => 'Ingredients That Matter',
        'hero_title'        => 'Seafood & Chicken Fried Rice',
        'hero_subtitle'     => 'With Fresh Vegetables And Egg',
        'hero_image'        => 'hero-plate.webp',
        'about_badge'       => 'Sri Lankan Cuisine Restaurant',
        'about_title'       => 'Founded With The Sole Intention Of Presenting Modern Sri Lankan Flavors To The Live Hood Melbourne',
        'about_text'        => '"Chum Chum\'s" Sri Lankan Cuisine Restaurant Is Founded With The Sole Intention Of Presenting Modern Sri Lankan Flavors To The Live Hood Of Melbourne. With A Modern Touch To The Traditional Recipes, Various Fusion Dishes Will Be Presented Six Days A Week Till Midnight.',
        'about_image'       => 'chef.jpg',
        'interior_image'    => 'interior.jpg',
        'hours_title'       => 'Opening Hours',
        'reservation_title' => 'Reservation',
        'reservation_text'  => 'Book Your Table At Chum Chums And Enjoy A Fantastic Dining Experience With Delicious Food And Great Ambiance!',
        'reservation_label' => 'Telephone Reservation',
        'reservation_image' => 'reservation.jpg',
        'footer_text_1'     => 'Lorem ipsum dolor sit amet consectetur. Condimentum convallis nulla nec ultrices convallis non turpis. Euismod lacus nunc in id justo ac sed. Sit habitant donec massa semper nibh maecenas.',
        'footer_text_2'     => 'Sit senectus ullamcorper massa turpis ac orci egestas. Augue tellus sed urna vehicula semper.',
        'copyright_name'    => 'Chumchums',
        'maintenance_mode'    => '0',
        'maintenance_title'   => "We'll Be Back Soon",
        'maintenance_message' => "Our website is getting a fresh new look. We're still serving delicious Sri Lankan food — give us a call or drop us an email in the meantime.",
        // About page
        'aboutpage_title'            => 'About Us',
        'aboutpage_intro'            => 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.',
        'aboutpage_banner'           => 'about-banner.jpg',
        'aboutpage_intro_badge'      => 'About Chumchums',
        'aboutpage_intro_title'      => "A Taste Of Sri Lanka,\nReimagined For Melbourne",
        'aboutpage_intro_text'       => "At Chum Chums, We Bring The Vibrant Flavours, Aromas, And Warmth Of Sri Lankan Cuisine To The Heart Of Clayton, Melbourne.\n\nBorn From A Passion For Authentic Sri Lankan Food And A Love For Bringing People Together, Chum Chums Was Created With One Simple Idea — To Give Melbourne A Fresh And Modern Way To Experience The Flavours Of Sri Lanka.\n\nFrom Traditional Favourites To Creative Fusion Dishes, Our Menu Celebrates The Rich Culinary Heritage Of Sri Lanka While Adding Our Own Modern Touch. Every Dish Is Prepared With Carefully Selected Ingredients, Bold Spices, And Plenty Of Passion.",
        'aboutpage_intro_image'      => 'about-fork.jpg',
        'aboutpage_story_title'      => 'Our Story.',
        'aboutpage_story_text'       => "Lorem Ipsum Dolor Sit Amet Consectetur. Magna Ullamcorper Bibendum Sodales Eu Egestas Quis Suspendisse Non. Nibh Tincidunt Sit Metus Nulla Interdum Cursus. Lorem Eget Sed Amet Dignissim In. Neque Facilisi Metus Diam Eu Eleifend Libero. Viverra Adipiscing Purus Leo Congue Etiam Eget Nisl. Nec Tristique Amet Fames Morbi Duis Diam Maecenas A Consequat. Risus Eros Malesuada Faucibus Velit Ante Varius.\n\nNunc Et Neque Convallis Ac Lacus. Velit Aenean A Venenatis Mattis Lorem Urna Viverra Convallis Sed. Quisque Parturient Ac Pellentesque Dictum Imperdiet Mauris Proin Lectus. Placerat At Sit Sit Praesent Ultricies Ut Neque Molestie. Viverra Tellus Neque Diam A Semper. Penatibus Eget Pellentesque Arcu Nisi Parturient Amet. Ut Euismod Ut Condimentum Risus Luctus.\n\nSit Varius Sit Consectetur At Nisi Risus Dolor Ut Fermentum. Non Sed Orci Arcu Pretium Ultrices Ullamcorper. Dictumst Euismod Id Suspendisse Pellentesque Nunc Ultrices. Habitant Aenean Faucibus Egestas Tortor. Gravida Bibendum Leo Viverra Feugiat Tortor Adipiscing. Turpis Fames Quam Feugiat Et Ut. Eget Imperdiet Mi Cum Ullamcorper. Volutpat Maecenas Cras Fermentum Molestie Sit Cras. Venenatis Congue Aliquam Nibh Id Et Tellus.",
        'aboutpage_story_image'      => 'about-story.jpg',
        'aboutpage_why_badge'        => 'Modern Flavours. Sri Lankan Roots.',
        'aboutpage_why_title'        => 'Why Choose Chumchums?',
        'aboutpage_why_text'         => 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat.',
        'aboutpage_show_reservation' => '1',
        'aboutpage_meta_description' => "Chum Chum's brings the vibrant flavours of Sri Lankan cuisine to Clayton, Melbourne — traditional favourites and modern fusion dishes, made fresh with bold spices.",
        // Contact page
        'contact_title'            => 'Contact Us',
        'contact_intro'            => 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.',
        'contact_banner'           => 'contact-banner.jpg',
        'contact_heading'          => 'Our Address',
        'contact_text'             => "Got a craving, a question, or just want to share your foodie love? We're all ears!",
        'contact_map_query'        => '47 Morton St, Clayton VIC 3168, Australia',
        'contact_success'          => "Thank you! Your message has been sent — we'll get back to you as soon as we can.",
        'contact_notify_enabled'   => '1',
        'contact_notify_email'     => '',
        'contact_meta_description' => "Get in touch with Chum Chum's Sri Lankan Cuisine Restaurant in Clayton, Melbourne — call, email, find us on the map or send us a message.",
    ];
}

function get_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    $settings = default_settings();
    if ($pdo = db()) {
        try {
            foreach ($pdo->query('SELECT setting_key, setting_value FROM settings') as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $ex) {
            error_log('[ChumChums] settings: ' . $ex->getMessage());
        }
    }
    return $settings;
}

/**
 * Run a SELECT and return its rows. The fallback is used only when the
 * database is unreachable or the query fails — an empty table (e.g. every
 * testimonial hidden in the admin) really does mean "show nothing".
 */
function fetch_or_default(string $sql, array $fallback): array
{
    if ($pdo = db()) {
        try {
            return $pdo->query($sql)->fetchAll();
        } catch (Throwable $ex) {
            error_log('[ChumChums] query failed: ' . $ex->getMessage());
        }
    }
    return $fallback;
}

function get_navigation(): array
{
    return [
        ['label' => 'Home',       'url' => 'index.php'],
        ['label' => 'About Us',   'url' => 'about.php'],
        ['label' => 'Gallery',    'url' => '#gallery'],
        ['label' => 'Contact Us', 'url' => 'contact.php'],
    ];
}

function get_footer_links(): array
{
    return [
        ['label' => 'Home',       'url' => 'index.php'],
        ['label' => 'Menu',       'url' => '#menu'],
        ['label' => 'Gallery',    'url' => '#gallery'],
        ['label' => 'Contact us', 'url' => 'contact.php'],
    ];
}

/** "Why Choose" cards on the About page. */
function get_about_features(): array
{
    return fetch_or_default(
        'SELECT title, text FROM about_features WHERE is_active = 1 ORDER BY sort_order, id',
        [
            ['title' => 'Authentic Sri Lankan Inspiration', 'text' => 'Experience The Bold Spices, Textures, And Flavours Inspired By Sri Lanka.'],
            ['title' => 'Modern Fusion Cuisine',            'text' => 'Traditional Favourites Meet Creative Ideas To Create Something Uniquely Chum Chums.'],
            ['title' => 'Freshly Prepared',                 'text' => 'We Focus On Quality Ingredients And Freshly Prepared Dishes For Every Customer.'],
            ['title' => 'Made For Sharing',                 'text' => "Our Food Is Meant To Bring People - Whether It's A Family Feast Or A Casual Meal With Friends."],
            ['title' => 'Dine-In & Takeaway',               'text' => 'Enjoy Your Favourite Dishes At Our Clayton Restaurant Or Take Them With You.'],
        ]
    );
}

/** Turn text with blank lines between paragraphs into escaped <p> elements. */
function text_paragraphs(string $text, string $attrs = ''): string
{
    $html = '';
    foreach (preg_split('~\R\s*\R~', trim($text)) as $para) {
        if (trim($para) !== '') {
            $html .= '<p' . $attrs . '>' . nl2br(e(trim($para)), false) . '</p>';
        }
    }
    return $html;
}

/**
 * Link for nav/footer items. "#section" links point at homepage sections, so on any
 * other page they become "/#section" (back to the homepage, then jump).
 */
function nav_url(string $url): string
{
    $page = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    if ($url !== '' && $url[0] === '#' && $page !== 'index.php') {
        return base_url('') . $url;
    }
    return link_url($url);
}

function get_menu_categories(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $c = static function (string $name, string $thumb, string $side, string $title, string $subtitle, string $image = ''): array {
        return ['name' => $name, 'thumbnail' => $thumb, 'link_url' => '#menu', 'side' => $side,
                'hero_badge' => '', 'hero_title' => $title, 'hero_subtitle' => $subtitle, 'hero_image' => $image];
    };
    $defaults = [
        $c('Submarine',    'thumb-submarine.png',   'left',  'Chicken Submarine',            'Toasted Bread, Fresh Salad And Signature Sauce'),
        $c('Fried Rice',   'thumb-fried-rice.png',  'left',  'Seafood & Chicken Fried Rice', 'With Fresh Vegetables And Egg', 'hero-plate.webp'),
        $c('Kottu',        'thumb-kottu.png',       'left',  'Chicken Kottu Roti',           'Chopped Roti, Vegetables And Spicy Curry Gravy'),
        $c('Cuttle Fish',  'thumb-cuttle-fish.png', 'right', 'Devilled Cuttle Fish',         'Crispy, Spicy And Tossed With Peppers And Onion'),
        $c('Rice & Curry', 'thumb-rice-curry.png',  'right', 'Sri Lankan Rice & Curry',      'Rice With Curries, Sambol And Papadam'),
        $c('Biryani',      'thumb-biryani.png',     'right', 'Chicken Biryani',              'Fragrant Spiced Rice With Tender Chicken'),
    ];
    $where = 'FROM menu_categories WHERE is_active = 1 ORDER BY side, sort_order, id';
    if ($pdo = db()) {
        try {
            return $rows = $pdo->query("SELECT name, thumbnail, link_url, side, hero_badge, hero_title, hero_subtitle, hero_image $where")->fetchAll();
        } catch (Throwable $ex) {
            // The hero-slide columns haven't been added yet — fall back to the original columns
            try {
                return $rows = $pdo->query("SELECT name, thumbnail, link_url, side $where")->fetchAll();
            } catch (Throwable $ex2) {
                error_log('[ChumChums] menu_categories: ' . $ex2->getMessage());
            }
        }
    }
    return $rows = $defaults;
}

/**
 * The original hero photo (hero-bowl.webp) shows only the top half of the bowl, which can't spin.
 * hero-plate.webp is the same bowl rebuilt as a full round plate — use it wherever the old one is set.
 */
function plate_image(string $file): string
{
    return $file === 'hero-bowl.webp' ? 'hero-plate.webp' : $file;
}

/**
 * Hero slides — one per active menu category, in pill order (left side, then right).
 * Empty slide fields fall back to the Home Page → Hero defaults.
 * Returns [slides, index of the slide to show first].
 */
function get_hero_slides(array $s, array $orderedCats): array
{
    $slides = [];
    foreach ($orderedCats as $cat) {
        $image    = trim((string) ($cat['hero_image'] ?? ''));
        $slides[] = [
            'name'      => $cat['name'],
            'badge'     => trim((string) ($cat['hero_badge'] ?? '')) ?: $s['hero_badge'],
            'title'     => trim((string) ($cat['hero_title'] ?? '')) ?: $cat['name'],
            'subtitle'  => trim((string) ($cat['hero_subtitle'] ?? '')),
            'image'     => plate_image($image !== '' ? $image : $s['hero_image']),
            'has_image' => $image !== '',
        ];
    }
    if (!$slides) {
        $slides[] = ['name' => '', 'badge' => $s['hero_badge'], 'title' => $s['hero_title'],
                     'subtitle' => $s['hero_subtitle'], 'image' => plate_image($s['hero_image']), 'has_image' => true];
    }
    // Open on the first dish that has its own photo
    $start = 0;
    foreach ($slides as $i => $slide) {
        if ($slide['has_image']) {
            $start = $i;
            break;
        }
    }
    return [$slides, $start];
}

function get_opening_hours(): array
{
    return fetch_or_default(
        "SELECT label, hours FROM opening_hours ORDER BY sort_order, id",
        [
            ['label' => 'Monday - Thursday', 'hours' => '8AM - 12AM'],
            ['label' => 'Friday - Saturday', 'hours' => '8AM - 2AM'],
            ['label' => 'Sunday',            'hours' => '9AM - 12AM'],
        ]
    );
}

/**
 * Showcase grid. Seven fixed slots:
 * 1 = feature (image + text, spans two rows), 2/4/6 = text cards, 3/5/7 = images.
 */
function get_showcase(): array
{
    $lorem = 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.';
    $defaults = [
        1 => ['slot' => 1, 'title' => 'Yellow Rice And Curry', 'description' => 'A Delicious Platter Of Fragrant Yellow Rice, Rich Curries, Crispy Sides, Fresh Salad, And Tender Glazed Meat, Beautifully Served On A Traditional Banana Leaf.', 'image' => 'yellow-rice-curry.jpg', 'link_url' => '#menu'],
        2 => ['slot' => 2, 'title' => 'Lorem Ipsum Dolor Sit Amet', 'description' => $lorem, 'image' => '', 'link_url' => '#menu'],
        3 => ['slot' => 3, 'title' => 'Fresh Buddha Bowl', 'description' => '', 'image' => 'buddha-bowl.jpg', 'link_url' => ''],
        4 => ['slot' => 4, 'title' => 'Lorem Ipsum Dolor Sit Amet', 'description' => $lorem, 'image' => '', 'link_url' => '#menu'],
        5 => ['slot' => 5, 'title' => 'Toasted Sandwich', 'description' => '', 'image' => 'toasted-sandwich.jpg', 'link_url' => ''],
        6 => ['slot' => 6, 'title' => 'Lorem Ipsum Dolor Sit Amet', 'description' => $lorem, 'image' => '', 'link_url' => '#menu'],
        7 => ['slot' => 7, 'title' => 'Seafood Soup', 'description' => '', 'image' => 'seafood-soup.jpg', 'link_url' => ''],
    ];
    $rows = fetch_or_default("SELECT slot, title, description, image, link_url FROM showcase_items WHERE slot BETWEEN 1 AND 7", []);
    foreach ($rows as $row) {
        $defaults[(int) $row['slot']] = $row;
    }
    ksort($defaults);
    return $defaults;
}

function get_testimonials(): array
{
    $lorem = 'Lorem Ipsum Dolor Sit Amet Consectetur. Amet Dolor Quis Id Maecenas Ipsum Nullam Posuere Vitae. Volutpat Tristique Ut Hendrerit Integer Sollicitudin Nibh Pharetra Sagittis. Tellus Vestibulum Ac Nisl Quis Nec Tincidunt. Nam Ultricies Feugiat Enim Cras. Sed Odio Mattis Venenatis Luctus Cum. At Facilisi Leo Etiam Tellus. Tincidunt Id.';
    return fetch_or_default(
        "SELECT title, content FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id",
        [
            ['title' => 'Lorem Ipsum Dolor Sit Amet', 'content' => $lorem],
            ['title' => 'Lorem Ipsum Dolor Sit Amet', 'content' => $lorem],
            ['title' => 'Lorem Ipsum Dolor Sit Amet', 'content' => $lorem],
        ]
    );
}
