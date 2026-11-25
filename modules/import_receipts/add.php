<?php
$page_title = 'Tạo phiếu nhập kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receipt_code = 'PN' . date('YmdHis');
    $receipt_date = $_POST['receipt_date'];
    $warehouse_id = $_POST['warehouse_id'];
    $supplier = trim($_POST['supplier'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? [];
    
    if (empty($receipt_date) || empty($warehouse_id) || empty($items)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO import_receipts (receipt_code, receipt_date, warehouse_id, supplier, created_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$receipt_code, $receipt_date, $warehouse_id, $supplier, $_SESSION['user_id'], $notes]);
            $receipt_id = $db->lastInsertId();
            
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $quantity = intval($item['quantity']);
                $batch_code = trim($item['batch_code'] ?? '');
                $unit_price = floatval($item['unit_price'] ?? 0);
                $location_id = $item['location_id'] ?? null;
                $expiry_date = $item['expiry_date'] ?? null;
                
                $stmt = $db->prepare("INSERT INTO import_receipt_details (receipt_id, item_id, batch_code, quantity, unit_price, storage_location_id, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$receipt_id, $item_id, $batch_code, $quantity, $unit_price, $location_id ?: null, $expiry_date ?: null]);
            }
            
            $db->commit();
            
            // Tạo thông báo cho admin
            $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin'");
            $stmt->execute();
            $admins = $stmt->fetchAll();
            foreach ($admins as $admin) {
                $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([
                    $admin['id'],
                    'approval_pending',
                    'Phiếu nhập kho chờ duyệt',
                    "Phiếu nhập kho $receipt_code từ $supplier cần được duyệt"
                ]);
            }
            
            logActivity('create', 'import_receipts', $receipt_id, "Tạo phiếu nhập: $receipt_code");
            header('Location: index.php?success=1');
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

$stmt = $db->query("SELECT * FROM warehouses WHERE status = 1 ORDER BY name");
$warehouses = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM items WHERE status = 1 ORDER BY name");
$items = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM storage_locations WHERE status = 1 ORDER BY code");
$locations = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <form method="POST" id="importForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="receipt_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kho <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse_id" id="warehouse_id" required>
                                <option value="">-- Chọn kho --</option>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nhà cung cấp</label>
                            <input type="text" class="form-control" name="supplier">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
                
                <hr>
                <h5>Chi tiết hàng hóa</h5>
                <div id="itemsContainer">
                    <div class="item-row mb-3 border p-3">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Hàng hóa <span class="text-danger">*</span></label>
                                <select class="form-select item-select" name="items[0][item_id]" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>" data-unit="<?php echo htmlspecialchars($item['unit']); ?>">
                                        <?php echo htmlspecialchars($item['code'] . ' - ' . $item['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="items[0][quantity]" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mã lô</label>
                                <input type="text" class="form-control" name="items[0][batch_code]">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Đơn giá</label>
                                <input type="number" class="form-control" name="items[0][unit_price]" min="0" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vị trí</label>
                                <select class="form-select" name="items[0][location_id]">
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['code']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger w-100 remove-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Hạn sử dụng</label>
                                <input type="date" class="form-control" name="items[0][expiry_date]">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-secondary" id="addItem">
                    <i class="bi bi-plus"></i> Thêm hàng hóa
                </button>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Lưu phiếu nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;
document.getElementById('addItem').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const newRow = container.firstElementChild.cloneNode(true);
    
    // Update names
    newRow.querySelectorAll('input, select').forEach(el => {
        if (el.name) {
            el.name = el.name.replace(/\[0\]/, '[' + itemIndex + ']');
            el.value = '';
        }
    });
    
    container.appendChild(newRow);
    itemIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        const row = e.target.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
        } else {
            alert('Phải có ít nhất một hàng hóa');
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
