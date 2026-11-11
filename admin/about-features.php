<?php
/** About Page → "Why Choose Us" cards: add, edit, reorder, hide, delete. */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_about_features_table($pdo);

$action = (string) ($_GET['action'] ?? '');
$id     = (int) ($_GET['id'] ?? 0);

function find_feature(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM about_features WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/* ---------- Actions ---------- */
if (is_post()) {
    verify_csrf();
    $op = post('op');

    if ($op === 'delete') {
        $pdo->prepare('DELETE FROM about_features WHERE id = ?')->execute([(int) post('id')]);
        flash('success', 'Card deleted.');
        redirect('about-features.php');
    }
    if ($op === 'toggle') {
        $pdo->prepare('UPDATE about_features SET is_active = 1 - is_active WHERE id = ?')->execute([(int) post('id')]);
        flash('success', 'Card updated.');
        redirect('about-features.php');
    }
    if ($op === 'move') {
        // Swap positions with the neighbouring card
        $rows = $pdo->query('SELECT id FROM about_features ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN);
        $pos  = array_search((int) post('id'), array_map('intval', $rows), true);
        $to   = post('dir') === 'up' ? $pos - 1 : $pos + 1;
        if ($pos !== false && isset($rows[$to])) {
            [$rows[$pos], $rows[$to]] = [$rows[$to], $rows[$pos]];
            $st = $pdo->prepare('UPDATE about_features SET sort_order = ? WHERE id = ?');
            foreach ($rows as $i => $rid) {
                $st->execute([$i + 1, (int) $rid]);
            }
        }
        redirect('about-features.php');
    }
    if ($op === 'save') {
        $id   = (int) post('id');
        $data = [
            'title'     => mb_substr(post('title'), 0, 100),
            'text'      => mb_substr(post('text'), 0, 400),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($id && !find_feature($pdo, $id)) {
            flash('error', 'That card no longer exists.');
            redirect('about-features.php');
        }
        if ($data['title'] === '') {
            flash('error', 'Please enter a title.');
            $action = $id ? 'edit' : 'new';
            $form   = ['id' => $id] + $data;
        } else {
            if ($id) {
                $pdo->prepare('UPDATE about_features SET title = ?, text = ?, is_active = ? WHERE id = ?')
                    ->execute([$data['title'], $data['text'], $data['is_active'], $id]);
            } else {
                $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM about_features')->fetchColumn();
                $pdo->prepare('INSERT INTO about_features (title, text, sort_order, is_active) VALUES (?, ?, ?, ?)')
                    ->execute([$data['title'], $data['text'], $next, $data['is_active']]);
            }
            flash('success', 'Card "' . $data['title'] . '" saved.');
            redirect('about-features.php');
        }
    }
}

/* ---------- Add / edit form ---------- */
if ($action === 'new' || $action === 'edit') {
    if (!isset($form)) {
        $form = $action === 'edit' ? find_feature($pdo, $id) : null;
        if ($action === 'edit' && !$form) {
            flash('error', 'That card was not found.');
            redirect('about-features.php');
        }
        $form = $form ?: ['id' => 0, 'title' => '', 'text' => '', 'is_active' => 1];
    }
    admin_page_start('About Page', 'about-page');
    about_tabs('cards');
    ?>
    <p class="crumbs"><a href="about-features.php">Why Choose Us Cards</a> › <?= $form['id'] ? 'Edit card' : 'Add card' ?></p>
    <form method="post" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
      <?= field_text('title', 'Title', $form['title'], ['required' => true, 'maxlength' => 100, 'help' => 'Short and bold, e.g. "Freshly Prepared".']) ?>
      <?= field_text('text', 'Text', $form['text'], ['type' => 'textarea', 'rows' => 3, 'maxlength' => 400, 'help' => 'One or two short sentences.']) ?>
      <?= field_checkbox('is_active', 'Show on the website', (bool) $form['is_active']) ?>
      <div class="actions">
        <button class="btn btn--primary" type="submit">Save card</button>
        <a class="btn btn--ghost" href="about-features.php">Cancel</a>
      </div>
    </form>
    <?php
    admin_page_end();
    exit;
}

/* ---------- List ---------- */
$rows = $pdo->query('SELECT * FROM about_features ORDER BY sort_order, id')->fetchAll();
$last = count($rows) - 1;

admin_page_start('About Page', 'about-page');
about_tabs('cards');
?>
  <div class="toolbar">
    <p class="muted">The cards under "Why Choose Chumchums?". Five fit neatly in one row on desktop; use the arrows to change the order.</p>
    <a class="btn btn--primary" href="about-features.php?action=new">+ Add card</a>
  </div>

  <?php if (!$rows): ?>
    <div class="panel empty">No cards yet — the cards row is hidden on the website.</div>
  <?php else: ?>
    <div class="feature-list">
      <?php foreach ($rows as $i => $r): ?>
        <article class="feature-card<?= $r['is_active'] ? '' : ' is-muted' ?>">
          <h2><?= e($r['title']) ?> <?= $r['is_active'] ? '' : '<span class="badge">Hidden</span>' ?></h2>
          <p><?= e($r['text']) ?></p>
          <div class="feature-card__actions">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="up"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--ghost" type="submit" aria-label="Move left"<?= $i === 0 ? ' disabled' : '' ?>>←</button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="down"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--ghost" type="submit" aria-label="Move right"<?= $i === $last ? ' disabled' : '' ?>>→</button></form>
            <a class="btn btn--small" href="about-features.php?action=edit&amp;id=<?= (int) $r['id'] ?>">Edit</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="toggle"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--ghost" type="submit"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button></form>
            <form method="post" data-confirm="Delete the card &quot;<?= e($r['title']) ?>&quot;?"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--danger" type="submit">Delete</button></form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php admin_page_end();
