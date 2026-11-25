<?php
$page_title = 'Vị trí lưu trữ';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$warehouse_id = $_GET['warehouse_id'] ?? '';

$query = "SELECT sl.*, w.name as warehouse_name 
    FROM storage_locations sl 
    JOIN warehouses w ON sl.warehouse_id = w.id 
    WHERE 1=1";
$params = [];

if ($warehouse_id) {
    $query .= " AND sl.warehouse_id = ?";
    $params[] = $warehouse_id;
}

$query .= " ORDER BY w.name, sl.code";

$stmt = $db->prepare($query);
$stmt->execute($params);
$locations = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM warehouses WHERE status = 1 ORDER BY name");
$warehouses = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-geo-alt"></i> Vị trí lưu trữ</h1>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Danh sách vị trí</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm mới
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" name="warehouse_id">
                            <option value="">Tất cả kho</option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?php echo $wh['id']; ?>" <?php echo $warehouse_id == $wh['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($wh['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kho</th>
                            <th>Mã vị trí</th>
                            <th>Tên vị trí</th>
                            <th>Khu vực</th>
                            <th>Kệ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locations as $loc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loc['warehouse_name']); ?></td>
                            <td><?php echo htmlspecialchars($loc['code']); ?></td>
                            <td><?php echo htmlspecialchars($loc['name']); ?></td>
                            <td><?php echo htmlspecialchars($loc['area'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($loc['shelf'] ?? ''); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $loc['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vị trí này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
