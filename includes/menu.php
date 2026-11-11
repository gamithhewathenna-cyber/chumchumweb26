<?php
/**
 * Menu page: turns the HTML + CSS pasted in Admin → Menu Page into something
 * safe to show inside the site.
 *
 * The menu is rendered inside a Shadow DOM, so its CSS can't restyle the site's
 * header/footer and the site's CSS can't break the menu. Because of that:
 *  - <style> blocks found in the pasted HTML are moved into the menu's CSS
 *  - `html`, `body` and `:root` selectors are mapped to the menu box (:host)
 *  - @font-face and @import rules (and <link> stylesheets, e.g. Google Fonts) are
 *    lifted to the page, because browsers ignore fonts declared inside a shadow root
 *  - <script>, <meta>, <title>, <base> and inline on…="" handlers are removed
 */

const MENU_MAX_BYTES = 2 * 1024 * 1024; // per field

/**
 * @return array{page_css:string, css:string, html:string}
 *   page_css: font rules for the normal page, css: styles for inside the menu box, html: the menu markup
 */
function menu_prepare(string $html, string $css): array
{
    $pageCss = '';
    $innerCss = '';

    // 1. Stylesheet links (e.g. Google Fonts) → @import on the page
    if (preg_match_all('~<link\b[^>]*>~i', $html, $links)) {
        foreach ($links[0] as $tag) {
            if (preg_match('~\brel\s*=\s*["\']?stylesheet~i', $tag) && preg_match('~\bhref\s*=\s*["\']([^"\']+)["\']~i', $tag, $h)
                && preg_match('~^(https?:)?//~i', $h[1])) {
                $pageCss .= '@import url("' . str_replace(['"', "\n", "\r"], '', $h[1]) . "\");\n";
            }
        }
    }

    // 2. <style> blocks inside the pasted HTML → menu CSS
    if (preg_match_all('~<style\b[^>]*>(.*?)</style\s*>~is', $html, $styles)) {
        $innerCss .= implode("\n", $styles[1]) . "\n";
    }
    $innerCss .= $css;

    // 3. Keep only what's inside <body> if a full page was pasted
    if (preg_match('~<body\b[^>]*>(.*)</body\s*>~is', $html, $body)) {
        $html = $body[1];
    } elseif (preg_match('~<body\b[^>]*>(.*)$~is', $html, $body)) {
        $html = $body[1];
    }

    // 4. Remove things that don't belong in a page section
    $html = preg_replace([
        '~<!DOCTYPE[^>]*>~i',
        '~<head\b[^>]*>.*?</head\s*>~is',
        '~</?(html|head|body)\b[^>]*>~i',
        '~<script\b[^>]*>.*?</script\s*>~is',
        '~<script\b[^>]*/?>~i',
        '~<style\b[^>]*>.*?</style\s*>~is',
        '~<(link|meta|base)\b[^>]*>~i',
        '~<title\b[^>]*>.*?</title\s*>~is',
        '~\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i',          // onclick="…" etc.
        '~(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2~i',          // javascript: links
    ], ['', '', '', '', '', '', '', '', '', '$1=$2#$2'], $html);
    $html = str_ireplace('</template', '&lt;/template', $html);   // can't close our wrapper early

    // 5. Fonts must be declared on the page, not inside the menu box
    if (preg_match_all('~@import\s+[^;]+;~i', $innerCss, $imports)) {
        $pageCss .= implode("\n", $imports[0]) . "\n";
        $innerCss = str_replace($imports[0], '', $innerCss);
    }
    if (preg_match_all('~@font-face\s*\{[^}]*\}~i', $innerCss, $faces)) {
        $pageCss .= implode("\n", $faces[0]) . "\n";
        $innerCss = str_replace($faces[0], '', $innerCss);
    }

    // 6. html / body / :root selectors mean "the whole menu" here
    $innerCss = preg_replace('~(^|[\s,{}>+\~(])(?:html|body|:root)(?=[\s,{.:#\[>+\~)]|$)~im', '$1:host', $innerCss);

    // Never let pasted text end a <style> element early
    $innerCss = str_ireplace('</style', '<\/style', $innerCss);
    $pageCss  = str_ireplace('</style', '<\/style', $pageCss);

    return ['page_css' => trim($pageCss), 'css' => trim($innerCss), 'html' => trim($html)];
}

/** One block of code: CSS (if any) in a <style> tag, followed by the HTML. */
function menu_combine(string $html, string $css): string
{
    return trim($css) === '' ? $html : "<style>\n" . trim($css) . "\n</style>\n\n" . $html;
}

/** Starter example shown by the admin's "Insert example" button (never saved automatically). */
function menu_example(): string
{
    $html = <<<'HTML'
<div class="menu">
  <section class="menu-section">
    <h2>Kottu</h2>
    <ul>
      <li><div><h3>Chicken Kottu</h3><p>Chopped godamba roti, vegetables, egg and spicy chicken curry.</p></div><span class="price">$18</span></li>
      <li><div><h3>Cheese Kottu</h3><p>Our classic kottu with melted cheese.</p></div><span class="price">$20</span></li>
    </ul>
  </section>
  <section class="menu-section">
    <h2>Rice &amp; Curry</h2>
    <ul>
      <li><div><h3>Yellow Rice &amp; Curry</h3><p>Fragrant yellow rice with three curries, sambol and papadam.</p></div><span class="price">$22</span></li>
    </ul>
  </section>
</div>
HTML;
    $css = <<<'CSS'
.menu { display: grid; gap: 48px; max-width: 900px; margin: 0 auto; }
.menu-section h2 { color: #e7af27; font-size: 30px; font-weight: 400; border-bottom: 1px solid #333; padding-bottom: 10px; margin: 0 0 18px; }
.menu-section ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 18px; }
.menu-section li { display: flex; justify-content: space-between; gap: 24px; align-items: baseline; }
.menu-section h3 { margin: 0; font-size: 18px; font-weight: 500; }
.menu-section p { margin: 4px 0 0; color: #bbb; font-size: 14px; }
.price { color: #e7af27; font-weight: 600; white-space: nowrap; }
CSS;
    return menu_combine($html, $css);
}
