<?php
require_once '../../config/config.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$file = $_POST['file'] ?? '';
if (empty($file) || !preg_match('/^backup_\d{14}\.sql$/', $file)) {
    header('Location: index.php');
    exit();
}

$file_path = __DIR__ . '/../../backups/' . $file;

if (file_exists($file_path)) {
    unlink($file_path);
    logActivity('delete', 'backup', null, "Xóa backup: $file");
}

header('Location: index.php?success=1');
exit();
