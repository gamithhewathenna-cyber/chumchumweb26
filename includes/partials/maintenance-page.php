<?php
/** @var array $s  site settings */
$mTitle   = trim((string) ($s['maintenance_title'] ?? '')) ?: "We'll Be Back Soon";
$mMessage = trim((string) ($s['maintenance_message'] ?? ''));
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($mTitle) ?> | <?= e($s['site_name']) ?></title>
<meta name="theme-color" content="#000000">
<meta name="google-site-verification" content="cuSIctaQ66p265EZgNNCAQYhP_QxzRI16NgKXT2ZPqk" />
<link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<style>
  .maint { min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; padding: 40px 16px; text-align: center; }
  .maint__inner { max-width: 620px; display: grid; justify-items: center; gap: 22px; }
  .maint__logo { width: 150px; height: auto; }
  .maint h1 { font-size: clamp(30px, 6vw, 48px); line-height: 1.1; font-weight: 600; }
  .maint__text { color: var(--text-soft); font-size: 18px; line-height: 1.6; white-space: pre-line; }
  .maint__contact { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 24px; margin-top: 6px; }
  .maint__contact a { color: var(--gold); font-weight: 600; }
  .maint__contact a:hover { text-decoration: underline; }
  .maint__preview { position: fixed; inset: 0 0 auto; padding: 8px 16px; background: var(--gold); color: #000; font-weight: 600; font-size: 14px; }
  .maint__preview a { text-decoration: underline; }
</style>
</head>
<body>
<?php if (isset($_GET['preview_maintenance'])): ?>
  <div class="maint__preview">Preview only — this is what visitors see when maintenance mode is on. <a href="<?= e(base_url('admin/settings.php?tab=maintenance')) ?>">Back to admin</a></div>
<?php endif; ?>
<main class="maint">
  <div class="maint__inner">
    <img class="maint__logo" src="<?= e(asset('img/logo.png')) ?>" alt="<?= e($s['site_name']) ?>" width="204" height="159">
    <span class="tag tag--gold"><?= e($s['site_name']) ?></span>
    <h1><?= e($mTitle) ?></h1>
    <?php if ($mMessage !== ''): ?>
      <p class="maint__text"><?= e($mMessage) ?></p>
    <?php endif; ?>
    <div class="maint__contact">
      <?php if (!empty($s['phone'])): ?><a href="<?= e(tel_link($s['phone'])) ?>"><?= e($s['phone']) ?></a><?php endif; ?>
      <?php if (!empty($s['email'])): ?><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a><?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>
