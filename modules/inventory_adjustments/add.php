<?php
$page_title = 'Tạo phiếu điều chỉnh tồn kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adjustment_code = 'DC' . date('YmdHis');
    $adjustment_date = $_POST['adjustment_date'];
    $warehouse_id = $_POST['warehouse_id'];
    $storage_location_id = $_POST['storage_location_id'] ?? null;
    $adjustment_type = $_POST['adjustment_type'];
    $reason = trim($_POST['reason'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? [];
    
    if (empty($adjustment_date) || empty($warehouse_id) || empty($items)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO inventory_adjustments (adjustment_code, adjustment_date, warehouse_id, storage_location_id, adjustment_type, reason, created_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$adjustment_code, $adjustment_date, $warehouse_id, $storage_location_id, $adjustment_type, $reason, $_SESSION['user_id'], $notes]);
            $adjustment_id = $db->lastInsertId();
            
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $batch_id = $item['batch_id'] ?? null;
                $quantity_before = intval($item['quantity_before']);
                $quantity_after = intval($item['quantity_after']);
                $quantity_difference = $quantity_after - $quantity_before;
                
                $stmt = $db->prepare("INSERT INTO inventory_adjustment_details (adjustment_id, item_id, batch_id, quantity_before, quantity_after, quantity_difference) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$adjustment_id, $item_id, $batch_id, $quantity_before, $quantity_after, $quantity_difference]);
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
                    'Phiếu điều chỉnh tồn kho chờ duyệt',
                    "Phiếu điều chỉnh $adjustment_code với lý do '$reason' cần được duyệt"
                ]);
            }
            
            logActivity('create', 'inventory_adjustments', $adjustment_id, "Tạo phiếu điều chỉnh: $adjustment_code");
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
        <div class="card-header">
            <h5 class="mb-0">Tạo phiếu điều chỉnh tồn kho</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="adjustmentForm">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Ngày điều chỉnh <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="adjustment_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kho <span class="text-danger">*</span></label>
                        <select class="form-select" name="warehouse_id" id="warehouse_id" required>
                            <option value="">Chọn kho</option>
                            <?php foreach ($warehouses as $w): ?>
                            <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vị trí lưu trữ</label>
                        <select class="form-select" name="storage_location_id" id="storage_location_id">
                            <option value="">Tất cả vị trí</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Loại điều chỉnh <span class="text-danger">*</span></label>
                        <select class="form-select" name="adjustment_type" required>
                            <option value="increase">Tăng tồn kho</option>
                            <option value="decrease">Giảm tồn kho</option>
                            <option value="count">Kiểm kê</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Lý do</label>
                        <input type="text" class="form-control" name="reason" placeholder="Ví dụ: Kiểm kê định kỳ, Hao hụt, Thừa...">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Chi tiết hàng hóa</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                            <i class="bi bi-plus"></i> Thêm hàng
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Hàng hóa</th>
                                        <th>Lô hàng</th>
                                        <th>Số lượng hiện tại</th>
                                        <th>Số lượng sau điều chỉnh</th>
                                        <th>Chênh lệch</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Tạo phiếu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let itemIndex = 0;
const items = <?php echo json_encode($items); ?>;

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select class="form-select item-select" name="items[${itemIndex}][item_id]" required onchange="loadBatches(this, ${itemIndex})">
                <option value="">Chọn hàng hóa</option>
                ${items.map(item => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('')}
            </select>
        </td>
        <td>
            <select class="form-select batch-select" name="items[${itemIndex}][batch_id]" id="batch_${itemIndex}">
                <option value="">Không có lô</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control qty-before" name="items[${itemIndex}][quantity_before]" required min="0" onchange="calculateDifference(${itemIndex})">
        </td>
        <td>
            <input type="number" class="form-control qty-after" name="items[${itemIndex}][quantity_after]" required min="0" onchange="calculateDifference(${itemIndex})">
        </td>
        <td>
            <input type="number" class="form-control qty-diff" readonly value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    itemIndex++;
}

function removeRow(btn) {
    btn.closest('tr').remove();
}

function calculateDifference(index) {
    const rows = document.querySelectorAll('#itemsBody tr');
    if (index >= rows.length) return;
    const row = rows[index];
    const before = parseFloat(row.querySelector('.qty-before').value) || 0;
    const after = parseFloat(row.querySelector('.qty-after').value) || 0;
    const diffInput = row.querySelector('.qty-diff');
    if (diffInput) {
        diffInput.value = after - before;
    }
}

function loadBatches(select, index) {
    const itemId = select.value;
    const batchSelect = document.getElementById(`batch_${index}`);
    batchSelect.innerHTML = '<option value="">Không có lô</option>';
    
    if (itemId) {
        // Load batches via AJAX or populate from available data
        fetch(`../../api/get_batches.php?item_id=${itemId}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = batch.batch_code;
                    batchSelect.appendChild(option);
                });
            })
            .catch(() => {
                // Fallback: just show empty
            });
    }
}

// Load storage locations when warehouse changes
document.getElementById('warehouse_id').addEventListener('change', function() {
    const warehouseId = this.value;
    const locationSelect = document.getElementById('storage_location_id');
    locationSelect.innerHTML = '<option value="">Tất cả vị trí</option>';
    
    if (warehouseId) {
        fetch(`../../api/get_locations.php?warehouse_id=${warehouseId}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(loc => {
                    const option = document.createElement('option');
                    option.value = loc.id;
                    option.textContent = loc.name;
                    locationSelect.appendChild(option);
                });
            });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
