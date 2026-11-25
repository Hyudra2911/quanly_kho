<?php
$page_title = 'Tạo phiếu xuất kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receipt_code = 'PX' . date('YmdHis');
    $receipt_date = $_POST['receipt_date'];
    $warehouse_id = $_POST['warehouse_id'];
    $customer = trim($_POST['customer'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? [];
    
    if (empty($receipt_date) || empty($warehouse_id) || empty($items)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO export_receipts (receipt_code, receipt_date, warehouse_id, customer, created_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$receipt_code, $receipt_date, $warehouse_id, $customer, $_SESSION['user_id'], $notes]);
            $receipt_id = $db->lastInsertId();
            
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $batch_id = $item['batch_id'] ?? null;
                $quantity = intval($item['quantity']);
                $unit_price = floatval($item['unit_price'] ?? 0);
                
                $stmt = $db->prepare("INSERT INTO export_receipt_details (receipt_id, item_id, batch_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$receipt_id, $item_id, $batch_id, $quantity, $unit_price]);
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
                    'Phiếu xuất kho chờ duyệt',
                    "Phiếu xuất kho $receipt_code cho $customer cần được duyệt"
                ]);
            }
            
            logActivity('create', 'export_receipts', $receipt_id, "Tạo phiếu xuất: $receipt_code");
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

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <form method="POST" id="exportForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ngày xuất <span class="text-danger">*</span></label>
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
                
                <div class="mb-3">
                    <label class="form-label">Khách hàng</label>
                    <input type="text" class="form-control" name="customer">
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
                            <div class="col-md-4">
                                <label class="form-label">Hàng hóa <span class="text-danger">*</span></label>
                                <select class="form-select item-select" name="items[0][item_id]" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>">
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
                                <label class="form-label">Đơn giá</label>
                                <input type="number" class="form-control" name="items[0][unit_price]" min="0" step="0.01">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger w-100 remove-item">
                                    <i class="bi bi-trash"></i>
                                </button>
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
                    <button type="submit" class="btn btn-primary">Lưu phiếu xuất</button>
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
