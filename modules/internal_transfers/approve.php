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

$stmt = $db->prepare("SELECT * FROM internal_transfers WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$transfer = $stmt->fetch();

if (!$transfer) {
    header('Location: index.php');
    exit();
}

$db->beginTransaction();
try {
    // Cập nhật trạng thái
    $stmt = $db->prepare("UPDATE internal_transfers SET status = 'in_transit', approved_by = ? WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    // Lấy chi tiết chuyển kho
    $stmt = $db->prepare("SELECT * FROM internal_transfer_details WHERE transfer_id = ?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    // Chuyển tồn kho từ kho này sang kho khác
    foreach ($details as $detail) {
        // Trừ tồn kho ở kho gửi
        if ($detail['batch_id']) {
            $stmt = $db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND warehouse_id = ? AND batch_id = ? LIMIT 1");
            $stmt->execute([$detail['quantity'], $detail['item_id'], $transfer['from_warehouse_id'], $detail['batch_id']]);
        } else {
            $stmt = $db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND warehouse_id = ? ORDER BY id LIMIT 1");
            $stmt->execute([$detail['quantity'], $detail['item_id'], $transfer['from_warehouse_id']]);
        }
        
        // Thêm tồn kho ở kho nhận
        $stmt = $db->prepare("INSERT INTO inventory (item_id, warehouse_id, batch_id, quantity) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->execute([$detail['item_id'], $transfer['to_warehouse_id'], $detail['batch_id'], $detail['quantity']]);
        
        // Xóa nếu tồn kho = 0
        $stmt = $db->prepare("DELETE FROM inventory WHERE quantity <= 0");
        $stmt->execute();
    }
    
    $db->commit();
    
    // Tạo thông báo cho quản lý kho
    $stmt = $db->prepare("SELECT id FROM users WHERE role = 'quanly_kho'");
    $stmt->execute();
    $managers = $stmt->fetchAll();
    foreach ($managers as $manager) {
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([
            $manager['id'],
            'approval_pending',
            'Phiếu chuyển kho đã được duyệt',
            "Phiếu chuyển kho {$transfer['transfer_code']} đã được duyệt bởi admin"
        ]);
    }
    
    logActivity('approve', 'internal_transfers', $id, "Duyệt phiếu chuyển: {$transfer['transfer_code']}");
    $_SESSION['success'] = 'Duyệt phiếu chuyển thành công';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

header('Location: view.php?id=' . $id);
exit();
?>
