<?php
$page_title = 'Chi tiết phiếu xuất';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT er.*, w.name as warehouse_name, u1.full_name as creator_name, u2.full_name as approver_name 
    FROM export_receipts er 
    JOIN warehouses w ON er.warehouse_id = w.id 
    JOIN users u1 ON er.created_by = u1.id 
    LEFT JOIN users u2 ON er.approved_by = u2.id 
    WHERE er.id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT erd.*, i.code as item_code, i.name as item_name, i.unit, b.batch_code 
    FROM export_receipt_details erd 
    JOIN items i ON erd.item_id = i.id 
    LEFT JOIN batches b ON erd.batch_id = b.id 
    WHERE erd.receipt_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Phiếu xuất: <?php echo htmlspecialchars($receipt['receipt_code']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Mã phiếu:</th><td><?php echo htmlspecialchars($receipt['receipt_code']); ?></td></tr>
                        <tr><th>Ngày xuất:</th><td><?php echo formatDate($receipt['receipt_date']); ?></td></tr>
                        <tr><th>Kho:</th><td><?php echo htmlspecialchars($receipt['warehouse_name']); ?></td></tr>
                        <tr><th>Khách hàng:</th><td><?php echo htmlspecialchars($receipt['customer'] ?? ''); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Người tạo:</th><td><?php echo htmlspecialchars($receipt['creator_name']); ?></td></tr>
                        <tr><th>Người duyệt:</th><td><?php echo htmlspecialchars($receipt['approver_name'] ?? 'Chưa duyệt'); ?></td></tr>
                        <tr><th>Trạng thái:</th><td>
                            <span class="badge bg-<?php 
                                echo $receipt['status'] == 'approved' ? 'success' : 
                                    ($receipt['status'] == 'rejected' ? 'danger' : 'warning'); 
                            ?>">
                                <?php 
                                echo $receipt['status'] == 'approved' ? 'Đã duyệt' : 
                                    ($receipt['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
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
                            <th>Số lượng</th>
                            <th>Đơn vị</th>
                            <th>Mã lô</th>
                            <th>Đơn giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['item_code']); ?></td>
                            <td><?php echo htmlspecialchars($d['item_name']); ?></td>
                            <td><?php echo formatNumber($d['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($d['unit']); ?></td>
                            <td><?php echo htmlspecialchars($d['batch_code'] ?? ''); ?></td>
                            <td><?php echo formatNumber($d['unit_price']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Quay lại</a>
                <a href="print.php?id=<?php echo $id; ?>" class="btn btn-info" target="_blank">
                    <i class="bi bi-printer"></i> In phiếu
                </a>
                <?php if ($receipt['status'] == 'pending' && in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                <form method="POST" action="approve.php" class="d-inline">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-success">Duyệt</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
