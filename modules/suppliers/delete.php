<?php
require_once '../../config/config.php';
checkRole(['admin', 'quanly_kho']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT name FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if ($supplier) {
    $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity('delete', 'suppliers', $id, "Xóa nhà cung cấp: " . $supplier['name']);
}

header('Location: index.php?success=1');
exit();
