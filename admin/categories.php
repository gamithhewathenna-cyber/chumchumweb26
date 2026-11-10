<?php
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

$action = (string) ($_GET['action'] ?? '');
$id     = (int) ($_GET['id'] ?? 0);

function find_category(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM menu_categories WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function unique_slug(PDO $pdo, string $name, int $exceptId): string
{
    $base = slugify($name);
    $slug = $base;
    $st   = $pdo->prepare('SELECT COUNT(*) FROM menu_categories WHERE slug = ? AND id <> ?');
    for ($n = 2; ; $n++) {
        $st->execute([$slug, $exceptId]);
        if ((int) $st->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $n;
    }
}

/* ---------- Handle POST ---------- */
if (is_post()) {
    verify_csrf();
    $op = post('op');

    if ($op === 'delete') {
        $pdo->prepare('DELETE FROM menu_categories WHERE id = ?')->execute([(int) post('id')]);
        flash('success', 'Category deleted.');
        redirect('categories.php');
    }

    if ($op === 'save') {
        $id   = (int) post('id');
        $row  = $id ? find_category($pdo, $id) : null;
        $data = [
            'name'       => post('name'),
            'link_url'   => post('link_url'),
            'side'       => post('side') === 'right' ? 'right' : 'left',
            'sort_order' => (int) post('sort_order'),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
            'thumbnail'  => $row['thumbnail'] ?? '',
        ];
        try {
            if ($id && !$row) {
                throw new RuntimeException('That category no longer exists.');
            }
            if ($data['name'] === '') {
                throw new RuntimeException('Please enter a name.');
            }
            $data['link_url'] = clean_link($data['link_url']) ?: '#menu';
            $uploaded = handle_upload('thumbnail');
            if ($uploaded !== null) {
                $data['thumbnail'] = $uploaded;
            }
            if ($data['thumbnail'] === '') {
                throw new RuntimeException('Please choose a thumbnail image.');
            }
            $data['slug'] = unique_slug($pdo, $data['name'], $id);

            if ($id) {
                $pdo->prepare('UPDATE menu_categories SET name = ?, slug = ?, thumbnail = ?, link_url = ?, side = ?, sort_order = ?, is_active = ? WHERE id = ?')
                    ->execute([$data['name'], $data['slug'], $data['thumbnail'], $data['link_url'], $data['side'], $data['sort_order'], $data['is_active'], $id]);
            } else {
                $pdo->prepare('INSERT INTO menu_categories (name, slug, thumbnail, link_url, side, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$data['name'], $data['slug'], $data['thumbnail'], $data['link_url'], $data['side'], $data['sort_order'], $data['is_active']]);
            }
            flash('success', 'Category "' . $data['name'] . '" saved.');
            redirect('categories.php');
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
            $action = $id ? 'edit' : 'new';
            $form   = ['id' => $id] + $data;
        }
    }
}

/* ---------- Edit / new form ---------- */
if ($action === 'new' || $action === 'edit') {
    if (!isset($form)) {
        $form = $action === 'edit' ? find_category($pdo, $id) : null;
        if ($action === 'edit' && !$form) {
            flash('error', 'That category was not found.');
            redirect('categories.php');
        }
        $form = $form ?: ['id' => 0, 'name' => '', 'link_url' => '#menu', 'side' => 'left', 'sort_order' => 0, 'is_active' => 1, 'thumbnail' => ''];
    }
    admin_page_start($form['id'] ? 'Edit category' : 'Add category', 'categories');
    ?>
    <p><a href="categories.php">← All categories</a></p>
    <form method="post" enctype="multipart/form-data" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
      <?= field_text('name', 'Name', $form['name'], ['required' => true, 'maxlength' => 100]) ?>
      <?= field_image('thumbnail', 'Thumbnail', $form['thumbnail'], 'Small cut-out dish photo (transparent PNG works best), about 120 × 75 px.') ?>
      <div class="field-row">
        <div class="field">
          <label for="f_side">Side of the hero bowl</label>
          <select id="f_side" name="side">
            <option value="left"<?= $form['side'] === 'left' ? ' selected' : '' ?>>Left</option>
            <option value="right"<?= $form['side'] === 'right' ? ' selected' : '' ?>>Right</option>
          </select>
        </div>
        <?= field_text('sort_order', 'Position', $form['sort_order'], ['type' => 'number', 'step' => 1, 'help' => 'Lower numbers appear first.']) ?>
      </div>
      <?= field_text('link_url', 'Link', $form['link_url'], ['maxlength' => 255, 'help' => 'Where the pill links to. Leave as #menu for now.']) ?>
      <?= field_checkbox('is_active', 'Show on the website', (bool) $form['is_active']) ?>
      <div class="actions">
        <button class="btn btn--primary" type="submit">Save category</button>
        <a class="btn btn--ghost" href="categories.php">Cancel</a>
      </div>
    </form>
    <?php
    admin_page_end();
    exit;
}

/* ---------- List ---------- */
$rows = $pdo->query('SELECT * FROM menu_categories ORDER BY side, sort_order, id')->fetchAll();

admin_page_start('Menu Categories', 'categories');
?>
  <div class="toolbar">
    <p class="muted">The dish pills shown to the left and right of the hero bowl. The design fits three on each side.</p>
    <a class="btn btn--primary" href="categories.php?action=new">+ Add category</a>
  </div>

  <?php if (!$rows): ?>
    <div class="panel empty">No categories yet. The website is showing the built-in defaults.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Image</th><th>Name</th><th>Side</th><th>Position</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><img class="thumb" src="<?= e(img_url($r['thumbnail'])) ?>" alt=""></td>
            <td><strong><?= e($r['name']) ?></strong></td>
            <td><?= e(ucfirst($r['side'])) ?></td>
            <td><?= (int) $r['sort_order'] ?></td>
            <td><?= $r['is_active'] ? '<span class="badge badge--on">Shown</span>' : '<span class="badge">Hidden</span>' ?></td>
            <td class="table__actions">
              <a class="btn btn--small" href="categories.php?action=edit&amp;id=<?= (int) $r['id'] ?>">Edit</a>
              <form method="post" data-confirm="Delete the category &quot;<?= e($r['name']) ?>&quot;?">
                <?= csrf_field() ?>
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn--small btn--danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php admin_page_end();
