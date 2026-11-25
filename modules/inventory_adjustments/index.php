<?php
$page_title = 'Điều chỉnh tồn kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$status = $_GET['status'] ?? '';

$query = "SELECT ia.*, w.name as warehouse_name, sl.name as location_name, u1.full_name as creator_name, u2.full_name as approver_name 
    FROM inventory_adjustments ia 
    JOIN warehouses w ON ia.warehouse_id = w.id 
    LEFT JOIN storage_locations sl ON ia.storage_location_id = sl.id 
    JOIN users u1 ON ia.created_by = u1.id 
    LEFT JOIN users u2 ON ia.approved_by = u2.id 
    WHERE 1=1";

$params = [];
if ($status) {
    $query .= " AND ia.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY ia.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$adjustments = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-clipboard-check"></i> Điều chỉnh tồn kho</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tạo phiếu điều chỉnh
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="btn-group" role="group">
                <a href="index.php" class="btn btn-sm <?php echo $status == '' ? 'btn-primary' : 'btn-outline-primary'; ?>">Tất cả</a>
                <a href="index.php?status=pending" class="btn btn-sm <?php echo $status == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">Chờ duyệt</a>
                <a href="index.php?status=approved" class="btn btn-sm <?php echo $status == 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">Đã duyệt</a>
                <a href="index.php?status=rejected" class="btn btn-sm <?php echo $status == 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">Từ chối</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Ngày</th>
                            <th>Kho</th>
                            <th>Vị trí</th>
                            <th class="text-center">Loại</th>
                            <th>Lý do</th>
                            <th>Người tạo</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($adjustments)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($adjustments as $adj): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($adj['adjustment_code']); ?></td>
                            <td><?php echo formatDate($adj['adjustment_date']); ?></td>
                            <td><?php echo htmlspecialchars($adj['warehouse_name']); ?></td>
                            <td><?php echo htmlspecialchars($adj['location_name'] ?? 'N/A'); ?></td>
                            <td class="text-center">
                                <?php $type = $adj['adjustment_type'] ?? null; ?>
                                <span class="badge bg-<?php 
                                    echo $type === 'increase' ? 'success' : 
                                        ($type === 'decrease' ? 'danger' : 'info'); 
                                ?>">
                                    <?php 
                                    echo $type === 'increase' ? 'Tăng' : 
                                        ($type === 'decrease' ? 'Giảm' : 'Kiểm kê'); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($adj['reason'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($adj['creator_name']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?php 
                                    echo $adj['status'] == 'approved' ? 'success' : 
                                        ($adj['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php 
                                    echo $adj['status'] == 'approved' ? 'Đã duyệt' : 
                                        ($adj['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
                                    ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="view.php?id=<?php echo $adj['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($adj['status'] == 'pending' && in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                                <form method="POST" action="approve.php" class="d-inline">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $adj['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check"></i></button>
                                </form>
                                <form method="POST" action="reject.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối phiếu điều chỉnh này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $adj['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
