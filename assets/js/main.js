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
      heroIndex = (i + heroSlides.length) % heroSlides.length;
      heroSlides.forEach(function (s, n) {
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
