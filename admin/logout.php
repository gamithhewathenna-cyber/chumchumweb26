<?php
require __DIR__ . '/includes/admin.php';

if (is_post()) {
    verify_csrf();
    logout_session();
    flash('success', 'You have been logged out.');
}
redirect('login.php');
