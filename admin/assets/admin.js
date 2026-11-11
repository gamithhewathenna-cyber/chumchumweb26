/* Chum Chum's — admin panel interactions */
(function () {
  'use strict';

  /* ---------- Mobile sidebar ---------- */
  var toggle = document.querySelector('.topbar__toggle');
  var backdrop = document.querySelector('.sidebar-backdrop');
  function setNav(open) {
    document.body.classList.toggle('nav-open', open);
    if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (backdrop) backdrop.hidden = !open;
  }
  if (toggle) toggle.addEventListener('click', function () { setNav(!document.body.classList.contains('nav-open')); });
  if (backdrop) backdrop.addEventListener('click', function () { setNav(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setNav(false); });

  /* ---------- Keep the current tab visible in a scrolling tab bar (phones) ---------- */
  document.querySelectorAll('.tabs').forEach(function (bar) {
    var cur = bar.querySelector('[aria-current="page"]');
    if (cur && bar.scrollWidth > bar.clientWidth) {
      bar.scrollLeft = cur.offsetLeft - (bar.clientWidth - cur.offsetWidth) / 2;
    }
  });

  /* ---------- Confirm before destructive actions ---------- */
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  /* ---------- Live preview of a chosen image ---------- */
  document.querySelectorAll('.field--image input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var preview = input.closest('.image-picker').querySelector('.image-picker__preview');
      if (!file || !preview) return;
      if (file.size > 5 * 1024 * 1024) {
        alert('That image is larger than 5 MB. Please choose a smaller file.');
        input.value = '';
        return;
      }
      var img = document.createElement('img');
      img.alt = '';
      img.src = URL.createObjectURL(file);
      preview.innerHTML = '';
      preview.appendChild(img);
    });
  });

  /* ---------- Warn about unsaved changes ---------- */
  var dirty = false;
  document.querySelectorAll('form.panel').forEach(function (form) {
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
  });
  window.addEventListener('beforeunload', function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();

/* Chum Chum's — Menu Page code editor, live preview and copy buttons */
(function () {
  'use strict';

  /* ---------- Copy buttons (Menu Images) ---------- */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    var text = btn.getAttribute('data-copy');
    var done = function () {
      var old = btn.textContent;
      btn.textContent = 'Copied ✓';
      setTimeout(function () { btn.textContent = old; }, 1500);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done);
    } else {
      var ta = document.createElement('textarea');
      ta.value = text; document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); done(); } catch (err) {}
      ta.remove();
    }
  });

  var form = document.getElementById('menu-code-form');
  if (!form) return;
  var codeBox = form.querySelector('#menu_code');
  var frame = form.querySelector('.preview__frame');
  var fontUrl = form.getAttribute('data-font');
  var dirty = false;

  /* ---------- Editor behaviour: Tab indents, Shift+Tab outdents, Ctrl/Cmd+S saves ---------- */
  [codeBox].forEach(function (box) {
    box.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab' || e.ctrlKey || e.metaKey || e.altKey) return;
      e.preventDefault();
      var s = box.selectionStart, end = box.selectionEnd;
      if (e.shiftKey) {
        var lineStart = box.value.lastIndexOf('\n', s - 1) + 1;
        if (box.value.substr(lineStart, 2) === '  ') {
          box.value = box.value.slice(0, lineStart) + box.value.slice(lineStart + 2);
          box.selectionStart = box.selectionEnd = Math.max(lineStart, s - 2);
        }
      } else {
        box.value = box.value.slice(0, s) + '  ' + box.value.slice(end);
        box.selectionStart = box.selectionEnd = s + 2;
      }
      box.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); dirty = false; form.submit(); }
  });

  /* ---------- Size counters ---------- */
  function count(box) {
    var el = form.querySelector('[data-count-for="' + box.id + '"]');
    var lines = box.value ? box.value.split('\n').length : 0;
    var kb = new Blob([box.value]).size / 1024;
    el.textContent = lines + ' lines · ' + (kb < 10 ? kb.toFixed(1) : Math.round(kb)) + ' KB';
    el.classList.toggle('is-big', kb > 1800);
  }

  /* ---------- Live preview (approximates the website: dark background, site font) ---------- */
  function render() {
    var html = codeBox.value.replace(/<script\b[\s\S]*?<\/script\s*>/gi, '');
    frame.srcdoc = '<!doctype html><html><head><meta charset="utf-8"><base target="_blank">'
      + '<style>@font-face{font-family:"DM Sans";src:url("' + fontUrl + '") format("woff2");font-weight:100 1000;}'
      + 'html,body{margin:0}body{padding:28px 24px;background:#000;color:#fff;font-family:"DM Sans",system-ui,sans-serif;line-height:1.45}'
      + '*,*::before,*::after{box-sizing:border-box}img{max-width:100%;height:auto}a{color:#e7af27}</style>'
      + '</head><body>' + html + '</body></html>'; // the pasted <style> works anywhere in the page
  }
  var timer = null;
  function onEdit() {
    dirty = true;
    count(codeBox);
    clearTimeout(timer);
    timer = setTimeout(render, 350);
  }
  codeBox.addEventListener('input', onEdit);
  count(codeBox); render();

  // Grow the preview to fit its content
  function fit() {
    try {
      var h = frame.contentDocument.documentElement.scrollHeight;
      frame.style.height = Math.min(Math.max(h, 240), 3000) + 'px';
    } catch (err) {}
  }
  frame.addEventListener('load', function () { fit(); setTimeout(fit, 400); });

  // Preview widths
  form.querySelectorAll('[data-preview-width]').forEach(function (b) {
    b.addEventListener('click', function () {
      form.querySelectorAll('[data-preview-width]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
      frame.style.width = b.getAttribute('data-preview-width');
      setTimeout(fit, 350);
    });
  });

  /* ---------- Insert example ---------- */
  var exBtn = form.querySelector('[data-insert-example]');
  var exData = document.getElementById('menu-example');
  if (exBtn && exData) {
    exBtn.addEventListener('click', function () {
      if (codeBox.value.trim() && !confirm('Replace what is in the box with the example? (Nothing is saved until you press Save menu.)')) return;
      codeBox.value = JSON.parse(exData.textContent).code;
      onEdit();
    });
  }

  /* ---------- Unsaved changes warning ---------- */
  form.addEventListener('submit', function () { dirty = false; });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
})();
