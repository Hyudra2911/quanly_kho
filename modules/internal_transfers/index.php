<?php
$page_title = 'Chuyển kho nội bộ';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$stmt = $db->query("SELECT it.*, w1.name as from_warehouse, w2.name as to_warehouse, u.full_name as creator_name 
    FROM internal_transfers it 
    JOIN warehouses w1 ON it.from_warehouse_id = w1.id 
    JOIN warehouses w2 ON it.to_warehouse_id = w2.id 
    JOIN users u ON it.created_by = u.id 
    ORDER BY it.created_at DESC");
$transfers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-arrow-repeat"></i> Chuyển kho nội bộ</h1>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Danh sách chuyển kho</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tạo phiếu chuyển
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Ngày</th>
                            <th>Từ kho</th>
                            <th>Đến kho</th>
                            <th>Người tạo</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transfers as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['transfer_code']); ?></td>
                            <td><?php echo formatDate($t['transfer_date']); ?></td>
                            <td><?php echo htmlspecialchars($t['from_warehouse']); ?></td>
                            <td><?php echo htmlspecialchars($t['to_warehouse']); ?></td>
                            <td><?php echo htmlspecialchars($t['creator_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $t['status'] == 'completed' ? 'success' : 
                                        ($t['status'] == 'cancelled' ? 'danger' : 
                                        ($t['status'] == 'in_transit' ? 'info' : 'warning')); 
                                ?>">
                                    <?php 
                                    echo $t['status'] == 'completed' ? 'Hoàn thành' : 
                                        ($t['status'] == 'cancelled' ? 'Hủy' : 
                                        ($t['status'] == 'in_transit' ? 'Đang vận chuyển' : 'Chờ duyệt')); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($t['status'] == 'pending' && $_SESSION['role'] == 'admin'): ?>
                                <form method="POST" action="approve.php" class="d-inline">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check"></i></button>
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
