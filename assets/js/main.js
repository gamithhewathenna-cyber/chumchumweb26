/* Chum Chum's — homepage interactions */
(function () {
  'use strict';

  /* ---------- Drawer menu ---------- */
  var drawer = document.getElementById('site-drawer');
  var toggle = document.querySelector('.menu-toggle');
  var lastFocus = null;

  function openDrawer() {
    if (!drawer) return;
    lastFocus = document.activeElement;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('drawer-open');
    var first = drawer.querySelector('.drawer__close');
    if (first) setTimeout(function () { first.focus(); }, 50);
  }

  function closeDrawer() {
    if (!drawer || !drawer.classList.contains('is-open')) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('drawer-open');
    if (lastFocus) lastFocus.focus();
  }

  if (toggle && drawer) {
    toggle.addEventListener('click', openDrawer);
    drawer.addEventListener('click', function (e) {
      if (e.target.closest('[data-drawer-close]')) closeDrawer();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeDrawer();
      if (e.key === 'Tab' && drawer.classList.contains('is-open')) {
        var f = drawer.querySelectorAll('a, button');
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* ---------- Motion: intro, scroll reveals, mouse parallax ---------- */
  window.ccReady = true;
  var root = document.documentElement;
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // End the page-load intro once its animations have finished
  setTimeout(function () { root.classList.remove('intro'); }, 2600);

  // Reveal sections as they scroll into view
  var revealEls = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
  if (calm || !('IntersectionObserver' in window)) {
    revealEls.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { entry.target.classList.add('is-in'); io.unobserve(entry.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  }

  // The hero dish and pills drift slightly with the mouse (desktop only)
  var heroEl = document.querySelector('.hero');
  if (heroEl && !calm && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    var frame = null, mx = 0, my = 0;
    heroEl.addEventListener('mousemove', function (e) {
      var r = heroEl.getBoundingClientRect();
      mx = ((e.clientX - r.left) / r.width - 0.5) * 2;
      my = ((e.clientY - r.top) / r.height - 0.5) * 2;
      if (!frame) frame = requestAnimationFrame(function () {
        heroEl.style.setProperty('--mx', mx.toFixed(3));
        heroEl.style.setProperty('--my', my.toFixed(3));
        frame = null;
      });
    });
    heroEl.addEventListener('mouseleave', function () {
      heroEl.style.setProperty('--mx', 0);
      heroEl.style.setProperty('--my', 0);
    });
  }

  /* ---------- Menu page: the sealed menu frame reports its height; grow/shrink to fit ---------- */
  var menuFrames = Array.prototype.slice.call(document.querySelectorAll('.menu-frame'));
  if (menuFrames.length) {
    window.addEventListener('message', function (e) {
      var h = e.data && typeof e.data.chumMenuHeight === 'number' ? e.data.chumMenuHeight : 0;
      if (!h) return;
      menuFrames.forEach(function (f) {
        if (f.contentWindow === e.source) f.style.height = Math.min(Math.max(Math.ceil(h), 120), 40000) + 'px';
      });
    });
  }

  /* ---------- Contact form ---------- */
  var cform = document.querySelector('[data-contact-form]');
  if (cform) {
    cform.addEventListener('submit', function (e) {
      // Friendly check before sending (the server checks everything again)
      var firstBad = null;
      cform.querySelectorAll('[required]').forEach(function (field) {
        var bad = !field.value.trim() || (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim()));
        field.closest('.cform__field').classList.toggle('has-error', bad);
        field.setAttribute('aria-invalid', bad ? 'true' : 'false');
        if (bad && !firstBad) firstBad = field;
      });
      if (firstBad) { e.preventDefault(); firstBad.focus(); return; }
      var btn = cform.querySelector('.cform__submit');
      btn.disabled = true;
      btn.querySelector('span').textContent = 'Sending…';
    });
    window.addEventListener('pageshow', function () { // re-enable after using the Back button
      var btn = cform.querySelector('.cform__submit');
      btn.disabled = false;
      btn.querySelector('span').textContent = 'Send Message';
    });
    cform.addEventListener('input', function (e) {
      var f = e.target.closest('.cform__field');
      if (f && f.classList.contains('has-error') && e.target.value.trim()) f.classList.remove('has-error');
    });
  }
  var sentBox = document.getElementById('contact-sent');
  if (sentBox) {
    // after load: the browser's jump to #contact-form would otherwise reset focus
    var focusSent = function () { setTimeout(function () { sentBox.focus({ preventScroll: true }); }, 50); };
    if (document.readyState === 'complete') focusSent(); else window.addEventListener('load', focusSent);
  }

  /* ---------- Hero dish slider ---------- */
  var hero = document.querySelector('.hero');
  var heroSlides = hero ? Array.prototype.slice.call(hero.querySelectorAll('.hero__slide')) : [];
  if (heroSlides.length > 1) {
    var pills = Array.prototype.slice.call(hero.querySelectorAll('.cat-pill[data-slide]'));
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var heroIndex = Math.max(0, heroSlides.findIndex(function (s) { return s.classList.contains('is-active'); }));
    var heroTimer = null;
    var HERO_DELAY = 5000;

    function showDish(i) {
      var next = (i + heroSlides.length) % heroSlides.length;
      if (next === heroIndex) return;

      // The outgoing plate whips away from the exact angle its slow spin had reached
      var leaving = heroSlides[heroIndex];
      var plate = leaving.querySelector('.hero__plate img');
      if (plate) {
        var angle = getComputedStyle(plate).rotate;
        plate.style.setProperty('--from', angle && angle !== 'none' ? angle : '0deg');
      }
      leaving.classList.remove('is-first');
      leaving.classList.add('is-leaving');
      setTimeout(function () { if (!leaving.classList.contains('is-active')) leaving.classList.remove('is-leaving'); }, 800);

      heroIndex = next;
      heroSlides.forEach(function (s, n) {
        if (n === heroIndex) s.classList.remove('is-leaving');
        var on = n === heroIndex;
        s.classList.toggle('is-active', on);
        if (on) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
      });
      pills.forEach(function (p) {
        var on = Number(p.getAttribute('data-slide')) === heroIndex;
        p.classList.toggle('is-active', on);
        p.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
    }
    function heroStop() { if (heroTimer) clearInterval(heroTimer); heroTimer = null; }
    function heroStart() {
      heroStop();
      if (!reduceMotion) heroTimer = setInterval(function () { showDish(heroIndex + 1); }, HERO_DELAY);
    }

    pills.forEach(function (p) {
      p.addEventListener('click', function () { showDish(Number(p.getAttribute('data-slide'))); heroStart(); });
      p.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        e.preventDefault();
        showDish(heroIndex + (e.key === 'ArrowRight' ? 1 : -1));
        var target = hero.querySelector('.cat-pill[data-slide="' + heroIndex + '"]');
        if (target) target.focus();
      });
    });

    // Pause while the visitor is pointing at / using the slider, or the tab is hidden
    hero.addEventListener('mouseenter', heroStop);
    hero.addEventListener('mouseleave', heroStart);
    hero.addEventListener('focusin', heroStop);
    hero.addEventListener('focusout', function (e) { if (!hero.contains(e.relatedTarget)) heroStart(); });
    document.addEventListener('visibilitychange', function () { if (document.hidden) heroStop(); else heroStart(); });

    // Swipe on phones
    var hx0 = null, hy0 = null;
    hero.addEventListener('touchstart', function (e) { hx0 = e.touches[0].clientX; hy0 = e.touches[0].clientY; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (hx0 === null) return;
      var dx = e.changedTouches[0].clientX - hx0, dy = e.changedTouches[0].clientY - hy0;
      if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) { showDish(heroIndex + (dx < 0 ? 1 : -1)); heroStart(); }
      hx0 = hy0 = null;
    });

    heroStart();
  }

  /* ---------- Testimonial slider ---------- */
  var stage = document.querySelector('.testimonials__stage');
  if (stage) {
    var slides = Array.prototype.slice.call(stage.querySelectorAll('.testimonial'));
    var prev = stage.querySelector('.testimonials__nav--prev');
    var next = stage.querySelector('.testimonials__nav--next');
    var index = 0;
    var timer = null;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function show(i) {
      if (slides.length < 2) return;
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) {
        var active = n === index;
        s.classList.toggle('is-active', active);
        if (active) s.removeAttribute('hidden'); else s.setAttribute('hidden', '');
      });
    }
    function start() { if (!reduce && slides.length > 1) { stop(); timer = setInterval(function () { show(index + 1); }, 6000); } }
    function stop() { if (timer) clearInterval(timer); timer = null; }

    if (prev) prev.addEventListener('click', function () { show(index - 1); start(); });
    if (next) next.addEventListener('click', function () { show(index + 1); start(); });
    stage.addEventListener('mouseenter', stop);
    stage.addEventListener('mouseleave', start);
    stage.addEventListener('focusin', stop);

    var x0 = null;
    stage.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) { show(index + (dx < 0 ? 1 : -1)); start(); }
      x0 = null;
    });
    start();
  }
})();
