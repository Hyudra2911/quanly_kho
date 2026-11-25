<?php
$page_title = 'Chi tiết chuyển kho';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT it.*, w1.name as from_warehouse, w2.name as to_warehouse, u.full_name as creator_name 
    FROM internal_transfers it 
    JOIN warehouses w1 ON it.from_warehouse_id = w1.id 
    JOIN warehouses w2 ON it.to_warehouse_id = w2.id 
    JOIN users u ON it.created_by = u.id 
    WHERE it.id = ?");
$stmt->execute([$id]);
$transfer = $stmt->fetch();

if (!$transfer) {
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT itd.*, i.code as item_code, i.name as item_name, i.unit, b.batch_code 
    FROM internal_transfer_details itd 
    JOIN items i ON itd.item_id = i.id 
    LEFT JOIN batches b ON itd.batch_id = b.id 
    WHERE itd.transfer_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Phiếu chuyển: <?php echo htmlspecialchars($transfer['transfer_code']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Mã phiếu:</th><td><?php echo htmlspecialchars($transfer['transfer_code']); ?></td></tr>
                        <tr><th>Ngày chuyển:</th><td><?php echo formatDate($transfer['transfer_date']); ?></td></tr>
                        <tr><th>Từ kho:</th><td><?php echo htmlspecialchars($transfer['from_warehouse']); ?></td></tr>
                        <tr><th>Đến kho:</th><td><?php echo htmlspecialchars($transfer['to_warehouse']); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Người tạo:</th><td><?php echo htmlspecialchars($transfer['creator_name']); ?></td></tr>
                        <tr><th>Trạng thái:</th><td>
                            <span class="badge bg-<?php 
                                echo $transfer['status'] == 'completed' ? 'success' : 
                                    ($transfer['status'] == 'cancelled' ? 'danger' : 
                                    ($transfer['status'] == 'in_transit' ? 'info' : 'warning')); 
                            ?>">
                                <?php 
                                echo $transfer['status'] == 'completed' ? 'Hoàn thành' : 
                                    ($transfer['status'] == 'cancelled' ? 'Hủy' : 
                                    ($transfer['status'] == 'in_transit' ? 'Đang vận chuyển' : 'Chờ duyệt')); 
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Quay lại</a>
                <?php if ($transfer['status'] == 'pending' && $_SESSION['role'] == 'admin'): ?>
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
