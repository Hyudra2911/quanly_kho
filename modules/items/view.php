<?php
$page_title = 'Chi tiết hàng hóa';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT i.*, ig.name as group_name 
    FROM items i 
    LEFT JOIN item_groups ig ON i.group_id = ig.id 
    WHERE i.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: index.php');
    exit();
}

// Lấy tồn kho theo kho
$stmt = $db->prepare("SELECT w.name as warehouse_name, sl.name as location_name, 
    SUM(inv.quantity) as total_quantity
    FROM inventory inv
    JOIN warehouses w ON inv.warehouse_id = w.id
    LEFT JOIN storage_locations sl ON inv.storage_location_id = sl.id
    WHERE inv.item_id = ?
    GROUP BY inv.warehouse_id, inv.storage_location_id
    ORDER BY w.name, sl.name");
$stmt->execute([$id]);
$inventory = $stmt->fetchAll();

// Tổng tồn kho
$stmt = $db->prepare("SELECT SUM(quantity) as total FROM inventory WHERE item_id = ?");
$stmt->execute([$id]);
$total_stock = $stmt->fetch()['total'] ?? 0;

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-eye"></i> Chi tiết hàng hóa</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Hàng hóa</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin hàng hóa</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Mã hàng:</th>
                            <td><?php echo htmlspecialchars($item['code']); ?></td>
                        </tr>
                        <tr>
                            <th>Tên hàng:</th>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Nhóm hàng:</th>
                            <td><?php echo htmlspecialchars($item['group_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Đơn vị tính:</th>
                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        </tr>
                        <tr>
                            <th>Hạn sử dụng:</th>
                            <td>
                                <?php if ($item['has_expiry']): ?>
                                    <span class="badge bg-info">Có</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Không</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Tồn kho tối thiểu:</th>
                            <td><?php echo formatNumber($item['min_stock']); ?></td>
                        </tr>
                        <tr>
                            <th>Trạng thái:</th>
                            <td>
                                <?php if ($item['status']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ngừng</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Mô tả:</th>
                            <td><?php echo nl2br(htmlspecialchars($item['description'] ?? '')); ?></td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Sửa
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tồn kho hiện tại</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Tổng tồn kho:</strong> <?php echo formatNumber($total_stock); ?> <?php echo htmlspecialchars($item['unit']); ?>
                    </div>
                    
                    <?php if (empty($inventory)): ?>
                    <p class="text-muted">Chưa có tồn kho</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kho</th>
                                    <th>Vị trí</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory as $inv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inv['warehouse_name']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['location_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatNumber($inv['total_quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
