<?php
require_once '../../config/config.php';
// Chỉ admin được duyệt phiếu điều chỉnh tồn kho
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Yêu cầu không hợp lệ';
    header('Location: index.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT * FROM inventory_adjustments WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$adjustment = $stmt->fetch();

if (!$adjustment) {
    header('Location: index.php');
    exit();
}

$db->beginTransaction();
try {
    // Cập nhật trạng thái
    $stmt = $db->prepare("UPDATE inventory_adjustments SET status = 'approved', approved_by = ? WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    // Lấy chi tiết
    $stmt = $db->prepare("SELECT * FROM inventory_adjustment_details WHERE adjustment_id = ?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    // Cập nhật tồn kho
    foreach ($details as $detail) {
        // Kiểm tra tồn kho hiện tại
        $stmt = $db->prepare("SELECT id, quantity FROM inventory WHERE item_id = ? AND warehouse_id = ? AND storage_location_id <=> ? AND batch_id <=> ?");
        $stmt->execute([$detail['item_id'], $adjustment['warehouse_id'], $adjustment['storage_location_id'], $detail['batch_id']]);
        $inventory = $stmt->fetch();
        
        if ($inventory) {
            // Cập nhật số lượng
            $new_quantity = $detail['quantity_after'];
            $stmt = $db->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $inventory['id']]);
        } else {
            // Tạo mới
            $stmt = $db->prepare("INSERT INTO inventory (item_id, warehouse_id, storage_location_id, batch_id, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$detail['item_id'], $adjustment['warehouse_id'], $adjustment['storage_location_id'], $detail['batch_id'], $detail['quantity_after']]);
        }
        
        // Cập nhật batch nếu có
        if ($detail['batch_id']) {
            $stmt = $db->prepare("UPDATE batches SET current_quantity = ? WHERE id = ?");
            $stmt->execute([$detail['quantity_after'], $detail['batch_id']]);
        }
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
            'Phiếu điều chỉnh tồn kho đã được duyệt',
            "Phiếu điều chỉnh {$adjustment['adjustment_code']} đã được duyệt bởi admin"
        ]);
    }
    
    logActivity('approve', 'inventory_adjustments', $id, "Duyệt phiếu điều chỉnh: " . $adjustment['adjustment_code']);
    header('Location: index.php?success=1');
    exit();
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
    header('Location: view.php?id=' . $id);
    exit();
}
