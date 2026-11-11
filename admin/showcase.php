<?php
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

/** What each of the 7 fixed slots is used for. */
function slot_type(int $slot): string
{
    if ($slot === 1) {
        return 'feature';
    }
    return in_array($slot, [2, 4, 6], true) ? 'text' : 'image';
}

$typeLabels = [
    'feature' => 'Large feature (photo + text)',
    'text'    => 'Text card',
    'image'   => 'Photo tile',
];

$items = get_showcase(); // DB rows merged over defaults, keyed by slot 1–7
$slot  = (int) ($_GET['slot'] ?? 0);

/* ---------- Save one slot ---------- */
if (is_post()) {
    verify_csrf();
    $slot = (int) post('slot');
    if ($slot < 1 || $slot > 7) {
        redirect('showcase.php');
    }
    $type = slot_type($slot);
    $item = $items[$slot];
    try {
        $item['title'] = post('title');
        if ($item['title'] === '') {
            throw new RuntimeException($type === 'image' ? 'Please enter a short description of the photo.' : 'Please enter a title.');
        }
        if ($type !== 'image') {
            $item['description'] = post('description');
            $item['link_url']    = clean_link(post('link_url'));
        }
        if ($type !== 'text') {
            $uploaded = handle_upload('image');
            if ($uploaded !== null) {
                $item['image'] = $uploaded;
            }
            if ((string) $item['image'] === '') {
                throw new RuntimeException('Please choose a photo.');
            }
        }
        $pdo->prepare('INSERT INTO showcase_items (slot, title, description, image, link_url) VALUES (?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description),
                                               image = VALUES(image), link_url = VALUES(link_url)')
            ->execute([$slot, mb_substr($item['title'], 0, 150), (string) $item['description'], (string) $item['image'], (string) $item['link_url']]);
        flash('success', 'Tile ' . $slot . ' saved' . (isset($uploaded) && $uploaded !== null ? ' with the new photo' : '') . '.');
        redirect('showcase.php#tile-' . $slot);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        $items[$slot] = $item;
    }
}

/* ---------- Edit form ---------- */
if ($slot >= 1 && $slot <= 7) {
    $type = slot_type($slot);
    $item = $items[$slot];
    admin_page_start('Home Page', 'home');
    home_tabs('showcase');
    ?>
    <p class="crumbs"><a href="showcase.php">Showcase Grid</a> › Edit tile <?= $slot ?></p>
    <form method="post" enctype="multipart/form-data" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="slot" value="<?= $slot ?>">
      <p class="badge"><?= e($typeLabels[$type]) ?></p>
      <?php if ($type === 'image'): ?>
        <?= field_image('image', 'Photo', $item['image'], 'About 535 × 467 px.') ?>
        <?= field_text('title', 'Photo description', $item['title'], ['required' => true, 'maxlength' => 150,
            'help' => 'Not shown on the page — read out by screen readers and used by Google. e.g. "Fresh Buddha Bowl".']) ?>
      <?php else: ?>
        <?php if ($type === 'feature'): ?>
          <?= field_image('image', 'Photo', $item['image'], 'Portrait, about 811 × 934 px.') ?>
        <?php endif; ?>
        <?= field_text('title', 'Title', $item['title'], ['required' => true, 'maxlength' => 150]) ?>
        <?= field_text('description', 'Text', $item['description'], ['type' => 'textarea', 'rows' => 5]) ?>
        <?= field_text('link_url', '"Discover More" button link', $item['link_url'], ['maxlength' => 255,
            'help' => 'Leave as #menu for now.']) ?>
      <?php endif; ?>
      <div class="actions">
        <button class="btn btn--primary" type="submit">Save tile</button>
        <a class="btn btn--ghost" href="showcase.php">Cancel</a>
      </div>
    </form>
    <?php
    admin_page_end();
    exit;
}

/* ---------- Overview: map of the grid + an editable card for every tile ---------- */
$sizes = [1 => 'Portrait, about 811 × 934 px.', 3 => 'About 535 × 467 px.', 5 => 'About 535 × 467 px.', 7 => 'About 535 × 467 px.'];
admin_page_start('Home Page', 'home');
home_tabs('showcase');
?>
  <p class="muted">The seven tiles below the About section on the home page. Tiles 1, 3, 5 and 7 are <strong>photos</strong> — use <strong>Change photo</strong> on their cards below. Tiles 2, 4 and 6 are text cards.</p>
  <div class="showcase-map" aria-label="Layout of the grid on the website">
    <?php foreach ($items as $n => $item): $type = slot_type((int) $n); ?>
      <a class="showcase-map__tile showcase-map__tile--<?= e($type) ?> showcase-map__tile--s<?= (int) $n ?>" href="#tile-<?= (int) $n ?>">
        <?php if ($type !== 'text' && $item['image']): ?>
          <img src="<?= e(img_url($item['image'])) ?>" alt="" loading="lazy" decoding="async">
          <span class="showcase-map__change">Change photo</span>
        <?php endif; ?>
        <span class="showcase-map__body">
          <span class="showcase-map__num"><?= (int) $n ?> · <?= e($typeLabels[$type]) ?></span>
          <strong><?= e($item['title']) ?></strong>
        </span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="tile-cards">
    <?php foreach ($items as $n => $item): $n = (int) $n; $type = slot_type($n); ?>
      <form method="post" enctype="multipart/form-data" class="panel tile-card tile-card--<?= e($type) ?>" id="tile-<?= $n ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="slot" value="<?= $n ?>">
        <h2 class="panel__title">Tile <?= $n ?> <span class="badge"><?= e($typeLabels[$type]) ?></span></h2>
        <?php if ($type !== 'text'): ?>
          <?= field_image('image', 'Photo', $item['image'], $sizes[$n] ?? '', 't' . $n . '-image') ?>
        <?php endif; ?>
        <?php if ($type === 'image'): ?>
          <?= field_text('title', 'Photo description', $item['title'], ['id' => 't' . $n . '-title', 'required' => true, 'maxlength' => 150,
              'help' => 'Not shown on the page — read out by screen readers and used by Google.']) ?>
        <?php else: ?>
          <?= field_text('title', 'Title', $item['title'], ['id' => 't' . $n . '-title', 'required' => true, 'maxlength' => 150]) ?>
          <?= field_text('description', 'Text', $item['description'], ['id' => 't' . $n . '-text', 'type' => 'textarea', 'rows' => 3]) ?>
          <?= field_text('link_url', '"Discover More" button link', $item['link_url'], ['id' => 't' . $n . '-link', 'maxlength' => 255]) ?>
        <?php endif; ?>
        <div class="actions"><button class="btn btn--primary" type="submit">Save tile <?= $n ?></button></div>
      </form>
    <?php endforeach; ?>
  </div>
<?php admin_page_end();
