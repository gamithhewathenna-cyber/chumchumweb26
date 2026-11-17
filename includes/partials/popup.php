<?php
/**
 * Promotion popup (Admin → Popup Offers). Included by the footer.
 * The image only downloads when the popup opens, so it never slows the page.
 * How often it shows: every page / once per visit / once a day / once a week —
 * remembered in the visitor's browser and reset whenever the offer is changed.
 */
/** @var array $s */
$popup = popup_for_page($s);
if (!$popup) {
    return;
}
$pKey     = $popup['id'] . '-' . strtotime((string) $popup['updated_at']);
$pPreview = !empty($popup['preview']);
$pFreq    = $pPreview ? 'always' : (in_array($s['popup_frequency'] ?? '', ['always', 'session', 'day', 'week'], true) ? $s['popup_frequency'] : 'session');
$pDelay   = $pPreview ? 0 : max(0, min(60, (int) ($s['popup_delay'] ?? 2)));
$pTitle   = trim((string) $popup['title']) !== '' ? trim((string) $popup['title']) : 'Special offer';
$pLink    = has_link($popup['link_url']) ? link_url($popup['link_url']) : '';
$pExt     = $pLink !== '' && preg_match('~^https?://~i', $pLink) && stripos($pLink, (string) ($_SERVER['HTTP_HOST'] ?? '#')) === false;
$pBtn     = trim((string) $popup['button_text']);
?>
<div class="promo" id="promo" data-key="<?= e($pKey) ?>" data-freq="<?= e($pFreq) ?>" data-delay="<?= $pDelay ?>" hidden>
  <div class="promo__backdrop" data-promo-close></div>
  <div class="promo__dialog" role="dialog" aria-modal="true" aria-label="<?= e($pTitle) ?>" tabindex="-1">
    <button class="promo__close" type="button" aria-label="Close" data-promo-close>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
    </button>
    <?php if ($pLink !== ''): ?><a class="promo__media" href="<?= e($pLink) ?>" data-promo-click<?= $pExt ? ' target="_blank" rel="noopener"' : '' ?>><?php else: ?><div class="promo__media"><?php endif; ?>
      <img data-src="<?= e(img_url($popup['image'])) ?>" alt="<?= e($pTitle) ?>" width="800" height="1000" decoding="async">
    <?php if ($pLink !== ''): ?></a><?php else: ?></div><?php endif; ?>
    <?php if ($pLink !== '' && $pBtn !== ''): ?>
      <a class="promo__btn" href="<?= e($pLink) ?>" data-promo-click<?= $pExt ? ' target="_blank" rel="noopener"' : '' ?>><?= e($pBtn) ?></a>
    <?php endif; ?>
    <?php if ($pPreview): ?><p class="promo__preview">Preview — only you can see this<?= popup_status($popup) !== 'live' ? ' (this offer is not live on the website)' : '' ?>.</p><?php endif; ?>
  </div>
</div>
<script>
(function () {
  var box = document.getElementById('promo');
  if (!box) return;
  var key = box.getAttribute('data-key'), freq = box.getAttribute('data-freq');
  var store = freq === 'session' ? 'sessionStorage' : 'localStorage', SEEN = 'cc_promo_seen';
  var ages = { day: 864e5, week: 6048e5 };
  function seen() {
    if (freq === 'always') return false;
    try {
      var v = JSON.parse(window[store].getItem(SEEN) || 'null');
      if (!v || v.k !== key) return false;
      return freq === 'session' ? true : (Date.now() - v.t) < ages[freq];
    } catch (e) { return false; }
  }
  function remember() { try { window[store].setItem(SEEN, JSON.stringify({ k: key, t: Date.now() })); } catch (e) {} }
  function track(name) { try { (window.dataLayer = window.dataLayer || []).push({ event: name, promo_id: key }); } catch (e) {} }
  if (seen()) return;

  var img = box.querySelector('img'), lastFocus = null;
  function open() {
    lastFocus = document.activeElement;
    box.hidden = false;
    document.documentElement.classList.add('promo-lock');
    requestAnimationFrame(function () { box.classList.add('is-open'); });
    box.querySelector('.promo__close').focus({ preventScroll: true });
    remember(); track('promo_view');
  }
  function close() {
    box.classList.remove('is-open');
    document.documentElement.classList.remove('promo-lock');
    setTimeout(function () { box.hidden = true; }, 350);
    if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
  }
  box.addEventListener('click', function (e) {
    if (e.target.closest('[data-promo-close]')) close();
    if (e.target.closest('[data-promo-click]')) { track('promo_click'); remember(); }
  });
  document.addEventListener('keydown', function (e) {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'Tab') { // keep keyboard focus inside the popup
      var f = box.querySelectorAll('a[href], button'), first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  // Load the image after the page, then open once it's ready (never shows a blank box)
  function start() {
    img.onload = function () { setTimeout(open, <?= $pDelay * 1000 ?>); };
    img.onerror = function () { box.remove(); };
    img.src = img.getAttribute('data-src');
  }
  if (document.readyState === 'complete') start(); else window.addEventListener('load', start);
})();
</script>
