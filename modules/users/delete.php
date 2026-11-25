<?php
require_once '../../config/config.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
if ($id && $id != $_SESSION['user_id']) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    logActivity('delete', 'users', $id, 'Xóa người dùng');
    $_SESSION['success'] = 'Xóa thành công';
}
header('Location: index.php');
exit();
?>
