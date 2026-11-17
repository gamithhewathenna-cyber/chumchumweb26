<?php
/** Popup Offers: promotion popups shown when visitors arrive (800 × 1000 px images). */
require __DIR__ . '/includes/admin.php';
require_login();
$pdo = admin_db();
ensure_popups_table($pdo);

$freqs = [
    'session' => 'Once per visit (recommended)',
    'day'     => 'Once a day',
    'week'    => 'Once a week',
    'always'  => 'On every page (not recommended)',
];
$pagesOpt = ['all' => 'Every page', 'home' => 'Home page only'];

function find_popup(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM promo_popups WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Read + check the text fields of a popup form. */
function popup_input(): array
{
    $date = static function (string $key): ?string {
        $v = trim((string) ($_POST[$key] ?? ''));
        if ($v === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            throw new RuntimeException('Please pick valid start and end dates.');
        }
        return $v;
    };
    $in = [
        'title'       => mb_substr(post('title'), 0, 150),
        'link_url'    => mb_substr(clean_link(post('link_url')), 0, 500),
        'button_text' => mb_substr(post('button_text'), 0, 60),
        'starts_on'   => $date('starts_on'),
        'ends_on'     => $date('ends_on'),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($in['starts_on'] && $in['ends_on'] && $in['ends_on'] < $in['starts_on']) {
        throw new RuntimeException('The end date is before the start date.');
    }
    if ($in['button_text'] !== '' && $in['link_url'] === '') {
        throw new RuntimeException('A button needs a link — add the link, or leave the button text empty.');
    }
    return $in;
}

/** Friendly note when the uploaded image isn't 4:5 (800 × 1000). */
function popup_size_note(string $path): string
{
    $info = @getimagesize(APP_ROOT . '/assets/img/' . $path);
    if (!$info) {
        return '';
    }
    [$w, $h] = $info;
    $note = '';
    if (abs($w / $h - 0.8) > 0.03) {
        $note .= " Your image is {$w} × {$h} px — it will be cropped to fit 800 × 1000 (portrait 4:5).";
    } elseif ($w < 800) {
        $note .= " Your image is {$w} × {$h} px — 800 × 1000 px or larger will look sharper.";
    }
    return $note;
}

function popup_delete_file(PDO $pdo, string $path): void
{
    if (preg_match('~^promo/[\w.-]+$~', $path)) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM promo_popups WHERE image = ?');
        $st->execute([$path]);
        if ((int) $st->fetchColumn() === 0) {
            @unlink(APP_ROOT . '/assets/img/' . $path);
        }
    }
}

if (is_post()) {
    verify_csrf();
    $op = post('op');
    $id = (int) post('id');
    try {
        if ($op === 'settings') {
            $save = [
                'popup_enabled'   => isset($_POST['popup_enabled']) ? '1' : '0',
                'popup_frequency' => array_key_exists(post('popup_frequency'), $freqs) ? post('popup_frequency') : 'session',
                'popup_delay'     => (string) max(0, min(60, (int) post('popup_delay'))),
                'popup_pages'     => array_key_exists(post('popup_pages'), $pagesOpt) ? post('popup_pages') : 'all',
            ];
            $st = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($save as $k => $v) {
                $st->execute([$k, $v]);
            }
            flash('success', 'Popup settings saved.');
            redirect('popups.php');
        }

        if ($op === 'add') {
            $in   = popup_input();
            $path = handle_upload('image', 'promo');
            if ($path === null) {
                throw new RuntimeException('Please choose the offer image (800 × 1000 px).');
            }
            $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM promo_popups')->fetchColumn();
            $pdo->prepare('INSERT INTO promo_popups (title, image, link_url, button_text, starts_on, ends_on, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$in['title'], $path, $in['link_url'], $in['button_text'], $in['starts_on'], $in['ends_on'], $in['is_active'], $next]);
            $newId = (int) $pdo->lastInsertId();
            flash('success', 'Offer added.' . popup_size_note($path));
            redirect('popups.php#popup-' . $newId);
        }

        if ($op === 'save' && ($row = find_popup($pdo, $id))) {
            $in   = popup_input();
            $path = handle_upload('image', 'promo');
            $pdo->prepare('UPDATE promo_popups SET title = ?, link_url = ?, button_text = ?, starts_on = ?, ends_on = ?, is_active = ?, image = ? WHERE id = ?')
                ->execute([$in['title'], $in['link_url'], $in['button_text'], $in['starts_on'], $in['ends_on'], $in['is_active'], $path ?? $row['image'], $id]);
            if ($path !== null) {
                popup_delete_file($pdo, (string) $row['image']);
            }
            flash('success', 'Offer saved.' . ($path !== null ? popup_size_note($path) : ''));
            redirect('popups.php#popup-' . $id);
        }

        if ($op === 'move') {
            $ids = array_map('intval', $pdo->query('SELECT id FROM promo_popups ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
            $pos = array_search($id, $ids, true);
            $to  = post('dir') === 'up' ? $pos - 1 : $pos + 1;
            if ($pos !== false && isset($ids[$to])) {
                [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];
                $st = $pdo->prepare('UPDATE promo_popups SET sort_order = ? WHERE id = ?');
                foreach ($ids as $i => $rid) {
                    $st->execute([$i + 1, $rid]);
                }
            }
            redirect('popups.php#popup-' . $id);
        }

        if ($op === 'delete' && ($row = find_popup($pdo, $id))) {
            $pdo->prepare('DELETE FROM promo_popups WHERE id = ?')->execute([$id]);
            popup_delete_file($pdo, (string) $row['image']);
            flash('success', 'Offer deleted.');
            redirect('popups.php');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('popups.php' . ($id ? '#popup-' . $id : '#add'));
    }
    redirect('popups.php');
}

$s       = get_settings();
$popups  = $pdo->query('SELECT * FROM promo_popups ORDER BY sort_order, id')->fetchAll();
$today   = date('Y-m-d');
$liveId  = null;
foreach ($popups as $p) {
    if (popup_status($p, $today) === 'live') {
        $liveId = (int) $p['id'];
        break;
    }
}
$labels  = ['live' => 'Live', 'scheduled' => 'Scheduled', 'expired' => 'Ended', 'hidden' => 'Off'];
$last    = count($popups) - 1;
$on      = ($s['popup_enabled'] ?? '1') === '1';

admin_page_start('Popup Offers', 'popups');
?>
  <section class="panel">
    <h2 class="panel__title">Right now</h2>
    <?php if (!$on): ?>
      <p><span class="badge badge--bad">Popups are switched off</span> Nobody sees a popup. Tick “Show the popup” below to turn it on.</p>
    <?php elseif ($liveId): $lp = find_popup($pdo, $liveId); ?>
      <p><span class="badge badge--ok">Showing</span> Visitors see <strong><?= e($lp['title'] !== '' ? $lp['title'] : 'Offer #' . $liveId) ?></strong>
        <?= $lp['ends_on'] ? 'until ' . e(date('j M Y', strtotime($lp['ends_on']))) : '' ?>.
        <a href="<?= e(base_url('index.php?popup_preview=' . $liveId)) ?>" target="_blank" rel="noopener">Preview ↗</a></p>
    <?php else: ?>
      <p><span class="badge">Nothing live</span> No offer is on and within its dates, so no popup shows. Add one below.</p>
    <?php endif; ?>
    <p class="muted small">If more than one offer is live, the first one in the list below is shown. Use the arrows to change which comes first.</p>
  </section>

  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="op" value="settings">
    <h2 class="panel__title">Popup settings</h2>
    <?= field_checkbox('popup_enabled', 'Show the popup on the website', $on, 'Untick to hide all popups at once (your offers stay saved).') ?>
    <div class="field-row field-row--3">
      <div class="field"><label for="f_freq">How often</label>
        <select id="f_freq" name="popup_frequency">
          <?php foreach ($freqs as $k => $l): ?><option value="<?= e($k) ?>"<?= ($s['popup_frequency'] ?? 'session') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
        <p class="help">Each visitor sees a new or changed offer again straight away.</p></div>
      <div class="field"><label for="f_delay">Delay (seconds)</label>
        <input id="f_delay" type="number" name="popup_delay" min="0" max="60" value="<?= e((string) ($s['popup_delay'] ?? '2')) ?>">
        <p class="help">Wait after the page loads. 2–5 seconds feels natural.</p></div>
      <div class="field"><label for="f_pages">Where</label>
        <select id="f_pages" name="popup_pages">
          <?php foreach ($pagesOpt as $k => $l): ?><option value="<?= e($k) ?>"<?= ($s['popup_pages'] ?? 'all') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <div class="actions"><button class="btn btn--primary" type="submit">Save settings</button></div>
  </form>

  <form method="post" enctype="multipart/form-data" class="panel" id="add">
    <?= csrf_field() ?><input type="hidden" name="op" value="add">
    <h2 class="panel__title">Add an offer</h2>
    <div class="field"><label for="f_image">Offer image <span class="muted small">(800 × 1000 px, portrait)</span></label>
      <input id="f_image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
      <p class="help">JPG, PNG or WebP, max 5 MB. Design it at 800 × 1000 px with the offer text inside the image. Keep the bottom 100 px clear if you add a button.</p></div>
    <div class="field-row">
      <?= field_text('title', 'Offer name', '', ['maxlength' => 150, 'placeholder' => 'e.g. 20% off Kottu Tuesdays', 'help' => 'Read out to blind visitors and shown here in the admin.']) ?>
      <?= field_text('link_url', 'Link (optional)', '', ['maxlength' => 500, 'placeholder' => 'e.g. menu.php or https://order…', 'help' => 'Where a click on the offer goes. Leave empty for no link.']) ?>
    </div>
    <div class="field-row field-row--3">
      <?= field_text('button_text', 'Button text (optional)', '', ['maxlength' => 60, 'placeholder' => 'e.g. Order now', 'help' => 'A gold button over the bottom of the image. Needs a link.']) ?>
      <?= field_text('starts_on', 'Start date (optional)', '', ['type' => 'date', 'help' => 'Empty = starts now.']) ?>
      <?= field_text('ends_on', 'End date (optional)', '', ['type' => 'date', 'help' => 'Empty = runs until you turn it off. Shows through the end of this day.']) ?>
    </div>
    <?= field_checkbox('is_active', 'Turn this offer on', true) ?>
    <div class="actions"><button class="btn btn--primary" type="submit">Add offer</button></div>
  </form>

  <?php if (!$popups): ?>
    <div class="panel empty">No offers yet — add your first one above.</div>
  <?php else: ?>
    <h2 class="section-title">Your offers</h2>
    <div class="promo-list">
      <?php foreach ($popups as $i => $p): $pid = (int) $p['id']; $st = popup_status($p, $today); ?>
        <article class="promo-card<?= $st === 'live' ? '' : ' is-muted' ?>" id="popup-<?= $pid ?>">
          <div class="promo-card__img">
            <img src="<?= e(img_url($p['image'])) ?>" alt="" loading="lazy">
            <span class="badge <?= $st === 'live' ? ($pid === $liveId && $on ? 'badge--ok' : '') : ($st === 'expired' ? 'badge--bad' : '') ?>">
              <?= $i + 1 ?> · <?= e($labels[$st]) ?><?= $st === 'live' && $pid === $liveId && $on ? ' — showing now' : ($st === 'live' ? ' — waiting (another is first)' : '') ?></span>
          </div>
          <form method="post" enctype="multipart/form-data" class="promo-card__form">
            <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= $pid ?>">
            <?= field_text('title', 'Offer name', $p['title'], ['maxlength' => 150, 'id' => 'pt-' . $pid]) ?>
            <?= field_text('link_url', 'Link', $p['link_url'], ['maxlength' => 500, 'id' => 'pl-' . $pid]) ?>
            <?= field_text('button_text', 'Button text', $p['button_text'], ['maxlength' => 60, 'id' => 'pb-' . $pid]) ?>
            <div class="field-row">
              <?= field_text('starts_on', 'Start date', (string) $p['starts_on'], ['type' => 'date', 'id' => 'ps-' . $pid]) ?>
              <?= field_text('ends_on', 'End date', (string) $p['ends_on'], ['type' => 'date', 'id' => 'pe-' . $pid]) ?>
            </div>
            <div class="field"><label for="pi-<?= $pid ?>">Replace image <span class="muted small">(optional)</span></label>
              <input id="pi-<?= $pid ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></div>
            <label class="check-inline"><input type="checkbox" name="is_active" value="1"<?= $p['is_active'] ? ' checked' : '' ?>> On</label>
            <div class="actions">
              <button class="btn btn--small btn--primary" type="submit">Save</button>
              <a class="btn btn--small btn--ghost" href="<?= e(base_url('index.php?popup_preview=' . $pid)) ?>" target="_blank" rel="noopener">Preview ↗</a>
            </div>
          </form>
          <div class="photo-card__actions">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="up"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--ghost" type="submit"<?= $i === 0 ? ' disabled' : '' ?>>↑ Earlier</button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="move"><input type="hidden" name="dir" value="down"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--ghost" type="submit"<?= $i === $last ? ' disabled' : '' ?>>↓ Later</button></form>
            <form method="post" data-confirm="Delete this offer?"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $pid ?>">
              <button class="btn btn--small btn--danger" type="submit">Delete</button></form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php admin_page_end();
