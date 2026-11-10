<?php
/** @var string $title  @var string $active  @var array|null $user */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Chum Chum's Admin</title>
<link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
<link rel="stylesheet" href="assets/admin.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/admin.css') ?>">
</head>
<body class="admin">
<a class="skip-link" href="#content">Skip to content</a>

<header class="topbar">
  <button class="topbar__toggle" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Open navigation">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
  </button>
  <a class="topbar__brand" href="index.php"><img src="<?= e(asset('img/logo.png')) ?>" alt="" width="44" height="34"> Admin</a>
</header>

<aside class="sidebar" id="sidebar">
  <a class="sidebar__brand" href="index.php">
    <img src="<?= e(asset('img/logo.png')) ?>" alt="Chum Chum's" width="96" height="75">
    <span>Website Admin</span>
  </a>
  <nav aria-label="Admin">
    <ul class="sidebar__nav">
      <?php foreach (admin_nav() as $key => [$label, $path]): ?>
        <li><a href="<?= e($key) ?>.php"<?= $key === $active ? ' aria-current="page"' : '' ?>>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg>
          <?= e($label) ?>
        </a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <div class="sidebar__foot">
    <a class="btn btn--ghost btn--block" href="<?= e(base_url('')) ?>" target="_blank" rel="noopener">View website ↗</a>
    <form method="post" action="logout.php">
      <?= csrf_field() ?>
      <button class="btn btn--link btn--block" type="submit">Log out<?= $user ? ' (' . e($user['username']) . ')' : '' ?></button>
    </form>
  </div>
</aside>
<div class="sidebar-backdrop" hidden></div>

<main class="content" id="content">
  <h1 class="content__title"><?= e($title) ?></h1>
  <?php render_flashes(); ?>
