<?php
/**
 * Local SEO: suburb ("Sri Lankan restaurant near Glen Waverley") and dish
 * ("Chicken Kottu Roti in Clayton") landing pages.
 *
 *   /sri-lankan-restaurant              all areas we serve      (sri-lankan-restaurant.php)
 *   /sri-lankan-restaurant/glen-waverley one suburb               (sri-lankan-restaurant.php?item=glen-waverley)
 *   /dishes                             all dishes               (dishes.php)
 *   /dishes/chicken-kottu-roti          one dish                 (dishes.php?item=chicken-kottu-roti)
 *
 * Every page is built from real facts — the distance, direction and drive time
 * from that suburb, the nearest neighbouring suburbs, the dishes on the menu and
 * the opening hours — so no two pages read the same. Owners can add their own
 * paragraph (and photo) to any page in Admin → Local SEO.
 */

/**
 * Suburbs within roughly 15 km of the restaurant: slug => [name, postcode, lat, lng].
 * Coordinates are each suburb's centre (approximate) — used for distances only.
 */
function local_areas(): array
{
    return [
        'clayton'          => ['Clayton',          '3168', -37.9247, 145.1205],
        'notting-hill'     => ['Notting Hill',     '3168', -37.9033, 145.1427],
        'oakleigh-east'    => ['Oakleigh East',    '3166', -37.9000, 145.1150],
        'huntingdale'      => ['Huntingdale',      '3166', -37.9100, 145.1030],
        'oakleigh'         => ['Oakleigh',         '3166', -37.8990, 145.0890],
        'mount-waverley'   => ['Mount Waverley',   '3149', -37.8770, 145.1290],
        'mulgrave'         => ['Mulgrave',         '3170', -37.9280, 145.1660],
        'springvale'       => ['Springvale',       '3171', -37.9500, 145.1530],
        'clarinda'         => ['Clarinda',         '3169', -37.9370, 145.1020],
        'glen-waverley'    => ['Glen Waverley',    '3150', -37.8780, 145.1650],
        'chadstone'        => ['Chadstone',        '3148', -37.8870, 145.0950],
        'hughesdale'       => ['Hughesdale',       '3166', -37.8950, 145.0770],
        'noble-park'       => ['Noble Park',       '3174', -37.9670, 145.1760],
        'wheelers-hill'    => ['Wheelers Hill',    '3150', -37.9050, 145.1890],
        'bentleigh-east'   => ['Bentleigh East',   '3165', -37.9200, 145.0530],
        'murrumbeena'      => ['Murrumbeena',      '3163', -37.8900, 145.0700],
        'carnegie'         => ['Carnegie',         '3163', -37.8890, 145.0570],
        'dandenong-north'  => ['Dandenong North',  '3175', -37.9550, 145.2130],
        'keysborough'      => ['Keysborough',      '3173', -37.9910, 145.1740],
        'ashwood'          => ['Ashwood',          '3147', -37.8660, 145.1030],
        'burwood'          => ['Burwood',          '3125', -37.8500, 145.1150],
        'vermont-south'    => ['Vermont South',    '3133', -37.8560, 145.1820],
        'moorabbin'        => ['Moorabbin',        '3189', -37.9350, 145.0370],
        'bentleigh'        => ['Bentleigh',        '3204', -37.9180, 145.0350],
        'ormond'           => ['Ormond',           '3204', -37.9030, 145.0400],
        'malvern-east'     => ['Malvern East',     '3145', -37.8740, 145.0430],
        'box-hill-south'   => ['Box Hill South',   '3128', -37.8350, 145.1210],
        'blackburn-south'  => ['Blackburn South',  '3130', -37.8380, 145.1550],
        'rowville'         => ['Rowville',         '3178', -37.9280, 145.2340],
        'dandenong'        => ['Dandenong',        '3175', -37.9870, 145.2150],
        'brighton-east'    => ['Brighton East',    '3187', -37.9180, 145.0150],
        'hampton-east'     => ['Hampton East',     '3188', -37.9370, 145.0300],
        'cheltenham'       => ['Cheltenham',       '3192', -37.9550, 145.0550],
        'mentone'          => ['Mentone',          '3194', -37.9800, 145.0650],
        'doncaster-east'   => ['Doncaster East',   '3109', -37.7860, 145.1610],
        'ringwood-east'    => ['Ringwood East',    '3135', -37.8150, 145.2500],
        'endeavour-hills'  => ['Endeavour Hills',  '3802', -37.9770, 145.2590],
    ];
}

