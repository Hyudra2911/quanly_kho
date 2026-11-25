<?php
$page_title = 'Quản lý tồn kho';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$filter = $_GET['filter'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';
$item_id = $_GET['item_id'] ?? '';

$query = "SELECT i.id, i.code, i.name, i.unit, i.min_stock, 
    COALESCE(SUM(inv.quantity), 0) as total_quantity,
    w.id as warehouse_id, w.name as warehouse_name
    FROM items i
    CROSS JOIN warehouses w
    LEFT JOIN inventory inv ON i.id = inv.item_id AND w.id = inv.warehouse_id
    WHERE i.status = 1 AND w.status = 1";

$params = [];

if ($warehouse_id) {
    $query .= " AND w.id = ?";
    $params[] = $warehouse_id;
}

if ($item_id) {
    $query .= " AND i.id = ?";
    $params[] = $item_id;
}

$query .= " GROUP BY i.id, w.id";

if ($filter == 'low_stock') {
    $query .= " HAVING total_quantity < i.min_stock OR (total_quantity = 0 AND i.min_stock > 0)";
}

$query .= " ORDER BY i.code, w.name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$inventory = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM warehouses WHERE status = 1 ORDER BY name");
$warehouses = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM items WHERE status = 1 ORDER BY name");
$items = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-clipboard-data"></i> Quản lý tồn kho</h1>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" name="warehouse_id">
                            <option value="">Tất cả kho</option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?php echo $wh['id']; ?>" <?php echo $warehouse_id == $wh['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($wh['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="item_id">
                            <option value="">Tất cả hàng hóa</option>
                            <?php foreach ($items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" <?php echo $item_id == $item['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['code'] . ' - ' . $item['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                    <div class="col-md-2">
                        <a href="index.php?filter=low_stock" class="btn btn-danger w-100">Tồn kho thấp</a>
                    </div>
                    <div class="col-md-2">
                        <a href="index.php" class="btn btn-secondary w-100">Làm mới</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã hàng</th>
                            <th>Tên hàng</th>
                            <th>Kho</th>
                            <th>Tồn kho</th>
                            <th>Đơn vị</th>
                            <th>Tồn tối thiểu</th>
                            <th>Cảnh báo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $inv): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inv['code']); ?></td>
                            <td><?php echo htmlspecialchars($inv['name']); ?></td>
                            <td><?php echo htmlspecialchars($inv['warehouse_name']); ?></td>
                            <td>
                                <strong><?php echo formatNumber($inv['total_quantity']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($inv['unit']); ?></td>
                            <td><?php echo formatNumber($inv['min_stock']); ?></td>
                            <td>
                                <?php if ($inv['total_quantity'] < $inv['min_stock']): ?>
                                    <span class="badge bg-danger">Tồn kho thấp</span>
                                <?php elseif ($inv['total_quantity'] == 0): ?>
                                    <span class="badge bg-warning">Hết hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Bình thường</span>
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
