<?php
/** Gallery Page: upload photos, set caption / tile size / visibility, reorder, delete; page title & SEO. */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_gallery_table($pdo);

$tab   = ($_GET['tab'] ?? '') === 'page' ? 'page' : 'photos';
$sizes = [
    'normal' => 'Normal (1 × 1)',
    'wide'   => 'Wide (2 columns)',
    'tall'   => 'Tall (2 rows)',
    'big'    => 'Big (2 × 2)',
];

/* ======================= Title & SEO ======================= */
if ($tab === 'page') {
    $fields = [
        'gallery_title'            => ['Page title', 'text', 'The big heading at the top, e.g. "Gallery".'],
        'gallery_intro'            => ['Intro text', 'textarea', 'The short paragraph beside the title.'],
        'gallery_empty_text'       => ['Message when there are no photos', 'textarea', ''],
        'gallery_meta_description' => ['Meta description', 'textarea', 'Shown under the page title in Google results. Aim for 120–160 characters.'],
    ];
    $current = settings_form_handle($pdo, $fields, 'gallery-page.php?tab=page', 'Gallery page settings saved.');
    admin_page_start('Gallery Page', 'gallery-page');
    gallery_tabs('page');
    settings_form_render($fields, $current, 'Title & SEO',
        '<a class="btn btn--ghost" href="' . e(base_url('gallery.php')) . '" target="_blank" rel="noopener">View gallery ↗</a>');
    admin_page_end();
    exit;
}

