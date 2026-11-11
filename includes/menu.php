<?php
/**
 * Menu page: the code pasted in Admin → Menu Page (HTML with its CSS and, if it
 * has one, its JavaScript) is shown inside a *sealed frame* — a sandboxed iframe.
 *
 * Why a sealed frame:
 *  - Menus built with JavaScript (tabs, categories, data lists) work exactly as they
 *    do on their own, because their scripts run normally inside the frame.
 *  - The browser isolates the frame completely: its scripts can't read the site,
 *    its cookies or the admin login, can't show pop-up alerts, and its CSS can't
 *    restyle the site's header/footer (and the site's CSS can't break the menu).
 *  - A tiny script added to the frame reports the menu's height, so the frame grows
 *    and shrinks with its content (see "Menu page" in assets/js/main.js).
 */

const MENU_MAX_BYTES = 2 * 1024 * 1024;

/** Sandbox flags shared by the website and the admin preview. No allow-same-origin: the menu runs isolated. */
const MENU_SANDBOX = 'allow-scripts allow-popups allow-popups-to-escape-sandbox';

/** Script injected into the frame: tells the parent page how tall the menu is. */
function menu_resize_script(): string
{
    // Measures the content (body), not the frame — so the frame can shrink as well as grow
    return '<script>(function(){var last=0;function send(){var b=document.body;if(!b)return;var cs=getComputedStyle(b);'
        . 'var h=Math.ceil(Math.max(b.scrollHeight,b.getBoundingClientRect().bottom+window.scrollY)+parseFloat(cs.marginBottom||0));'
        . 'if(h!==last){last=h;parent.postMessage({chumMenuHeight:h},"*");}}'
        . 'if(window.ResizeObserver){var ro=new ResizeObserver(send);ro.observe(document.documentElement);if(document.body)ro.observe(document.body);}'
        . 'window.addEventListener("load",send);document.addEventListener("click",function(){setTimeout(send,50);});setInterval(send,1000);send();})();</script>';
}

/**
 * Build the full HTML document shown inside the frame.
 * Works with a whole pasted page (<!doctype html>…) or just a fragment.
 */
/**
 * Defaults the pasted CSS can override: transparent so the site's dark background shows,
 * site-like text colour/font, links open in a new tab (the frame can't navigate the site).
 */
function menu_frame_defaults(): string
{
    return '<base target="_blank"><style id="chum-defaults">html{background:transparent}'
        . 'body{margin:0;color:#fff;font-family:"DM Sans",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;line-height:1.45}'
        . 'img{max-width:100%;height:auto}</style>';
}

function menu_document(string $code): string
{
    $base   = menu_frame_defaults();
    $resize = menu_resize_script();
    // NOTE: admin/assets/admin.js (menu preview) mirrors these steps — keep them in sync.

    if (preg_match('~<html\b|<body\b|<head\b~i', $code)) {
        // A whole page: put our defaults first in <head> (so the page's own CSS wins) and the resize script last
        if (preg_match('~<head\b[^>]*>~i', $code)) {
            $code = preg_replace('~<head\b[^>]*>~i', '$0<meta charset="utf-8">' . $base, $code, 1);
        } else {
            $code = preg_replace('~<html\b[^>]*>~i', '$0<head><meta charset="utf-8">' . $base . '</head>', $code, 1, $n);
            if (!$n) {
                $code = '<head><meta charset="utf-8">' . $base . '</head>' . $code;
            }
        }
        if (stripos($code, '</body>') !== false) {
            $code = preg_replace('~</body\s*>~i', $resize . '$0', $code, 1);
        } else {
            $code .= $resize;
        }
        return $code;
    }

    // Just a fragment: wrap it in a page
    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . $base . '</head><body>' . $code . $resize . '</body></html>';
}

/** One block of code: CSS (if any) in a <style> tag, followed by the HTML. (Older saves kept CSS separately.) */
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