function local_enabled(array $s): bool
{
    return ($s['local_enabled'] ?? '1') === '1';
}

/** URL of a hub ('sri-lankan-restaurant' or 'dishes') or one of its pages. */
function local_url(string $hub, string $slug = ''): string
{
    if (clean_urls_enabled()) {
        return base_url($hub . ($slug !== '' ? '/' . $slug : ''));
    }
    return base_url($hub . '.php' . ($slug !== '' ? '?item=' . rawurlencode($slug) : ''));
}

function area_url(string $slug = ''): string { return local_url('sri-lankan-restaurant', $slug); }
function dish_url(string $slug = ''): string { return local_url('dishes', $slug); }

/** "Seafood & Chicken Fried Rice" → "seafood-chicken-fried-rice" */
function local_slug(string $text): string
{
    $text = strtolower(str_replace('&', ' and ', $text));
    $text = preg_replace('~\band\b~', ' ', $text);
    return trim((string) preg_replace('~[^a-z0-9]+~', '-', $text), '-');
}

/** Restaurant location [lat, lng] (Admin → Local SEO; defaults to 47 Morton St, Clayton). */
function restaurant_geo(array $s): array
{
    $lat = (float) ($s['seo_lat'] ?? 0);
    $lng = (float) ($s['seo_lng'] ?? 0);
    return ($lat && $lng) ? [$lat, $lng] : [-37.9196, 145.1222];
}

/** Straight-line distance in km. */
function geo_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/** Compass word for the direction from point 1 to point 2, e.g. "south-east". */
function geo_direction(float $lat1, float $lng1, float $lat2, float $lng2): string
{
    $y = sin(deg2rad($lng2 - $lng1)) * cos(deg2rad($lat2));
    $x = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2 - $lng1));
    $deg = fmod(rad2deg(atan2($y, $x)) + 360, 360);
    $words = ['north', 'north-east', 'east', 'south-east', 'south', 'south-west', 'west', 'north-west'];
    return $words[(int) round($deg / 45) % 8];
}

/**
 * Every area with its facts, nearest first:
 * slug, name, postcode, lat, lng, km (by road, rounded), minutes (driving, rounded), direction (restaurant from suburb).
 */
function local_area_list(array $s): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    [$rLat, $rLng] = restaurant_geo($s);
    $out = [];
    foreach (local_areas() as $slug => [$name, $pc, $lat, $lng]) {
        $straight = geo_km($lat, $lng, $rLat, $rLng);
        $road     = max(1.0, $straight * 1.3);                         // roads are never straight
        $minutes  = (int) max(3, 5 * round(($road / 32 * 60 + 2) / 5)); // ~32 km/h suburban average, to the nearest 5
        if ($road < 2.5) {
            $minutes = max(3, (int) round($road / 30 * 60 + 2));
        }
        $out[$slug] = [
            'slug' => $slug, 'name' => $name, 'postcode' => $pc, 'lat' => $lat, 'lng' => $lng,
            'km' => $road < 10 ? round($road, 1) : (int) round($road), 'straight' => $straight, 'minutes' => $minutes,
            'direction' => geo_direction($lat, $lng, $rLat, $rLng),
        ];
    }
    uasort($out, fn($a, $b) => $a['straight'] <=> $b['straight']);
    return $cache = $out;
}

/** The $n areas nearest to the given one (not itself). */
function local_nearest(array $s, string $slug, int $n): array
{
    $all = local_area_list($s);
    if (!isset($all[$slug])) {
        return array_slice($all, 0, $n, true);
    }
    $me = $all[$slug];
    $others = array_filter($all, fn($a) => $a['slug'] !== $slug);
    uasort($others, fn($a, $b) => geo_km($me['lat'], $me['lng'], $a['lat'], $a['lng']) <=> geo_km($me['lat'], $me['lng'], $b['lat'], $b['lng']));
    return array_slice($others, 0, $n, true);
}

/** Pick $n items from a list in a fixed order that depends on $seed (same page → same choice every time). */
function local_pick(array $items, string $seed, int $n): array
{
    $items = array_values($items);
    usort($items, fn($a, $b) => crc32($seed . json_encode($a)) <=> crc32($seed . json_encode($b)));
    return array_slice($items, 0, $n);
}

/** Choose one template variant for a page. */
function local_variant(string $seed, int $count): int
{
    return (int) (crc32('v' . $seed) % $count);
}

