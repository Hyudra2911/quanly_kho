<?php
$page_title = 'Tạo phiếu chuyển kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transfer_code = 'CK' . date('YmdHis');
    $transfer_date = $_POST['transfer_date'];
    $from_warehouse_id = $_POST['from_warehouse_id'];
    $to_warehouse_id = $_POST['to_warehouse_id'];
    $items = $_POST['items'] ?? [];
    
    if (empty($transfer_date) || empty($from_warehouse_id) || empty($to_warehouse_id) || empty($items)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO internal_transfers (transfer_code, transfer_date, from_warehouse_id, to_warehouse_id, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$transfer_code, $transfer_date, $from_warehouse_id, $to_warehouse_id, $_SESSION['user_id']]);
            $transfer_id = $db->lastInsertId();
            
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $batch_id = $item['batch_id'] ?? null;
                $quantity = intval($item['quantity']);
                
                // Kiểm tra tồn kho
                $stmt = $db->prepare("SELECT SUM(quantity) as total FROM inventory WHERE item_id = ? AND warehouse_id = ?");
                $stmt->execute([$item_id, $from_warehouse_id]);
                $stock = $stmt->fetch()['total'] ?? 0;
                
                if ($stock < $quantity) {
                    throw new Exception("Không đủ tồn kho cho hàng hóa này");
                }
                
                $stmt = $db->prepare("INSERT INTO internal_transfer_details (transfer_id, item_id, batch_id, quantity) VALUES (?, ?, ?, ?)");
                $stmt->execute([$transfer_id, $item_id, $batch_id, $quantity]);
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
                    'Phiếu chuyển kho chờ duyệt',
                    "Phiếu chuyển kho $transfer_code cần được duyệt"
                ]);
            }
            
            logActivity('create', 'internal_transfers', $transfer_id, "Tạo phiếu chuyển: $transfer_code");
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
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Ngày chuyển <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="transfer_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Từ kho <span class="text-danger">*</span></label>
                            <select class="form-select" name="from_warehouse_id" required>
                                <option value="">-- Chọn kho --</option>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Đến kho <span class="text-danger">*</span></label>
                            <select class="form-select" name="to_warehouse_id" required>
                                <option value="">-- Chọn kho --</option>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <hr>
                <h5>Chi tiết hàng hóa</h5>
                <div id="itemsContainer">
                    <div class="item-row mb-3 border p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Hàng hóa <span class="text-danger">*</span></label>
                                <select class="form-select" name="items[0][item_id]" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['code'] . ' - ' . $item['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="items[0][quantity]" min="1" required>
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
                    <button type="submit" class="btn btn-primary">Lưu phiếu chuyển</button>
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
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
