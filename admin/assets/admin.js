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
