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
    
    // Lấy thông tin hàng hóa trước khi xóa
    $stmt = $db->prepare("SELECT code, name FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Kiểm tra xem có đang sử dụng trong tồn kho không
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM inventory WHERE item_id = ?");
        $stmt->execute([$id]);
        $inventory_count = $stmt->fetch()['count'];
        
        if ($inventory_count > 0) {
            $_SESSION['error'] = 'Không thể xóa hàng hóa đang có trong tồn kho';
        } else {
            $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
            if ($stmt->execute([$id])) {
                logActivity('delete', 'items', $id, "Xóa hàng hóa: {$item['code']} - {$item['name']}");
                $_SESSION['success'] = 'Xóa hàng hóa thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa';
            }
        }
    }
}

header('Location: index.php');
exit();
?>
