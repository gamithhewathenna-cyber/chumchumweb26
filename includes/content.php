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
        // Gallery page (the photos themselves are managed in Admin → Gallery Page)
        'gallery_title'            => 'Gallery',
        'gallery_intro'            => 'A Taste Of Life At Chum Chum\'s — Our Dishes, Our Kitchen And The Warm, Busy Dining Room In Clayton. Click Any Photo To See It Bigger.',
        'gallery_empty_text'       => 'Our gallery is coming soon — check back shortly for photos of our food and restaurant.',
        'gallery_meta_description' => "Photos of the food, kitchen and dining room at Chum Chum's Sri Lankan Cuisine Restaurant in Clayton, Melbourne.",
        // Menu page (the menu itself is pasted as HTML + CSS in Admin → Menu Page)
        'menu_title'            => 'Menu',
        'menu_intro'            => 'Explore Our Menu Of Modern Sri Lankan Favourites And Fusion Dishes — From Kottu And Rice & Curry To Biryani And Fresh Seafood, Made Fresh Every Day.',
        'menu_banner'           => 'menu-banner.jpg',
        'menu_html'             => '',
        'menu_css'              => '',
        'menu_empty_text'       => "Our full menu is coming soon. In the meantime, give us a call and we'll happily tell you what's cooking today.",
        'menu_meta_description' => "See the menu at Chum Chum's Sri Lankan Cuisine Restaurant in Clayton, Melbourne — kottu, rice & curry, biryani, seafood and modern fusion dishes.",
        // Email (SMTP) — Admin → Site Settings → Email (SMTP)
        'smtp_enabled'     => '0',
        'smtp_host'        => '',
        'smtp_port'        => '465',
        'smtp_encryption'  => 'ssl',
        'smtp_username'    => '',
        'smtp_password'    => '',
        'smtp_verify_cert' => '1',
        'smtp_from_email'  => '',
        'smtp_from_name'   => "Chum Chum's Website",
        'smtp_to_email'    => '',
        'smtp_cc'          => '',
        // Performance Meals page — Admin → Performance Meals
        'pm_title'            => 'Chums Performance Meals',
        'pm_tagline'          => 'Real Food. Smart Fuel. Better Performance.',
        'pm_banner'           => 'menu-banner.jpg',
        'pm_intro_badge'      => 'Healthy Eating Made Easy.',
        'pm_intro_title'      => "Delicious, high-protein meals,\nfully cooked and ready to heat and enjoy.",
        'pm_intro_text'       => "Whether you're a fitness enthusiast, a busy professional or simply someone who enjoys good food, Chums Performance Meals make healthy eating convenient and enjoyable.\n\nNo cooking. No hassle. Just real food.",
        'pm_intro_image'      => 'chef.jpg',
        'pm_life_title'       => 'Good Food for Every Lifestyle',
        'pm_life_text'        => "At Chums, we believe eating well should be simple.\n\nOur Performance Meals are prepared using quality ingredients, protein-rich options and balanced portions to deliver great taste and everyday convenience.\n\nPerfect for lunch, dinner or after a workout, our meals are designed for everyone — not just athletes.\n\nSimply heat, eat and enjoy!",
        'pm_life_image'       => 'reservation.jpg',
        'pm_meals_title'      => 'Explore Our Performance Meals',
        'pm_meals_text'       => 'High-protein chicken, fish and beef meals — fully cooked, ready to heat and enjoy.',
        'pm_meal1_title'      => 'Masala Grilled Fish',
        'pm_meal1_sides'      => 'Garlic Rice & Beans Curry',
        'pm_meal1_text'       => 'Tender masala-seasoned basa fish served with aromatic garlic rice and flavourful beans curry. A delicious, protein-rich meal packed with flavour.',
        'pm_meal1_image'      => 'seafood-soup.jpg',
        'pm_meal2_title'      => 'Curry Chicken',
        'pm_meal2_sides'      => 'Basmati Rice & Beans Curry',
        'pm_meal2_text'       => 'Tender chicken breast cooked with aromatic curry spices, served with fluffy basmati rice and green beans for a satisfying meal.',
        'pm_meal2_image'      => 'yellow-rice-curry.jpg',
        'pm_meal3_title'      => 'Chilli Chicken',
        'pm_meal3_sides'      => 'Basmati Rice & Vegetables',
        'pm_meal3_text'       => 'Delicious chilli chicken served with basmati rice and colourful vegetables. A flavourful choice for those who enjoy a little extra spice.',
        'pm_meal3_image'      => 'buddha-bowl.jpg',
        'pm_meal4_title'      => 'Chicken & Vegetable Biryani',
        'pm_meal4_sides'      => 'Aromatic Biryani Rice & Vegetables',
        'pm_meal4_text'       => 'Tender chicken breast combined with fragrant basmati rice, traditional biryani spices and mixed vegetables for a delicious, satisfying meal.',
        'pm_meal4_image'      => 'hero-plate.webp',
        'pm_meal5_title'      => 'Beef Bolognese Bowl',
        'pm_meal5_sides'      => 'Garlic Rice & Mixed Vegetables',
        'pm_meal5_text'       => 'Lean beef mince cooked in a rich tomato and herb sauce, served with garlic rice and mixed vegetables for a hearty, protein-rich meal.',
        'pm_meal5_image'      => 'toasted-sandwich.jpg',
        'pm_meal6_title'      => '',
        'pm_meal6_sides'      => '',
        'pm_meal6_text'       => '',
        'pm_meal6_image'      => '',
        'pm_why_title'        => 'Why Choose Chums Performance Meals?',
        'pm_why_items'        => "High-Protein Meals — Delicious protein-rich options for everyday nutrition.\nFully Cooked — Ready to heat and enjoy.\nQuality Ingredients — Carefully selected ingredients and flavourful recipes.\nConvenient Portions — Available in 500ml and 680ml options for selected meals.\nNo Added MSG — Prepared without added MSG.\nSaves Time — No shopping, preparation or cooking required.",
        'pm_everyone_badge'   => 'Made for Everyone',
        'pm_everyone_title'   => 'Not Just for the Gym. Made for Everyday Life.',
        'pm_everyone_text'    => "Whether you're heading to work, finishing a workout, studying or relaxing at home, Chums Performance Meals offer a simple way to enjoy delicious food without spending time in the kitchen.\n\nGreat for busy professionals, fitness enthusiasts, students, families and anyone looking for convenient meals.",
        'pm_everyone_tags'    => 'Busy professionals, Fitness enthusiasts, Students, Families',
        'pm_everyone_image'   => 'about-story.jpg',
        'pm_how_title'        => 'How It Works',
        'pm_step1_title'      => 'Choose Your Meal',
        'pm_step1_text'       => 'Explore our selection of chicken, fish and beef Performance Meals.',
        'pm_step2_title'      => 'Heat It Up',
        'pm_step2_text'       => 'Our meals are fully cooked. Simply reheat until piping hot.',
        'pm_step3_title'      => 'Enjoy Your Food',
        'pm_step3_text'       => 'Enjoy a delicious, satisfying meal without the hassle of cooking.',
        'pm_cta_title'        => 'Your Next Meal Is Already Cooked.',
        'pm_cta_text'         => "Good food doesn't have to be complicated.\n\nDiscover the convenience of fully cooked, high-protein meals made for your everyday lifestyle.",
        'pm_cta_button1'      => 'Explore Our Meals',
        'pm_cta_button2'      => 'Contact Chums',
        'pm_meta_description' => "Chums Performance Meals: delicious, high-protein meals, fully cooked and ready to heat — masala fish, curry & chilli chicken, biryani and beef bolognese. Clayton, Melbourne.",
        // Promotion popup — Admin → Popup Offers
        'popup_enabled'      => '1',
        'popup_frequency'    => 'session',
        'popup_delay'        => '2',
        'popup_pages'        => 'all',
        // Local SEO pages (suburbs + dishes) — Admin → Local SEO
        'local_enabled'      => '1',
        'seo_lat'            => '-37.9196',
        'seo_lng'            => '145.1222',
        'local_image'        => '',
        'local_hub_title'    => 'Sri Lankan Restaurant Near You',
        'local_hub_intro'    => "Chum Chum's cooks authentic Sri Lankan food in Clayton — kottu roti, biryani, rice & curry, devilled dishes and more — for locals across Melbourne's south-east, all within about 15 km.",
        'dishes_hub_title'   => 'Our Sri Lankan Dishes',
        'dishes_hub_intro'   => "The dishes people cross the south-east for — cooked fresh in our Clayton kitchen with real Sri Lankan spices.",
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
        ['label' => 'Menu',       'url' => 'menu.php'],
        ['label' => 'Performance Meals', 'url' => 'performance-meals.php'],
        ['label' => 'Gallery',    'url' => 'gallery.php'],
        ['label' => 'Contact Us', 'url' => 'contact.php'],
    ];
}

