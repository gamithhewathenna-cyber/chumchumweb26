<?php
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

$action = (string) ($_GET['action'] ?? '');
$id     = (int) ($_GET['id'] ?? 0);

function find_testimonial(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

if (is_post()) {
    verify_csrf();
    $op = post('op');

    if ($op === 'delete') {
        $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([(int) post('id')]);
        flash('success', 'Testimonial deleted.');
        redirect('testimonials.php');
    }

    if ($op === 'toggle') {
        $pdo->prepare('UPDATE testimonials SET is_active = 1 - is_active WHERE id = ?')->execute([(int) post('id')]);
        flash('success', 'Testimonial updated.');
        redirect('testimonials.php');
    }

    if ($op === 'save') {
        $id   = (int) post('id');
        $data = [
            'title'      => post('title'),
            'content'    => post('content'),
            'sort_order' => (int) post('sort_order'),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
        try {
            if ($id && !find_testimonial($pdo, $id)) {
                throw new RuntimeException('That testimonial no longer exists.');
            }
            if ($data['title'] === '' || $data['content'] === '') {
                throw new RuntimeException('Please fill in both the heading and the review text.');
            }
            $data['title'] = mb_substr($data['title'], 0, 150);
            if ($id) {
                $pdo->prepare('UPDATE testimonials SET title = ?, content = ?, sort_order = ?, is_active = ? WHERE id = ?')
                    ->execute([$data['title'], $data['content'], $data['sort_order'], $data['is_active'], $id]);
            } else {
                $pdo->prepare('INSERT INTO testimonials (title, content, sort_order, is_active) VALUES (?, ?, ?, ?)')
                    ->execute([$data['title'], $data['content'], $data['sort_order'], $data['is_active']]);
            }
            flash('success', 'Testimonial saved.');
            redirect('testimonials.php');
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
        $form = $action === 'edit' ? find_testimonial($pdo, $id) : null;
        if ($action === 'edit' && !$form) {
            flash('error', 'That testimonial was not found.');
            redirect('testimonials.php');
        }
        if (!$form) {
            $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM testimonials')->fetchColumn();
            $form = ['id' => 0, 'title' => '', 'content' => '', 'sort_order' => $next, 'is_active' => 1];
        }
    }
    admin_page_start($form['id'] ? 'Edit testimonial' : 'Add testimonial', 'testimonials');
    ?>
    <p><a href="testimonials.php">← All testimonials</a></p>
    <form method="post" class="panel">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
      <?= field_text('title', 'Heading', $form['title'], ['required' => true, 'maxlength' => 150,
          'help' => 'A short headline, e.g. "Best kottu in Melbourne!"']) ?>
      <?= field_text('content', 'Review', $form['content'], ['type' => 'textarea', 'rows' => 6, 'required' => true]) ?>
      <?= field_text('sort_order', 'Position', $form['sort_order'], ['type' => 'number', 'step' => 1, 'help' => 'Lower numbers show first in the slider.']) ?>
      <?= field_checkbox('is_active', 'Show on the website', (bool) $form['is_active']) ?>
      <div class="actions">
        <button class="btn btn--primary" type="submit">Save testimonial</button>
        <a class="btn btn--ghost" href="testimonials.php">Cancel</a>
      </div>
    </form>
    <?php
    admin_page_end();
    exit;
}

/* ---------- List ---------- */
$rows = $pdo->query('SELECT * FROM testimonials ORDER BY sort_order, id')->fetchAll();

admin_page_start('Testimonials', 'testimonials');
?>
  <div class="toolbar">
    <p class="muted">Reviews that rotate in the yellow slider. Hide any you don't want shown — if none are shown, the section is removed from the page.</p>
    <a class="btn btn--primary" href="testimonials.php?action=new">+ Add testimonial</a>
  </div>

  <?php if (!$rows): ?>
    <div class="panel empty">No testimonials yet.</div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($rows as $r): ?>
        <article class="list__item<?= $r['is_active'] ? '' : ' is-muted' ?>">
          <div class="list__body">
            <h2><?= e($r['title']) ?> <?= $r['is_active'] ? '<span class="badge badge--on">Shown</span>' : '<span class="badge">Hidden</span>' ?></h2>
            <p><?= e(mb_strimwidth($r['content'], 0, 220, '…')) ?></p>
            <p class="muted small">Position <?= (int) $r['sort_order'] ?></p>
          </div>
          <div class="list__actions">
            <a class="btn btn--small" href="testimonials.php?action=edit&amp;id=<?= (int) $r['id'] ?>">Edit</a>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="op" value="toggle">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--ghost" type="submit"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <form method="post" data-confirm="Delete this testimonial?">
              <?= csrf_field() ?>
              <input type="hidden" name="op" value="delete">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--small btn--danger" type="submit">Delete</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php admin_page_end();