/** Food photos that can illustrate a suburb page: file => what it shows. */
function local_photos(): array
{
    return [
        'yellow-rice-curry.jpg' => 'Yellow rice & curry on banana leaf',
        'hero-plate.webp'       => 'Seafood & chicken fried rice',
        'seafood-soup.jpg'      => 'Seafood soup',
        'reservation.jpg'       => "Dinner at Chum Chum's",
        'buddha-bowl.jpg'       => 'Fresh Buddha bowl',
        'chef.jpg'              => 'Plating up in the kitchen',
        'toasted-sandwich.jpg'  => 'Toasted sandwich',
    ];
}

/** [file, caption] of the photo for a suburb page. */
function local_area_photo(array $s, string $slug): array
{
    $own = trim((string) ($s['area_image_' . $slug] ?? ''));
    if ($own !== '') {
        return [$own, "Chum Chum's Sri Lankan food"];
    }
    $site = trim((string) ($s['local_image'] ?? ''));
    if ($site !== '') {
        return [$site, "Sri Lankan food at Chum Chum's"];
    }
    $photos = array_filter(local_photos(), fn($f) => is_file(APP_ROOT . '/assets/img/' . $f), ARRAY_FILTER_USE_KEY);
    $keys   = array_keys($photos);
    $file   = $keys[local_variant($slug . 'photo', count($keys))];
    return [$file, $photos[$file]];
}

/** Width/height of an image in assets/img (for layout stability), or [1200, 800]. */
function local_img_size(string $file): array
{
    $path = APP_ROOT . '/assets/img/' . ltrim($file, '/');
    $info = is_file($path) ? @getimagesize($path) : false;
    return $info ? [(int) $info[0], (int) $info[1]] : [1200, 800];
}

/* ------------------------------------------------------------------ */
/*  Dishes                                                             */
/* ------------------------------------------------------------------ */

/** A short, honest description of a well-known dish (matched by name), or ''. */
function dish_story(string $name): string
{
    $n = strtolower($name);
    $lib = [
        'kottu'      => "Kottu roti is Sri Lanka's favourite street food: soft flatbread chopped on a hot griddle with vegetables, egg, spices and curry gravy until every bite is full of flavour. You usually hear kottu being made before you see it — the rhythm of the blades on the griddle is part of the meal.",
        'biryani'    => "Biryani is a celebration dish — long-grain rice cooked with whole spices, fried onions and tender, marinated meat so the flavours soak right through. Ours is served the Sri Lankan way, fragrant and generous, with sides to cool the heat.",
        'submarine'  => "The submarine is a Sri Lankan take on the loaded sub: a toasted roll packed with spiced filling, fresh salad and our signature sauce. It's quick, filling and a favourite for a casual lunch.",
        'fried rice' => "Sri Lankan fried rice is cooked hot and fast in the wok with vegetables, egg and plenty of flavour. Ours comes loaded with seafood and chicken — a crowd-pleaser that suits every table.",
        'cuttle'     => "Devilled cuttlefish is a Sri Lankan classic: tender cuttlefish fried until crisp, then tossed with chilli, capsicum and onion in a sweet, spicy and tangy 'devilled' sauce. Perfect to share, perfect with a cold drink.",
        'yellow rice'=> "Yellow rice (kaha bath) is cooked with turmeric, spices and coconut, then served with a spread of curries, crispy sides and salad on a traditional banana leaf — a proper Sri Lankan feast on one plate.",
        'rice'       => "Rice and curry is how Sri Lanka eats every day: steamed rice with a selection of curries, coconut sambol and crunchy papadam. Every spoonful is a little different, and that's the point.",
        'soup'       => "A warming bowl of seafood soup, full of flavour from the sea and gently spiced — light enough for a starter, hearty enough for a cold Melbourne night.",
        'bowl'       => "A fresh, colourful bowl of grains, vegetables and greens with a punchy dressing — a lighter choice that's still packed with flavour.",
        'sandwich'   => "A hot toasted sandwich, golden and crisp outside with a generous filling — simple, satisfying and quick.",
    ];
    foreach ($lib as $key => $text) {
        if (strpos($n, $key) !== false) {
            return $text;
        }
    }
    return '';
}

/**
 * Dishes for the dish pages, from the menu categories (Home Page → Menu slider)
 * and the named Showcase photos: slug => [slug, name, subtitle, image, story].
 */
