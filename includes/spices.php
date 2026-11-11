<?php
/**
 * Falling spices behind (and a few in front of) the hero plate.
 * Pure decoration: inline SVG illustrations, animated in CSS (see "Falling spices" in style.css).
 */

/** SVG sprite with one <symbol> per ingredient. Chillies use currentColor so one shape serves red and green. */
function spice_sprite(): string
{
    $anise = '';
    for ($k = 0; $k < 8; $k++) {
        $anise .= '<g transform="rotate(' . ($k * 45) . ' 50 50)">'
            . '<path d="M50 50C43 40 41 22 50 9c9 13 7 31 0 41z" fill="#6b3218"/>'
            . '<path d="M50 46C46 38 46 24 50 15" stroke="#3d1a0b" stroke-width="1.5" fill="none"/>'
            . '<ellipse cx="50" cy="29" rx="3.6" ry="6.5" fill="#c98a3e"/></g>';
    }
    return '<svg class="spice-sprite" width="0" height="0" aria-hidden="true" focusable="false">'
        // Chilli (colour from CSS `color`)
        . '<symbol id="sp-chili" viewBox="0 0 100 100">'
        . '<path d="M24 26c10-8 26-4 36 8 12 14 20 32 28 52 1 3-2 5-4 3C70 76 54 64 38 54 24 45 16 34 24 26z" fill="currentColor"/>'
        . '<path d="M38 54C54 64 70 76 84 89c2 2 5 0 4-3-2-4-3-8-5-11-14-10-30-18-45-21z" fill="#000" fill-opacity=".28"/>'
        . '<path d="M31 29c11-4 22 1 30 10" stroke="#fff" stroke-opacity=".38" stroke-width="3.5" fill="none" stroke-linecap="round"/>'
        . '<path d="M24 26c-6-2-9 4-5 8 3-2 6-4 5-8z" fill="#3d6e25"/>'
        . '<path d="M22 28c-6-6-8-14-4-20" stroke="#3d6e25" stroke-width="4" fill="none" stroke-linecap="round"/>'
        . '</symbol>'
        // Curry leaf
        . '<symbol id="sp-leaf" viewBox="0 0 100 100">'
        . '<path d="M12 88C14 52 40 18 88 12 86 50 58 84 12 88z" fill="#2f6b2a"/>'
        . '<path d="M12 88C40 70 62 46 88 12 86 50 58 84 12 88z" fill="#000" fill-opacity=".22"/>'
        . '<path d="M14 86C34 62 56 38 86 14" stroke="#86c45e" stroke-width="2.5" fill="none" stroke-opacity=".7"/>'
        . '</symbol>'
        // Star anise
        . '<symbol id="sp-anise" viewBox="0 0 100 100">' . $anise . '<circle cx="50" cy="50" r="6" fill="#4a2210"/></symbol>'
        // Cinnamon stick
        . '<symbol id="sp-cinnamon" viewBox="0 0 100 100"><g transform="rotate(-35 50 50)">'
        . '<rect x="6" y="40" width="86" height="20" rx="6" fill="#8a4b22"/>'
        . '<rect x="6" y="40" width="86" height="7" rx="3.5" fill="#b06a35"/>'
        . '<path d="M10 54h78" stroke="#5e3015" stroke-width="2"/>'
        . '<ellipse cx="91" cy="50" rx="4.5" ry="10" fill="#5e3015"/>'
        . '<path d="M91 43a5 7 0 0 1 0 14" stroke="#c98a4f" stroke-width="2" fill="none"/>'
        . '</g></symbol>'
        // Cardamom pod
        . '<symbol id="sp-cardamom" viewBox="0 0 100 100"><g transform="rotate(28 50 50)">'
        . '<ellipse cx="50" cy="50" rx="17" ry="32" fill="#8aa94f"/>'
        . '<path d="M50 18c-7 10-9 22-9 32s2 22 9 32M50 18c7 10 9 22 9 32s-2 22-9 32" stroke="#64812f" stroke-width="2.2" fill="none"/>'
        . '<path d="M43 26c-4 8-5 16-4 24" stroke="#c6dc8e" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-opacity=".7"/>'
        . '<path d="M50 82l-2 7h4z" fill="#64812f"/>'
        . '</g></symbol>'
        // Black peppercorn
        . '<symbol id="sp-pepper" viewBox="0 0 100 100">'
        . '<defs><radialGradient id="sp-pepper-g" cx=".38" cy=".35" r=".7"><stop offset="0" stop-color="#5a4232"/><stop offset="1" stop-color="#140b06"/></radialGradient></defs>'
        . '<circle cx="50" cy="50" r="30" fill="url(#sp-pepper-g)"/>'
        . '<path d="M32 44c8-4 14 2 22-2M38 60c6-4 14 0 20-4M56 34c4 4 8 4 12 2" stroke="#0a0503" stroke-width="2.4" fill="none" stroke-linecap="round"/>'
        . '<circle cx="40" cy="38" r="5" fill="#fff" fill-opacity=".22"/>'
        . '</symbol>'
        . '</svg>';
}

