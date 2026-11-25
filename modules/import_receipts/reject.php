<?php
require_once '../../config/config.php';
checkLogin();
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
	$_SESSION['error'] = 'Yêu cầu không hợp lệ';
	header('Location: index.php');
	exit();
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("UPDATE import_receipts SET status = 'rejected', approved_by = ? WHERE id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id'], $id]);

$stmt = $db->prepare("SELECT receipt_code FROM import_receipts WHERE id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

logActivity('reject', 'import_receipts', $id, "Từ chối phiếu nhập: {$receipt['receipt_code']}");
$_SESSION['success'] = 'Từ chối phiếu nhập thành công';

header('Location: view.php?id=' . $id);
exit();
?>
