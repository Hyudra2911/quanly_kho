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

$stmt = $db->prepare("SELECT adjustment_code FROM inventory_adjustments WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$adjustment = $stmt->fetch();

if ($adjustment) {
    $stmt = $db->prepare("UPDATE inventory_adjustments SET status = 'rejected', approved_by = ? WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    logActivity('reject', 'inventory_adjustments', $id, "Từ chối phiếu điều chỉnh: " . $adjustment['adjustment_code']);
}

header('Location: index.php?success=1');
exit();
