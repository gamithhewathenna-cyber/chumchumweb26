<?php
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();

if (is_post()) {
    verify_csrf();
    $rows = is_array($_POST['rows'] ?? null) ? $_POST['rows'] : [];
    $new  = is_array($_POST['new'] ?? null) ? $_POST['new'] : [];

    $update = $pdo->prepare('UPDATE opening_hours SET label = ?, hours = ?, sort_order = ? WHERE id = ?');
    $delete = $pdo->prepare('DELETE FROM opening_hours WHERE id = ?');

    $pdo->beginTransaction();
    foreach ($rows as $id => $r) {
        if (!is_array($r)) {
            continue;
        }
        $label = trim((string) ($r['label'] ?? ''));
        $hours = trim((string) ($r['hours'] ?? ''));
        if (!empty($r['delete']) || ($label === '' && $hours === '')) {
            $delete->execute([(int) $id]);
        } else {
            $update->execute([mb_substr($label, 0, 60), mb_substr($hours, 0, 60), (int) ($r['sort_order'] ?? 0), (int) $id]);
        }
    }
    $label = trim((string) ($new['label'] ?? ''));
    $hours = trim((string) ($new['hours'] ?? ''));
    if ($label !== '' || $hours !== '') {
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM opening_hours')->fetchColumn();
        $pdo->prepare('INSERT INTO opening_hours (label, hours, sort_order) VALUES (?, ?, ?)')
            ->execute([mb_substr($label, 0, 60), mb_substr($hours, 0, 60), $next]);
    }
    $pdo->commit();

    flash('success', 'Opening hours saved.');
    redirect('hours.php');
}

$rows = $pdo->query('SELECT * FROM opening_hours ORDER BY sort_order, id')->fetchAll();

admin_page_start('Home Page', 'home');
home_tabs('hours');
?>
  <p class="muted">These rows appear in the yellow Opening Hours card. Each row shows as “Days : Hours”.</p>

  <form method="post" class="panel">
    <?= csrf_field() ?>
    <div class="table-wrap">
      <table class="table table--form">
        <thead><tr><th>Days</th><th>Hours</th><th>Position</th><th>Remove</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $id = (int) $r['id']; ?>
          <tr>
            <td><input type="text" name="rows[<?= $id ?>][label]" value="<?= e($r['label']) ?>" maxlength="60" aria-label="Days"></td>
            <td><input type="text" name="rows[<?= $id ?>][hours]" value="<?= e($r['hours']) ?>" maxlength="60" aria-label="Hours"></td>
            <td><input type="number" name="rows[<?= $id ?>][sort_order]" value="<?= (int) $r['sort_order'] ?>" step="1" class="input--narrow" aria-label="Position"></td>
            <td><label class="check"><input type="checkbox" name="rows[<?= $id ?>][delete]" value="1"> <span class="sr-only">Remove this row</span></label></td>
          </tr>
        <?php endforeach; ?>
          <tr class="table__new">
            <td><input type="text" name="new[label]" placeholder="e.g. Public Holidays" maxlength="60" aria-label="New row: days"></td>
            <td><input type="text" name="new[hours]" placeholder="e.g. 10AM - 10PM" maxlength="60" aria-label="New row: hours"></td>
            <td colspan="2" class="muted small">Add a new row</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="actions">
      <button class="btn btn--primary" type="submit">Save opening hours</button>
    </div>
  </form>
<?php admin_page_end();
