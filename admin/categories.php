<?php
/**
 * Home Page → Slider.
 * Each category is a pill around the hero AND a slide in the hero slider
 * (its own title, subtitle, badge and dish photo).
 */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_category_hero_columns($pdo);

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
        flash('success', 'Slide deleted.');
        redirect('categories.php');
    }

    if ($op === 'save') {
        $id   = (int) post('id');
        $row  = $id ? find_category($pdo, $id) : null;
        $data = [
            'name'          => post('name'),
            'link_url'      => post('link_url'),
            'side'          => post('side') === 'right' ? 'right' : 'left',
            'sort_order'    => (int) post('sort_order'),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
            'thumbnail'     => $row['thumbnail'] ?? '',
            'hero_badge'    => mb_substr(post('hero_badge'), 0, 100),
            'hero_title'    => mb_substr(post('hero_title'), 0, 150),
            'hero_subtitle' => mb_substr(post('hero_subtitle'), 0, 150),
            'hero_image'    => isset($_POST['hero_image_remove']) ? '' : (string) ($row['hero_image'] ?? ''),
        ];
        try {
            if ($id && !$row) {
                throw new RuntimeException('That slide no longer exists.');
            }
            if ($data['name'] === '') {
                throw new RuntimeException('Please enter a name.');
            }
            $data['link_url'] = clean_link($data['link_url']) ?: '#menu';
            if (($uploaded = handle_upload('thumbnail')) !== null) {
                $data['thumbnail'] = $uploaded;
            }
            if ($data['thumbnail'] === '') {
                throw new RuntimeException('Please choose a thumbnail image.');
            }
            if (($uploaded = handle_upload('hero_image')) !== null) {
                $data['hero_image'] = $uploaded;
            }
            $data['slug'] = unique_slug($pdo, $data['name'], $id);

            $cols   = ['name', 'slug', 'thumbnail', 'link_url', 'side', 'sort_order', 'is_active', 'hero_badge', 'hero_title', 'hero_subtitle', 'hero_image'];
            $values = array_map(fn($c) => $data[$c], $cols);
            if ($id) {
                $pdo->prepare('UPDATE menu_categories SET ' . implode(' = ?, ', $cols) . ' = ? WHERE id = ?')
                    ->execute(array_merge($values, [$id]));
            } else {
                $pdo->prepare('INSERT INTO menu_categories (' . implode(', ', $cols) . ') VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')')
                    ->execute($values);
            }
            flash('success', 'Slide "' . $data['name'] . '" saved.');
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
            flash('error', 'That slide was not found.');
            redirect('categories.php');
        }
        $form = $form ?: ['id' => 0, 'name' => '', 'link_url' => '#menu', 'side' => 'left', 'sort_order' => 0, 'is_active' => 1,
                          'thumbnail' => '', 'hero_badge' => '', 'hero_title' => '', 'hero_subtitle' => '', 'hero_image' => ''];
    }
    $defaults = get_settings();
    admin_page_start('Home Page', 'home');
    home_tabs('categories');
    ?>
    <p class="crumbs"><a href="categories.php">Slider</a> › <?= $form['id'] ? 'Edit slide' : 'Add slide' ?></p>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

      <section class="panel">
        <h2 class="panel__title">Pill button</h2>
        <?= field_text('name', 'Name', $form['name'], ['required' => true, 'maxlength' => 100, 'help' => 'The label on the pill, e.g. "Kottu".']) ?>
        <?= field_image('thumbnail', 'Pill thumbnail', $form['thumbnail'], 'Small cut-out dish photo (transparent PNG works best), about 120 × 75 px.') ?>
        <div class="field-row">
          <div class="field">
            <label for="f_side">Side of the hero</label>
            <select id="f_side" name="side">
              <option value="left"<?= $form['side'] === 'left' ? ' selected' : '' ?>>Left</option>
              <option value="right"<?= $form['side'] === 'right' ? ' selected' : '' ?>>Right</option>
            </select>
          </div>
          <?= field_text('sort_order', 'Position', $form['sort_order'], ['type' => 'number', 'step' => 1, 'help' => 'Lower numbers appear first (top).']) ?>
        </div>
        <?= field_text('link_url', 'Mobile menu link', $form['link_url'], ['maxlength' => 255,
            'help' => 'Where this dish links to in the phone "Menu" drawer. Leave as #menu for now.']) ?>
        <?= field_checkbox('is_active', 'Show on the website', (bool) $form['is_active']) ?>
      </section>

      <section class="panel">
        <h2 class="panel__title">Slide</h2>
        <p class="muted small">Clicking this pill shows this slide in the big hero area. Empty fields use the defaults from the Hero tab.</p>
        <?= field_image('hero_image', 'Slide dish photo', $form['hero_image'],
            'Large cut-out dish photo on a transparent or black background, about 1215 × 747 px.'
            . ($form['hero_image'] === '' ? ' Until you add one, the default hero photo is shown.' : '')) ?>
        <?php if ($form['hero_image'] !== ''): ?>
          <?= field_checkbox('hero_image_remove', 'Remove this photo (use the default hero photo instead)', false) ?>
        <?php endif; ?>
        <?= field_text('hero_title', 'Slide title', $form['hero_title'], ['maxlength' => 150,
            'placeholder' => $form['name'] !== '' ? $form['name'] : 'e.g. Chicken Kottu Roti', 'help' => 'Leave empty to use the pill name.']) ?>
        <?= field_text('hero_subtitle', 'Slide subtitle', $form['hero_subtitle'], ['maxlength' => 150, 'placeholder' => 'e.g. With Fresh Vegetables And Egg']) ?>
        <?= field_text('hero_badge', 'Slide badge', $form['hero_badge'], ['maxlength' => 100,
            'placeholder' => $defaults['hero_badge'], 'help' => 'The small pill above the title. Leave empty to use the default: "' . $defaults['hero_badge'] . '".']) ?>
        <div class="actions">
          <button class="btn btn--primary" type="submit">Save slide</button>
          <a class="btn btn--ghost" href="categories.php">Cancel</a>
        </div>
      </section>
    </form>
    <?php
    admin_page_end();
    exit;
}

