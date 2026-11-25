<?php
require_once '../../config/config.php';
checkRole(['admin']);

$file = $_GET['file'] ?? '';
if (empty($file) || !preg_match('/^backup_\d{14}\.sql$/', $file)) {
    header('Location: index.php');
    exit();
}

$file_path = __DIR__ . '/../../backups/' . $file;

if (!file_exists($file_path)) {
    header('Location: index.php?error=file_not_found');
    exit();
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit();
