<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/contact.php';

$s = get_settings();
maintenance_gate($s); // no form submissions while the site is in maintenance mode

$result = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = handle_contact_submission($s);
    if ($result['ok']) {
        // Post/Redirect/Get: refreshing the thank-you page never sends the message twice
        header('Location: ' . base_url('contact.php?sent=1') . '#contact-form', true, 303);
        exit;
    }
}
$sent   = isset($_GET['sent']);
$values = $result['values'] ?? [];
$errors = $result['errors'] ?? [];
$token  = contact_form_token();

$pageTitle       = $s['contact_title'] . " | " . $s['site_name'] . ' — Sri Lankan Restaurant, Clayton';
$pageDescription = $s['contact_meta_description'];
$heroPreload     = $s['contact_banner'];

$address  = trim($s['address_line1'] . ' ' . $s['address_line2']);
$mapQuery = trim((string) $s['contact_map_query']);

/** One labelled form field with its error message. */
function contact_field(string $name, string $label, array $values, array $errors, array $o = []): string
{
    $id    = 'cf-' . str_replace('_', '-', $name);
    $err   = $errors[$name] ?? '';
    $attrs = ' id="' . $id . '" name="' . $name . '"' . ($err ? ' aria-invalid="true" aria-describedby="' . $id . '-err"' : '');
    foreach (['type', 'autocomplete', 'maxlength', 'inputmode'] as $a) {
        if (isset($o[$a]) && $a !== 'type') {
            $attrs .= ' ' . $a . '="' . e((string) $o[$a]) . '"';
        }
    }
    if (!empty($o['required'])) {
        $attrs .= ' required';
    }
    $value = $values[$name] ?? '';
    $html  = '<div class="cform__field' . ($err ? ' has-error' : '') . '"><label for="' . $id . '">' . e($label) . '</label>';
    if (($o['type'] ?? 'text') === 'textarea') {
        $html .= '<textarea' . $attrs . ' rows="7">' . e($value) . '</textarea>';
    } else {
        $html .= '<input type="' . e($o['type'] ?? 'text') . '"' . $attrs . ' value="' . e($value) . '">';
    }
    if ($err) {
        $html .= '<p class="cform__error" id="' . $id . '-err">' . e($err) . '</p>';
    }
    return $html . '</div>';
}

require __DIR__ . '/includes/partials/header.php';
?>
<main id="main" class="contact-page">

  <!-- ============ PAGE TITLE ============ -->
  <section class="page-hero" aria-labelledby="page-title">
    <h1 class="page-hero__title" id="page-title"><?= e($s['contact_title']) ?></h1>
    <?php if (trim($s['contact_intro']) !== ''): ?>
      <p class="page-hero__intro"><?= e($s['contact_intro']) ?></p>
    <?php endif; ?>
  </section>

  <?php if (trim($s['contact_banner']) !== ''): ?>
    <div class="page-banner" data-reveal="wipe">
      <img src="<?= e(img_url($s['contact_banner'])) ?>" alt="" width="2416" height="675" fetchpriority="high">
    </div>
  <?php endif; ?>

  <!-- ============ ADDRESS + FORM ============ -->
  <section class="contact" id="contact-form" aria-label="Contact details and message form">
    <div class="contact__info">
      <h2 class="contact__heading" data-reveal><?= e($s['contact_heading']) ?></h2>
      <?php if (trim($s['contact_text']) !== ''): ?>
        <p class="contact__text" data-reveal style="--d:1"><?= e($s['contact_text']) ?></p>
      <?php endif; ?>

      <ul class="contact__list">
        <?php if ($s['phone'] !== ''): ?>
          <li data-reveal style="--d:2"><span class="contact__icon" aria-hidden="true"><?= icon_phone() ?></span>
            <a href="<?= e(tel_link($s['phone'])) ?>"><span class="sr-only">Phone: </span><?= e($s['phone']) ?></a></li>
        <?php endif; ?>
        <?php if ($s['email'] !== ''): ?>
          <li data-reveal style="--d:3"><span class="contact__icon" aria-hidden="true"><?= icon_mail() ?></span>
            <a href="mailto:<?= e($s['email']) ?>"><span class="sr-only">Email: </span><?= e($s['email']) ?></a></li>
        <?php endif; ?>
        <?php if ($address !== ''): ?>
          <li data-reveal style="--d:4"><span class="contact__icon" aria-hidden="true"><?= icon_pin() ?></span>
            <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($mapQuery !== '' ? $mapQuery : $address)) ?>" target="_blank" rel="noopener">
              <span class="sr-only">Address (opens Google Maps): </span><?= e($address) ?></a></li>
        <?php endif; ?>
      </ul>

      <?php if ($mapQuery !== ''): ?>
        <div class="contact__map" data-reveal="zoom" style="--d:5">
          <iframe title="Map showing <?= e($s['site_name']) ?> at <?= e($address) ?>"
                  src="https://www.google.com/maps?q=<?= e(rawurlencode($mapQuery)) ?>&amp;z=15&amp;output=embed"
                  loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
      <?php endif; ?>
    </div>

    <div class="contact__divider" aria-hidden="true"></div>

    <div class="contact__form-wrap" data-reveal style="--d:1">
      <?php if ($sent): ?>
        <div class="contact__sent" role="status" tabindex="-1" id="contact-sent">
          <svg class="contact__sent-icon" viewBox="0 0 52 52" aria-hidden="true">
            <circle cx="26" cy="26" r="24" fill="none" stroke="currentColor" stroke-width="2.5"/>
            <path d="M15 27l7 7 15-16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h2>Message sent</h2>
          <p><?= e($s['contact_success']) ?></p>
          <a class="btn-pill" href="<?= e(base_url('contact.php')) ?>#contact-form">
            <span class="btn-pill__icon" aria-hidden="true"><?= icon_arrow() ?></span>
            <span class="btn-pill__text">Send another message</span>
          </a>
        </div>
      <?php else: ?>
        <form class="cform" method="post" action="<?= e(base_url('contact.php')) ?>#contact-form" novalidate data-contact-form>
          <?php if (!empty($errors['form'])): ?>
            <p class="cform__alert" role="alert"><?= e($errors['form']) ?></p>
          <?php elseif ($errors): ?>
            <p class="cform__alert" role="alert">Please check the highlighted fields.</p>
          <?php endif; ?>

          <div class="cform__row">
            <?= contact_field('first_name', 'First Name', $values, $errors, ['required' => true, 'autocomplete' => 'given-name', 'maxlength' => 60]) ?>
            <?= contact_field('last_name', 'Last Name', $values, $errors, ['autocomplete' => 'family-name', 'maxlength' => 60]) ?>
          </div>
          <?= contact_field('phone', 'Phone Number', $values, $errors, ['type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 30, 'inputmode' => 'tel']) ?>
          <?= contact_field('email', 'Email Address', $values, $errors, ['type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 150]) ?>
          <?= contact_field('message', 'Message', $values, $errors, ['type' => 'textarea', 'required' => true, 'maxlength' => 3000]) ?>

          <!-- Spam protection: a field people never see, plus a signed timestamp -->
          <div class="cform__trap" aria-hidden="true">
            <label for="cf-website">Leave this field empty</label>
            <input type="text" id="cf-website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <input type="hidden" name="ts" value="<?= e($token['ts']) ?>">
          <input type="hidden" name="sig" value="<?= e($token['sig']) ?>">

          <button class="cform__submit" type="submit"><span>Send Message</span></button>
        </form>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
