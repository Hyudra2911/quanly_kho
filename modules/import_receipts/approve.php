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

$stmt = $db->prepare("SELECT * FROM import_receipts WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    header('Location: index.php');
    exit();
}

$db->beginTransaction();
try {
    // Cập nhật trạng thái phiếu
    $stmt = $db->prepare("UPDATE import_receipts SET status = 'approved', approved_by = ? WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    // Lấy chi tiết phiếu
    $stmt = $db->prepare("SELECT * FROM import_receipt_details WHERE receipt_id = ?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    // Cập nhật tồn kho
    foreach ($details as $detail) {
        // Tạo hoặc cập nhật batch
        if (!empty($detail['batch_code'])) {
            $stmt = $db->prepare("SELECT id FROM batches WHERE item_id = ? AND batch_code = ?");
            $stmt->execute([$detail['item_id'], $detail['batch_code']]);
            $batch = $stmt->fetch();
            
            if ($batch) {
                $batch_id = $batch['id'];
                $stmt = $db->prepare("UPDATE batches SET current_quantity = current_quantity + ? WHERE id = ?");
                $stmt->execute([$detail['quantity'], $batch_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO batches (item_id, batch_code, import_date, expiry_date, initial_quantity, current_quantity, storage_location_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $detail['item_id'],
                    $detail['batch_code'],
                    $receipt['receipt_date'],
                    $detail['expiry_date'] ?: null,
                    $detail['quantity'],
                    $detail['quantity'],
                    $detail['storage_location_id'] ?: null
                ]);
                $batch_id = $db->lastInsertId();
            }
        } else {
            $batch_id = null;
        }
        
        // Cập nhật inventory
        $stmt = $db->prepare("INSERT INTO inventory (item_id, warehouse_id, storage_location_id, batch_id, quantity) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->execute([
            $detail['item_id'],
            $receipt['warehouse_id'],
            $detail['storage_location_id'] ?: null,
            $batch_id,
            $detail['quantity']
        ]);
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
            'Phiếu nhập kho đã được duyệt',
            "Phiếu nhập kho {$receipt['receipt_code']} đã được duyệt bởi admin"
        ]);
    }
    
    logActivity('approve', 'import_receipts', $id, "Duyệt phiếu nhập: {$receipt['receipt_code']}");
    $_SESSION['success'] = 'Duyệt phiếu nhập thành công';
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

header('Location: view.php?id=' . $id);
exit();
?>