/* ======================= Photos ======================= */
function find_photo(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM gallery_images WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

if (is_post()) {
    verify_csrf();
    $op = post('op');
    $id = (int) post('id');

    if ($op === 'upload') {
        $size  = array_key_exists(post('size'), $sizes) ? post('size') : 'normal';
        $files = $_FILES['images'] ?? null;
        $ok = 0; $errors = [];
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM gallery_images')->fetchColumn();
        $ins  = $pdo->prepare('INSERT INTO gallery_images (image, caption, size, sort_order) VALUES (?, ?, ?, ?)');
        if ($files && is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                $_FILES['one'] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i],
                                  'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                try {
                    if (($path = handle_upload('one', 'gallery')) !== null) {
                        // A tidy starting caption from the file name: "kottu-night_2.jpg" → "Kottu night 2"
                        $caption = ucfirst(trim(preg_replace('~[_\-\s]+~', ' ', pathinfo($name, PATHINFO_FILENAME))));
                        $ins->execute([$path, mb_substr($caption, 0, 200), $size, $next++]);
                        $ok++;
                    }
                } catch (RuntimeException $ex) {
                    $errors[] = $name . ': ' . $ex->getMessage();
                }
            }
        }
        if ($ok) {
            flash('success', $ok . ' photo' . ($ok === 1 ? '' : 's') . ' added to the end of the gallery. Edit the captions below.');
        }
        foreach ($errors as $err) {
            flash('error', $err);
        }
        if (!$ok && !$errors) {
            flash('error', 'Please choose at least one photo.');
        }
        redirect('gallery-page.php');
    }

    if ($op === 'save' && ($row = find_photo($pdo, $id))) {
        $size = array_key_exists(post('size'), $sizes) ? post('size') : 'normal';
        $pdo->prepare('UPDATE gallery_images SET caption = ?, size = ?, is_active = ? WHERE id = ?')
            ->execute([mb_substr(post('caption'), 0, 200), $size, isset($_POST['is_active']) ? 1 : 0, $id]);
        flash('success', 'Photo updated.');
        redirect('gallery-page.php#photo-' . $id);
    }

    if ($op === 'move') {
        $ids = array_map('intval', $pdo->query('SELECT id FROM gallery_images ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
        $pos = array_search($id, $ids, true);
        $to  = post('dir') === 'up' ? $pos - 1 : $pos + 1;
        if ($pos !== false && isset($ids[$to])) {
            [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];
            $st = $pdo->prepare('UPDATE gallery_images SET sort_order = ? WHERE id = ?');
            foreach ($ids as $i => $rid) {
                $st->execute([$i + 1, $rid]);
            }
        }
        redirect('gallery-page.php#photo-' . $id);
    }

    if ($op === 'delete' && ($row = find_photo($pdo, $id))) {
        $pdo->prepare('DELETE FROM gallery_images WHERE id = ?')->execute([$id]);
        // Remove the file too, if it was uploaded here and nothing else uses it
        $path = (string) $row['image'];
        if (strpos($path, 'gallery/') === 0 && preg_match('~^gallery/[\w.-]+$~', $path)) {
            $st = $pdo->prepare('SELECT COUNT(*) FROM gallery_images WHERE image = ?');
            $st->execute([$path]);
            if ((int) $st->fetchColumn() === 0) {
                @unlink(APP_ROOT . '/assets/img/' . $path);
            }
        }
        flash('success', 'Photo deleted.');
        redirect('gallery-page.php');
    }
    redirect('gallery-page.php');
}

$photos = $pdo->query('SELECT * FROM gallery_images ORDER BY sort_order, id')->fetchAll();
$shown  = count(array_filter($photos, fn($p) => $p['is_active']));
$last   = count($photos) - 1;

admin_page_start('Gallery Page', 'gallery-page');
gallery_tabs('photos');
?>
  <form method="post" enctype="multipart/form-data" class="panel">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="upload">
    <h2 class="panel__title">Add photos</h2>
    <div class="field-row">
      <div class="field"><label for="f_images">Choose photos <span class="muted small">(you can pick several)</span></label>
        <input id="f_images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
        <p class="help">JPG, PNG, WebP or GIF, max 5 MB each. Large, good-quality photos look best.</p></div>
      <div class="field"><label for="f_size">Tile size</label>
        <select id="f_size" name="size">
          <?php foreach ($sizes as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <p class="help">You can change each photo's size afterwards.</p></div>
    </div>
    <div class="actions"><button class="btn btn--primary" type="submit">Upload</button>
      <a class="btn btn--ghost" href="<?= e(base_url('gallery.php')) ?>" target="_blank" rel="noopener">View gallery ↗</a></div>
  </form>

  <?php if (!$photos): ?>
    <div class="panel empty">No photos yet — upload some above.</div>
  <?php else: ?>
    <section class="panel">
      <h2 class="panel__title">Layout preview <span class="muted small">(<?= $shown ?> shown<?= $shown < count($photos) ? ', ' . (count($photos) - $shown) . ' hidden' : '' ?> — this is how the website arranges them on a computer)</span></h2>
      <div class="gal-map">
        <?php foreach ($photos as $p): if (!$p['is_active']) continue; ?>
          <a class="gal-map__tile gal-map__tile--<?= e($p['size']) ?>" href="#photo-<?= (int) $p['id'] ?>" title="<?= e($p['caption']) ?>">
            <img src="<?= e(img_url($p['image'])) ?>" alt="" loading="lazy">
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="toolbar"><p class="muted">Each photo: write a caption (shown when visitors hover or open it), pick a tile size, then <strong>Save</strong>. Use the arrows to change the order.</p></div>
    <div class="photo-list">
      <?php foreach ($photos as $i => $p): $pid = (int) $p['id']; ?>
        <article class="photo-card<?= $p['is_active'] ? '' : ' is-muted' ?>" id="photo-<?= $pid ?>">
          <div class="photo-card__img"><img src="<?= e(img_url($p['image'])) ?>" alt="" loading="lazy">
            <span class="badge"><?= $i + 1 ?> · <?= e(ucfirst($p['size'])) ?><?= $p['is_active'] ? '' : ' · Hidden' ?></span></div>
          <form method="post" class="photo-card__form">
            <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= $pid ?>">
            <label class="sr-only" for="cap-<?= $pid ?>">Caption</label>
            <input id="cap-<?= $pid ?>" type="text" name="caption" value="<?= e($p['caption']) ?>" maxlength="200" placeholder="Caption (optional)">
            <div class="photo-card__row">
              <label class="sr-only" for="size-<?= $pid ?>">Tile size</label>
              <select id="size-<?= $pid ?>" name="size">
                <?php foreach ($sizes as $k => $label): ?><option value="<?= e($k) ?>"<?= $p['size'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
              </select>
              <label class="check-inline"><input type="checkbox" name="is_active" value="1"<?= $p['is_active'] ? ' checked' : '' ?>> Show</label>
            </div>
            <button class="btn btn--small btn--primary" type="submit">Save</button>
          </form>
          <div class="photo-card__actions">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="up"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--ghost" type="submit" aria-label="Move earlier"<?= $i === 0 ? ' disabled' : '' ?>>← Earlier</button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="down"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--ghost" type="submit" aria-label="Move later"<?= $i === $last ? ' disabled' : '' ?>>Later →</button></form>
            <form method="post" data-confirm="Delete this photo from the gallery?"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--danger" type="submit">Delete</button></form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php admin_page_end();
