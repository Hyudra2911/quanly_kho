<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    logActivity('logout', 'users', $_SESSION['user_id'], 'Đăng xuất hệ thống');
}

$_SESSION = [];
session_destroy();
session_start();
session_regenerate_id(true);
header('Location: ' . BASE_URL . 'login.php');
exit();
?>