function get_footer_links(): array
{
    return [
        ['label' => 'Home',       'url' => 'index.php'],
        ['label' => 'Menu',       'url' => 'menu.php'],
        ['label' => 'Performance Meals', 'url' => 'performance-meals.php'],
        ['label' => 'Gallery',    'url' => 'gallery.php'],
        ['label' => 'Contact us', 'url' => 'contact.php'],
    ];
}

/**
 * Gallery photos. size = normal | wide (2 columns) | tall (2 rows) | big (2 × 2).
 * The defaults (also used to fill the table the first time) are photos already on the site.
 */
function default_gallery_images(): array
{
    $g = static fn(string $image, string $caption, string $size) => ['image' => $image, 'caption' => $caption, 'size' => $size];
    return [
        $g('yellow-rice-curry.jpg', 'Yellow Rice & Curry on banana leaf', 'tall'),
        $g('about-banner.jpg',      'Fresh pasta, made to order',         'wide'),
        $g('seafood-soup.jpg',      'Seafood soup',                       'normal'),
        $g('about-story.jpg',       'Our dining room in Clayton',         'big'),
        $g('buddha-bowl.jpg',       'Fresh Buddha bowl',                  'normal'),
        $g('about-fork.jpg',        'Pesto noodles',                      'tall'),
        $g('toasted-sandwich.jpg',  'Toasted sandwich',                   'normal'),
        $g('contact-banner.jpg',    'BBQ ribs platter',                   'wide'),
        $g('chef.jpg',              'Plating up in the kitchen',          'tall'),
        $g('hero-plate.webp',       'Seafood & chicken fried rice',       'normal'),
        $g('reservation.jpg',       'Dinner at Chum Chum\'s',             'wide'),
    ];
}

function get_gallery_images(): array
{
    return fetch_or_default(
        'SELECT image, caption, size FROM gallery_images WHERE is_active = 1 ORDER BY sort_order, id',
        default_gallery_images()
    );
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
