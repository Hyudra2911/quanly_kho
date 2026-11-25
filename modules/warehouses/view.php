<?php
$page_title = 'Chi tiết kho';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT w.*, u.full_name as manager_name 
    FROM warehouses w 
    LEFT JOIN users u ON w.manager_id = u.id 
    WHERE w.id = ?");
$stmt->execute([$id]);
$warehouse = $stmt->fetch();

if (!$warehouse) {
    header('Location: index.php');
    exit();
}

// Lấy danh sách vị trí lưu trữ
$stmt = $db->prepare("SELECT * FROM storage_locations WHERE warehouse_id = ? ORDER BY code");
$stmt->execute([$id]);
$locations = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-building"></i> Chi tiết kho: <?php echo htmlspecialchars($warehouse['name']); ?></h1>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin kho</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th>Mã kho:</th><td><?php echo htmlspecialchars($warehouse['code']); ?></td></tr>
                        <tr><th>Tên kho:</th><td><?php echo htmlspecialchars($warehouse['name']); ?></td></tr>
                        <tr><th>Địa chỉ:</th><td><?php echo htmlspecialchars($warehouse['address'] ?? ''); ?></td></tr>
                        </tr>
                        <tr><th>Quản lý:</th><td><?php echo htmlspecialchars($warehouse['manager_name'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Trạng thái:</th><td>
                            <?php if ($warehouse['status']): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Ngừng</span>
                            <?php endif; ?>
                        </td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Vị trí lưu trữ</h5>
                    <a href="../storage_locations/add.php?warehouse_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Thêm vị trí
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($locations)): ?>
                    <p class="text-muted">Chưa có vị trí lưu trữ</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên</th>
                                    <th>Khu vực</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($locations as $loc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loc['code']); ?></td>
                                    <td><?php echo htmlspecialchars($loc['name']); ?></td>
                                    <td><?php echo htmlspecialchars($loc['area'] ?? ''); ?></td>
                                    <td>
                                        <a href="../storage_locations/edit.php?id=<?php echo $loc['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
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
    
    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
