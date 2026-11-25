<?php
require_once '../../config/config.php';
checkLogin();
// Chỉ admin được duyệt phiếu xuất kho
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT * FROM export_receipts WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    header('Location: index.php');
    exit();
}

$db->beginTransaction();
try {
    $stmt = $db->prepare("UPDATE export_receipts SET status = 'approved', approved_by = ? WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    $stmt = $db->prepare("SELECT * FROM export_receipt_details WHERE receipt_id = ?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    foreach ($details as $detail) {
        // Kiểm tra tồn kho
        $stmt = $db->prepare("SELECT SUM(quantity) as total FROM inventory WHERE item_id = ? AND warehouse_id = ?");
        $stmt->execute([$detail['item_id'], $receipt['warehouse_id']]);
        $stock = $stmt->fetch()['total'] ?? 0;
        
        if ($stock < $detail['quantity']) {
            throw new Exception("Không đủ tồn kho cho hàng hóa này");
        }
        
        // Trừ tồn kho
        if ($detail['batch_id']) {
            $stmt = $db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND warehouse_id = ? AND batch_id = ? LIMIT 1");
            $stmt->execute([$detail['quantity'], $detail['item_id'], $receipt['warehouse_id'], $detail['batch_id']]);
            
            $stmt = $db->prepare("UPDATE batches SET current_quantity = current_quantity - ? WHERE id = ?");
            $stmt->execute([$detail['quantity'], $detail['batch_id']]);
        } else {
            $stmt = $db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND warehouse_id = ? ORDER BY id LIMIT 1");
            $stmt->execute([$detail['quantity'], $detail['item_id'], $receipt['warehouse_id']]);
        }
        
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
            'Phiếu xuất kho đã được duyệt',
            "Phiếu xuất kho {$receipt['receipt_code']} đã được duyệt bởi admin"
        ]);
    }
    
    logActivity('approve', 'export_receipts', $id, "Duyệt phiếu xuất: {$receipt['receipt_code']}");
    $_SESSION['success'] = 'Duyệt phiếu xuất thành công';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

header('Location: view.php?id=' . $id);
exit();
?>
