<?php
require_once '../../config/config.php';
checkLogin();
checkRole(['admin', 'quanly_kho']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

// Chỉ cho phép từ chối phiếu đang ở trạng thái chờ duyệt
$stmt = $db->prepare("UPDATE export_receipts SET status = 'rejected', approved_by = ? WHERE id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id'], $id]);

$stmt = $db->prepare("SELECT receipt_code FROM export_receipts WHERE id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if ($receipt) {
    logActivity('reject', 'export_receipts', $id, "Từ chối phiếu xuất: {$receipt['receipt_code']}");
    $_SESSION['success'] = 'Từ chối phiếu xuất thành công';
} else {
    $_SESSION['error'] = 'Không tìm thấy phiếu xuất hoặc trạng thái không hợp lệ';
}

header('Location: view.php?id=' . $id);
exit();
?>

