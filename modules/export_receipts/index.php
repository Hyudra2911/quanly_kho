<?php
$page_title = 'Phiếu xuất kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$status = $_GET['status'] ?? '';

$query = "SELECT er.*, w.name as warehouse_name, u1.full_name as creator_name, u2.full_name as approver_name 
    FROM export_receipts er 
    JOIN warehouses w ON er.warehouse_id = w.id 
    JOIN users u1 ON er.created_by = u1.id 
    LEFT JOIN users u2 ON er.approved_by = u2.id 
    WHERE 1=1";

$params = [];
if ($status) {
    $query .= " AND er.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY er.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$receipts = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-box-arrow-up"></i> Phiếu xuất kho</h1>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Danh sách phiếu xuất</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tạo phiếu xuất
            </a>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="index.php" class="btn btn-sm <?php echo !$status ? 'btn-primary' : 'btn-outline-primary'; ?>">Tất cả</a>
                <a href="index.php?status=pending" class="btn btn-sm <?php echo $status == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">Chờ duyệt</a>
                <a href="index.php?status=approved" class="btn btn-sm <?php echo $status == 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">Đã duyệt</a>
                <a href="index.php?status=rejected" class="btn btn-sm <?php echo $status == 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">Từ chối</a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Ngày</th>
                            <th>Kho</th>
                            <th>Khách hàng</th>
                            <th>Người tạo</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['receipt_code']); ?></td>
                            <td><?php echo formatDate($r['receipt_date']); ?></td>
                            <td><?php echo htmlspecialchars($r['warehouse_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['customer'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['creator_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $r['status'] == 'approved' ? 'success' : 
                                        ($r['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php 
                                    echo $r['status'] == 'approved' ? 'Đã duyệt' : 
                                        ($r['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($r['status'] == 'pending' && in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                                <form method="POST" action="approve.php" class="d-inline">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check"></i></button>
                                </form>
                                <form method="POST" action="reject.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối phiếu này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
