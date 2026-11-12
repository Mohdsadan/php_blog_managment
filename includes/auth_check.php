
<?php

require_once __DIR__ . '/bootstrap.php';

if (empty($_SESSION['user_id'])) {
    set_flash('Please login first.', 'warning');
    redirect('/blog-management/auth/login.php');
}
