<?php
/** Contact Page → Messages: everything sent through the website contact form. */
require __DIR__ . '/includes/admin.php';
require_once APP_ROOT . '/includes/contact.php';
require_login();
$pdo = admin_db();
ensure_contact_table($pdo);

const MESSAGES_PER_PAGE = 20;

$filter = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';
$page   = max(1, (int) ($_GET['page'] ?? 1));
$viewId = (int) ($_GET['view'] ?? 0);

function find_message(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function message_name(array $m): string
{
    return trim($m['first_name'] . ' ' . $m['last_name']);
}

function message_when(string $ts): string
{
    $t = strtotime($ts);
    if (date('Y-m-d', $t) === date('Y-m-d')) {
        return 'Today, ' . date('g:ia', $t);
    }
    if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) {
        return 'Yesterday, ' . date('g:ia', $t);
    }
    return date('j M Y, g:ia', $t);
}

/* ---------- Actions ---------- */
if (is_post()) {
    verify_csrf();
    $op   = post('op');
    $id   = (int) post('id');
    $back = 'messages.php' . ($filter === 'unread' ? '?filter=unread' : '');

    if ($op === 'delete') {
        $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
        flash('success', 'Message deleted.');
        redirect($back);
    }
    if ($op === 'unread') {
        $pdo->prepare('UPDATE contact_messages SET is_read = 0 WHERE id = ?')->execute([$id]);
        flash('success', 'Marked as unread.');
        redirect($back);
    }
    if ($op === 'read') {
        $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        redirect($back);
    }
    if ($op === 'read_all') {
        $n = $pdo->exec('UPDATE contact_messages SET is_read = 1 WHERE is_read = 0');
        flash('success', $n ? $n . ' message' . ($n === 1 ? '' : 's') . ' marked as read.' : 'Nothing to mark.');
        redirect($back);
    }
}

/* ---------- Single message ---------- */
if ($viewId) {
    $m = find_message($pdo, $viewId);
    if (!$m) {
        flash('error', 'That message was not found — it may have been deleted.');
        redirect('messages.php');
    }
    if (!$m['is_read']) {
        $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
    }
    $s       = get_settings();
    $name    = message_name($m);
    $replyTo = 'mailto:' . rawurlencode($m['email'])
        . '?subject=' . rawurlencode('Re: Your message to ' . $s['site_name'])
        . '&body=' . rawurlencode("Hi {$m['first_name']},\n\n\n\n---\nOn " . date('j M Y', strtotime($m['created_at'])) . " you wrote:\n> "
            . str_replace("\n", "\n> ", $m['message']));

    admin_page_start('Contact Page', 'messages');
    contact_tabs('messages');
    ?>
    <p class="crumbs"><a href="messages.php">Messages</a> › <?= e($name) ?></p>
    <article class="panel message">
      <header class="message__head">
        <div>
          <h2 class="message__name"><?= e($name) ?></h2>
          <p class="muted small"><?= e(message_when($m['created_at'])) ?></p>
        </div>
        <div class="message__actions">
          <a class="btn btn--primary btn--small" href="<?= e($replyTo) ?>">Reply by email</a>
          <?php if ($m['phone'] !== ''): ?>
            <a class="btn btn--small" href="<?= e(tel_link($m['phone'])) ?>">Call</a>
          <?php endif; ?>
          <form method="post">
            <?= csrf_field() ?><input type="hidden" name="op" value="unread"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="btn btn--small btn--ghost" type="submit">Mark unread</button>
          </form>
          <form method="post" data-confirm="Delete this message from <?= e($name) ?>? This can't be undone.">
            <?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="btn btn--small btn--danger" type="submit">Delete</button>
          </form>
        </div>
      </header>
      <dl class="message__meta">
        <div><dt>Email</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd></div>
        <div><dt>Phone</dt><dd><?= $m['phone'] !== '' ? '<a href="' . e(tel_link($m['phone'])) . '">' . e($m['phone']) . '</a>' : '<span class="muted">—</span>' ?></dd></div>
      </dl>
      <div class="message__body"><?= nl2br(e($m['message'])) ?></div>
    </article>
    <?php
    admin_page_end();
    exit;
}

/* ---------- List ---------- */
$where = $filter === 'unread' ? 'WHERE is_read = 0' : '';
$total = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages $where")->fetchColumn();
$pages = max(1, (int) ceil($total / MESSAGES_PER_PAGE));
$page  = min($page, $pages);
$rows  = $pdo->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC, id DESC LIMIT "
    . MESSAGES_PER_PAGE . ' OFFSET ' . (($page - 1) * MESSAGES_PER_PAGE))->fetchAll();
$unread = unread_message_count();
$all    = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();

admin_page_start('Contact Page', 'messages');
contact_tabs('messages');
?>
  <div class="toolbar">
    <nav class="filter-links" aria-label="Filter messages">
      <a href="messages.php"<?= $filter === 'all' ? ' aria-current="page"' : '' ?>>All <span class="badge"><?= $all ?></span></a>
      <a href="messages.php?filter=unread"<?= $filter === 'unread' ? ' aria-current="page"' : '' ?>>Unread <span class="badge<?= $unread ? ' badge--warn' : '' ?>"><?= $unread ?></span></a>
    </nav>
    <?php if ($unread): ?>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="op" value="read_all">
        <button class="btn btn--small btn--ghost" type="submit">Mark all as read</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (!$rows): ?>
    <div class="panel empty">
      <?= $filter === 'unread' ? "You're all caught up — no unread messages." : 'No messages yet. When someone uses the form on the Contact page, it will appear here.' ?>
    </div>
  <?php else: ?>
    <div class="inbox">
      <?php foreach ($rows as $m): ?>
        <a class="inbox__item<?= $m['is_read'] ? '' : ' is-unread' ?>" href="messages.php?view=<?= (int) $m['id'] ?>">
          <span class="inbox__dot" aria-hidden="true"></span>
          <span class="inbox__from">
            <strong><?= e(message_name($m)) ?></strong>
            <span class="muted small"><?= e($m['email']) ?><?= $m['phone'] !== '' ? ' · ' . e($m['phone']) : '' ?></span>
          </span>
          <span class="inbox__snippet"><?= e(mb_strimwidth(preg_replace('~\s+~', ' ', $m['message']), 0, 140, '…')) ?></span>
          <span class="inbox__when muted small"><?= e(message_when($m['created_at'])) ?><?= $m['is_read'] ? '' : '<span class="sr-only"> (unread)</span>' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Pages">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <a href="messages.php?page=<?= $p ?><?= $filter === 'unread' ? '&amp;filter=unread' : '' ?>"<?= $p === $page ? ' aria-current="page"' : '' ?>><?= $p ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
<?php admin_page_end();
