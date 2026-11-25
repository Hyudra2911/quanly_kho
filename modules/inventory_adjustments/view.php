<?php
$page_title = 'Chi tiết phiếu điều chỉnh';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT ia.*, w.name as warehouse_name, sl.name as location_name, u1.full_name as creator_name, u2.full_name as approver_name 
    FROM inventory_adjustments ia 
    JOIN warehouses w ON ia.warehouse_id = w.id 
    LEFT JOIN storage_locations sl ON ia.storage_location_id = sl.id 
    JOIN users u1 ON ia.created_by = u1.id 
    LEFT JOIN users u2 ON ia.approved_by = u2.id 
    WHERE ia.id = ?");
$stmt->execute([$id]);
$adjustment = $stmt->fetch();

if (!$adjustment) {
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT iad.*, i.code as item_code, i.name as item_name, i.unit, b.batch_code 
    FROM inventory_adjustment_details iad 
    JOIN items i ON iad.item_id = i.id 
    LEFT JOIN batches b ON iad.batch_id = b.id 
    WHERE iad.adjustment_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Phiếu điều chỉnh: <?php echo htmlspecialchars($adjustment['adjustment_code']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Mã phiếu:</th><td><?php echo htmlspecialchars($adjustment['adjustment_code']); ?></td></tr>
                        <tr><th>Ngày:</th><td><?php echo formatDate($adjustment['adjustment_date']); ?></td></tr>
                        <tr><th>Kho:</th><td><?php echo htmlspecialchars($adjustment['warehouse_name']); ?></td></tr>
                        <tr><th>Vị trí:</th><td><?php echo htmlspecialchars($adjustment['location_name'] ?? 'N/A'); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Loại:</th><td>
                            <span class="badge bg-<?php 
                                echo $adjustment['adjustment_type'] == 'increase' ? 'success' : 
                                    ($adjustment['adjustment_type'] == 'decrease' ? 'danger' : 'info'); 
                            ?>">
                                <?php 
                                echo $adjustment['adjustment_type'] == 'increase' ? 'Tăng' : 
                                    ($adjustment['adjustment_type'] == 'decrease' ? 'Giảm' : 'Kiểm kê'); 
                                ?>
                            </span>
                        </td></tr>
                        <tr><th>Lý do:</th><td><?php echo htmlspecialchars($adjustment['reason'] ?? ''); ?></td></tr>
                        <tr><th>Người tạo:</th><td><?php echo htmlspecialchars($adjustment['creator_name']); ?></td></tr>
                        <tr><th>Người duyệt:</th><td><?php echo htmlspecialchars($adjustment['approver_name'] ?? 'Chưa duyệt'); ?></td></tr>
                        <tr><th>Trạng thái:</th><td>
                            <span class="badge bg-<?php 
                                echo $adjustment['status'] == 'approved' ? 'success' : 
                                    ($adjustment['status'] == 'rejected' ? 'danger' : 'warning'); 
                            ?>">
                                <?php 
                                echo $adjustment['status'] == 'approved' ? 'Đã duyệt' : 
                                    ($adjustment['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
                                ?>
                            </span>
                        </td></tr>
                    </table>
                </div>
            </div>
            
            <h5>Chi tiết hàng hóa</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã hàng</th>
                            <th>Tên hàng</th>
                            <th>Lô hàng</th>
                            <th>Số lượng trước</th>
                            <th>Số lượng sau</th>
                            <th>Chênh lệch</th>
                            <th>Đơn vị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                        <?php
                            // Hỗ trợ cả hai kiểu schema: (current/adjusted/difference) và (quantity_before/after/difference)
                            $before = $d['quantity_before'] ?? ($d['current_quantity'] ?? 0);
                            $after = $d['quantity_after'] ?? ($d['adjusted_quantity'] ?? 0);
                            $diff = $d['quantity_difference'] ?? ($d['difference'] ?? ($after - $before));
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['item_code']); ?></td>
                            <td><?php echo htmlspecialchars($d['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($d['batch_code'] ?? 'N/A'); ?></td>
                            <td><?php echo formatNumber($before); ?></td>
                            <td><?php echo formatNumber($after); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $diff >= 0 ? 'success' : 'danger'; ?>">
                                    <?php echo ($diff >= 0 ? '+' : '') . formatNumber($diff); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($d['unit']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Quay lại</a>
                <?php if ($adjustment['status'] == 'pending' && in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                <form method="POST" action="approve.php" class="d-inline">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-success">Duyệt</button>
                </form>
                <form method="POST" action="reject.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối phiếu điều chỉnh này?');">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
