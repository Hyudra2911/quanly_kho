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

$stmt = $db->prepare("SELECT name FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if ($customer) {
    $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity('delete', 'customers', $id, "Xóa khách hàng: " . $customer['name']);
}

header('Location: index.php?success=1');
exit();