/**
 * The falling pieces. Each row: symbol, colour, x position (%), size (design px), layer,
 * fall time (s), start offset (s, negative = already mid-fall on load), sideways drift (px), spin time (s).
 * back-far: small, darkest, most blurred · back: mid-sized · front: big soft bokeh near the edges, in front of the plate.
 */
function spice_layout(): array
{
    $red = '#d4301c';
    $green = '#4f8a2b';
    return [
        ['chili',    $red,   5,  46, 'back',     15, -2,   40,  9],
        ['leaf',     '',     13, 34, 'back',     18, -9,  -30,  7],
        ['pepper',   '',     21, 14, 'back-far', 13, -5,   20,  6],
        ['anise',    '',     29, 36, 'back',     20, -14,  30, 14],
        ['cardamom', '',     37, 22, 'back-far', 16, -3,  -20,  8],
        ['chili',    $green, 46, 40, 'back',     19, -11, -40, 10],
        ['cinnamon', '',     55, 50, 'back',     22, -6,   30, 16],
        ['pepper',   '',     63, 12, 'back-far', 14, -10, -15,  5],
        ['leaf',     '',     71, 30, 'back',     17, -1,   35,  7],
        ['chili',    $red,   79, 38, 'back',     16, -8,  -30,  9],
        ['cardamom', '',     87, 20, 'back-far', 18, -13,  20,  8],
        ['anise',    '',     94, 28, 'back-far', 15, -4,  -25, 12],
        ['cinnamon', '',     18, 40, 'back',     21, -17,  25, 15],
        ['chili',    $red,   33, 30, 'back',     17, -15, -25,  8],
        ['leaf',     '',     42, 26, 'back-far', 19, -7,   30,  8],
        ['pepper',   '',     58, 16, 'back',     15, -12,  15,  6],
        ['chili',    $green, 67, 34, 'back',     20, -16,  30, 11],
        ['anise',    '',     83, 32, 'back',     18, -10, -20, 13],
        ['chili',    $red,   2,  92, 'front',    11, -3,   50, 12],
        ['leaf',     '',     89, 80, 'front',    13, -8,  -40,  9],
        ['pepper',   '',     97, 42, 'front',     9, -6,   20,  6],
    ];
}

/** Render one layer of falling spices. */
function spice_layer(string $layer): string
{
    $html = '<div class="hero__spices hero__spices--' . $layer . '" aria-hidden="true">';
    foreach (spice_layout() as [$symbol, $color, $x, $size, $l, $dur, $delay, $drift, $spin]) {
        $isFront = $l === 'front';
        if (($layer === 'front') !== $isFront) {
            continue;
        }
        $html .= '<span class="spice spice--' . $l . '" style="--x:' . $x . '%;--size:' . $size . ';--dur:' . $dur . 's;--delay:' . $delay
            . 's;--drift:' . $drift . 'px;--spin:' . $spin . 's' . ($color !== '' ? ';color:' . $color : '') . '">'
            . '<svg viewBox="0 0 100 100"><use href="#sp-' . $symbol . '"/></svg></span>';
    }
    return $html . '</div>';
}
