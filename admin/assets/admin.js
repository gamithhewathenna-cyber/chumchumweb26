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

  /* ---------- Live preview: the same sealed frame as the website (scripts run, fully isolated) ---------- */
  var parts = JSON.parse((document.getElementById('menu-frame-parts') || {}).textContent || '{}');
  // Mirrors menu_document() in includes/menu.php
  function buildDocument(code) {
    if (!/<html\b|<body\b|<head\b/i.test(code)) {
      return parts.fragment.split(parts.marker).join(code);
    }
    var head = '<meta charset="utf-8">' + parts.defaults;
    if (/<head\b[^>]*>/i.test(code)) {
      code = code.replace(/<head\b[^>]*>/i, function (m) { return m + head; });
    } else if (/<html\b[^>]*>/i.test(code)) {
      code = code.replace(/<html\b[^>]*>/i, function (m) { return m + '<head>' + head + '</head>'; });
    } else {
      code = '<head>' + head + '</head>' + code;
    }
    return /<\/body\s*>/i.test(code)
      ? code.replace(/<\/body\s*>/i, function (m) { return parts.resize + m; })
      : code + parts.resize;
  }
  function render() { frame.srcdoc = buildDocument(codeBox.value); }
  var timer = null;
  function onEdit() {
    dirty = true;
    count(codeBox);
    clearTimeout(timer);
    timer = setTimeout(render, 350);
  }
  codeBox.addEventListener('input', onEdit);
  count(codeBox); render();

  // Grow the preview to fit its content (the sealed frame reports its own height)
  var lastHeight = 0;
  function fit() { if (lastHeight) frame.style.height = Math.min(Math.max(lastHeight, 200), 20000) + 'px'; }
  window.addEventListener('message', function (e) {
    if (e.source === frame.contentWindow && e.data && typeof e.data.chumMenuHeight === 'number') {
      lastHeight = Math.ceil(e.data.chumMenuHeight);
      fit();
    }
  });

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