function local_dishes(array $s): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $out = [];
    $add = static function (string $name, string $subtitle, string $image) use (&$out, $s): void {
        $name = trim($name);
        $slug = local_slug($name);
        if ($name === '' || $slug === '' || isset($out[$slug]) || stripos($name, 'lorem') !== false) {
            return;
        }
        $own = trim((string) ($s['dish_image_' . $slug] ?? ''));
        $out[$slug] = [
            'slug' => $slug, 'name' => $name, 'subtitle' => trim($subtitle),
            'image' => $own !== '' ? $own : $image, 'story' => dish_story($name),
            // Sri Lankan specialities lead the suburb pages (not the sandwich or the Buddha bowl)
            'sri' => (bool) preg_match('~kottu|biryani|submarine|fried rice|cuttle|devil|rice\s*(&|and)\s*curry|hopper|lamprais|sri lanka|sambol|roti~i', $name),
        ];
    };
    foreach (get_menu_categories() as $c) {
        $name = trim((string) ($c['hero_title'] ?? '')) ?: (string) $c['name'];
        $img  = trim((string) ($c['hero_image'] ?? ''));
        $add($name, (string) ($c['hero_subtitle'] ?? ''), $img !== '' ? plate_image($img) : '');
    }
    foreach (get_showcase() as $item) {
        $add((string) $item['title'], (string) $item['description'], (string) $item['image']);
    }
    // Sri Lankan specialities first (stable order otherwise)
    $i = 0;
    $rank = array_map(function ($d) use (&$i) { return [$d["sri"] ? 0 : 1, $i++]; }, $out);
    uksort($out, fn($a, $b) => $rank[$a] <=> $rank[$b]);
    return $cache = $out;
}

/** Photo [file, alt-ish caption] for a dish page: its own photo, or our dining room. */
function local_dish_photo(array $dish): array
{
    if ($dish['image'] !== '' && (preg_match('~^https?://~', $dish['image']) || is_file(APP_ROOT . '/assets/img/' . $dish['image']))) {
        return [$dish['image'], $dish['name']];
    }
    return ['reservation.jpg', "Dinner at Chum Chum's in Clayton"];
}

/** Opening hours as one sentence: "Monday - Thursday 8AM - 12AM; Friday - Saturday …". */
function local_hours_text(): string
{
    return implode('; ', array_map(fn($h) => $h['label'] . ' ' . $h['hours'], get_opening_hours()));
}

/** Natural list: "a, b and c". */
function local_join(array $items): string
{
    $items = array_values($items);
    if (count($items) < 2) {
        return (string) ($items[0] ?? '');
    }
    $last = array_pop($items);
    return implode(', ', $items) . ' and ' . $last;
}

/** Google Maps links/embeds. */
function local_map_embed(string $query, int $zoom = 14): string
{
    return 'https://www.google.com/maps?q=' . rawurlencode($query) . '&z=' . $zoom . '&output=embed';
}
function local_directions_embed(string $from, string $to): string
{
    return 'https://www.google.com/maps?saddr=' . rawurlencode($from) . '&daddr=' . rawurlencode($to) . '&output=embed';
}
function local_directions_link(string $from, string $to): string
{
    return 'https://www.google.com/maps/dir/?api=1&origin=' . rawurlencode($from) . '&destination=' . rawurlencode($to);
}

/** The restaurant's address for maps ("47 Morton St, Clayton VIC 3168, Australia"). */
function local_address_query(array $s): string
{
    $q = trim((string) ($s['contact_map_query'] ?? ''));
    return $q !== '' ? $q : trim(rtrim(trim($s['address_line1']), ',') . ', ' . $s['address_line2']);
}

/** Short street address for text ("47 Morton St, Clayton VIC 3168"). */
function local_address_short(array $s): string
{
    return preg_replace('~,\s*Australia$~i', '', local_address_query($s));
}

/** FAQ list → schema.org FAQPage. */
function local_faq_schema(array $faq): array
{
    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn($q) => [
            '@type' => 'Question', 'name' => $q[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]],
        ], $faq),
    ];
}

/** Breadcrumb trail [[name, absolute url], …] → schema.org BreadcrumbList. */
function local_breadcrumb_schema(array $trail): array
{
    $items = [];
    foreach (array_values($trail) as $i => [$name, $url]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => $url];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** Send visitors who used an old ".php?item=" address to the clean one (301). */
function local_canonical_redirect(string $hub, string $slug): void
{
    if (!clean_urls_enabled() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $want = (string) parse_url(local_url($hub, $slug), PHP_URL_PATH);
    if ($path !== '' && rtrim($path, '/') !== rtrim($want, '/')) {
        header('Location: ' . local_url($hub, $slug), true, 301);
        exit;
    }
}

/** Show the 404 page and stop. */
function local_not_found(): void
{
    require APP_ROOT . '/404.php';
    exit;
}