/* ---------- List ---------- */
$rows = $pdo->query('SELECT * FROM menu_categories ORDER BY side, sort_order, id')->fetchAll();
$missingPhotos = count(array_filter($rows, fn($r) => $r['is_active'] && $r['hero_image'] === ''));

admin_page_start('Home Page', 'home');
home_tabs('categories');
?>
  <div class="toolbar">
    <p class="muted">The dish slider at the top of the home page. Each slide has a pill button beside it — clicking the pill shows that slide. The design fits three pills on each side.</p>
    <a class="btn btn--primary" href="categories.php?action=new">+ Add slide</a>
  </div>

  <?php if ($missingPhotos > 0): ?>
    <div class="alert alert--info"><?= $missingPhotos ?> dish<?= $missingPhotos === 1 ? ' has' : 'es have' ?> no slide photo yet, so the default hero photo is shown for <?= $missingPhotos === 1 ? 'it' : 'them' ?>. Click <strong>Edit</strong> to add one.</div>
  <?php endif; ?>

  <?php if (!$rows): ?>
    <div class="panel empty">No slides yet. The hero shows the default content from the Hero tab.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Pill</th><th>Name</th><th>Slide</th><th>Side</th><th>Position</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><img class="thumb" src="<?= e(img_url($r['thumbnail'])) ?>" alt=""></td>
            <td><strong><?= e($r['name']) ?></strong><?php if ($r['hero_title'] !== ''): ?><br><span class="muted small"><?= e($r['hero_title']) ?></span><?php endif; ?></td>
            <td><?= $r['hero_image'] !== ''
                ? '<img class="thumb thumb--slide" src="' . e(img_url($r['hero_image'])) . '" alt="">'
                : '<span class="badge badge--warn">No photo</span>' ?></td>
            <td><?= e(ucfirst($r['side'])) ?></td>
            <td><?= (int) $r['sort_order'] ?></td>
            <td><?= $r['is_active'] ? '<span class="badge badge--on">Shown</span>' : '<span class="badge">Hidden</span>' ?></td>
            <td class="table__actions">
              <a class="btn btn--small" href="categories.php?action=edit&amp;id=<?= (int) $r['id'] ?>">Edit</a>
              <form method="post" data-confirm="Delete the slide &quot;<?= e($r['name']) ?>&quot;?">
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
