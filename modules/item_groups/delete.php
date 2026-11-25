<?php
require_once '../../config/config.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM item_groups WHERE id = ?");
    $stmt->execute([$id]);
    logActivity('delete', 'item_groups', $id, 'Xóa nhóm hàng');
    $_SESSION['success'] = 'Xóa thành công';
}
header('Location: index.php');
exit();
?>
