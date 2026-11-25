<?php
$page_title = 'Dashboard';
require_once 'config/config.php';
checkLogin();

$db = getDB();

// Thống kê tổng quan
$stats = [];

// Tổng số hàng hóa
$stmt = $db->query("SELECT COUNT(*) as count FROM items WHERE status = 1");
$stats['total_items'] = $stmt->fetch()['count'];

// Tổng số kho
$stmt = $db->query("SELECT COUNT(*) as count FROM warehouses WHERE status = 1");
$stats['total_warehouses'] = $stmt->fetch()['count'];

// Tổng số vị trí lưu trữ
$stmt = $db->query("SELECT COUNT(*) as count FROM storage_locations WHERE status = 1");
$stats['total_locations'] = $stmt->fetch()['count'];

// Tổng giá trị tồn kho
$stmt = $db->query("SELECT SUM(i.quantity * COALESCE((SELECT unit_price FROM import_receipt_details ird 
    JOIN import_receipts ir ON ird.receipt_id = ir.id 
    WHERE ird.item_id = i.item_id AND ir.status = 'approved' 
    ORDER BY ir.receipt_date DESC LIMIT 1), 0)) as total_value 
    FROM inventory i");
$stats['total_inventory_value'] = $stmt->fetch()['total_value'] ?? 0;

// Phiếu nhập chờ duyệt
$stmt = $db->query("SELECT COUNT(*) as count FROM import_receipts WHERE status = 'pending'");
$stats['pending_imports'] = $stmt->fetch()['count'];

// Phiếu xuất chờ duyệt
$stmt = $db->query("SELECT COUNT(*) as count FROM export_receipts WHERE status = 'pending'");
$stats['pending_exports'] = $stmt->fetch()['count'];

// Chuyển kho chưa hoàn thành
$stmt = $db->query("SELECT COUNT(*) as count FROM internal_transfers WHERE status IN ('pending', 'in_transit')");
$stats['pending_transfers'] = $stmt->fetch()['count'];

// Hàng tồn kho thấp
$stmt = $db->query("SELECT COUNT(*) as count 
    FROM (
        SELECT i.id, i.min_stock
        FROM items i 
        LEFT JOIN inventory inv ON i.id = inv.item_id 
        WHERE i.status = 1 AND i.min_stock > 0 
        GROUP BY i.id, i.min_stock
        HAVING COALESCE(SUM(inv.quantity), 0) < i.min_stock
    ) as low_stock");
$stats['low_stock_items'] = $stmt->fetch()['count'];

// Hàng sắp hết hạn (trong 30 ngày)
$stmt = $db->query("SELECT COUNT(DISTINCT b.id) as count 
    FROM batches b 
    WHERE b.expiry_date IS NOT NULL 
    AND b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND b.current_quantity > 0");
$stats['expiring_items'] = $stmt->fetch()['count'];

// Phiếu nhập gần đây
$stmt = $db->query("SELECT ir.*, w.name as warehouse_name, u.full_name as creator_name 
    FROM import_receipts ir 
    JOIN warehouses w ON ir.warehouse_id = w.id 
    JOIN users u ON ir.created_by = u.id 
    ORDER BY ir.created_at DESC LIMIT 5");
$recent_imports = $stmt->fetchAll();

// Phiếu xuất gần đây
$stmt = $db->query("SELECT er.*, w.name as warehouse_name, u.full_name as creator_name 
    FROM export_receipts er 
    JOIN warehouses w ON er.warehouse_id = w.id 
    JOIN users u ON er.created_by = u.id 
    ORDER BY er.created_at DESC LIMIT 5");
$recent_exports = $stmt->fetchAll();

// Thông báo mới
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        </div>
    </div>
    
    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body dashboard-stat">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-box"></i>
                    </div>
                    <div class="stat-value text-primary"><?php echo formatNumber($stats['total_items']); ?></div>
                    <div class="stat-label">Hàng hóa</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body dashboard-stat">
                    <div class="stat-icon text-success">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-value text-success"><?php echo formatNumber($stats['total_warehouses']); ?></div>
                    <div class="stat-label">Kho hàng</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body dashboard-stat">
                    <div class="stat-icon text-info">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div class="stat-value text-info"><?php echo formatNumber($stats['total_locations']); ?></div>
                    <div class="stat-label">Vị trí lưu trữ</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body dashboard-stat">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-value text-warning"><?php echo formatNumber($stats['total_inventory_value']); ?></div>
                    <div class="stat-label">Giá trị tồn kho (VNĐ)</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cảnh báo -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-triangle text-warning"></i> Chờ duyệt
                    </h5>
                    <p class="card-text">
                        <strong>Phiếu nhập:</strong> <?php echo $stats['pending_imports']; ?><br>
                        <strong>Phiếu xuất:</strong> <?php echo $stats['pending_exports']; ?><br>
                        <strong>Chuyển kho:</strong> <?php echo $stats['pending_transfers']; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-circle text-danger"></i> Tồn kho thấp
                    </h5>
                    <p class="card-text">
                        <strong>Số mặt hàng:</strong> <?php echo $stats['low_stock_items']; ?>
                    </p>
                    <a href="<?php echo BASE_URL; ?>modules/inventory/index.php?filter=low_stock" class="btn btn-sm btn-danger">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calendar-x text-info"></i> Sắp hết hạn
                    </h5>
                    <p class="card-text">
                        <strong>Số lô hàng:</strong> <?php echo $stats['expiring_items']; ?>
                    </p>
                    <a href="<?php echo BASE_URL; ?>modules/inventory/index.php?filter=expiring" class="btn btn-sm btn-info">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Phiếu nhập/xuất gần đây -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Phiếu nhập gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Kho</th>
                                    <th>Ngày</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_imports as $import): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($import['receipt_code']); ?></td>
                                    <td><?php echo htmlspecialchars($import['warehouse_name']); ?></td>
                                    <td><?php echo formatDate($import['receipt_date']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $import['status'] == 'approved' ? 'success' : 
                                                ($import['status'] == 'rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php 
                                            echo $import['status'] == 'approved' ? 'Đã duyệt' : 
                                                ($import['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo BASE_URL; ?>modules/import_receipts/index.php" class="btn btn-sm btn-primary">
                        Xem tất cả
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-arrow-up"></i> Phiếu xuất gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Kho</th>
                                    <th>Ngày</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_exports as $export): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($export['receipt_code']); ?></td>
                                    <td><?php echo htmlspecialchars($export['warehouse_name']); ?></td>
                                    <td><?php echo formatDate($export['receipt_date']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $export['status'] == 'approved' ? 'success' : 
                                                ($export['status'] == 'rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php 
                                            echo $export['status'] == 'approved' ? 'Đã duyệt' : 
                                                ($export['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); 
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo BASE_URL; ?>modules/export_receipts/index.php" class="btn btn-sm btn-primary">
                        Xem tất cả
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Biểu đồ -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Thống kê nhập xuất (7 ngày gần đây)</h5>
                </div>
                <div class="card-body">
                    <canvas id="importExportChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Phân bố tồn kho theo kho</h5>
                </div>
                <div class="card-body">
                    <canvas id="warehouseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Biểu đồ nhập xuất
<?php
$stmt = $db->query("SELECT DATE(receipt_date) as date, COUNT(*) as count 
    FROM import_receipts 
    WHERE receipt_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'approved'
    GROUP BY DATE(receipt_date)");
$imports_data = [];
while ($row = $stmt->fetch()) {
    $imports_data[$row['date']] = $row['count'];
}

$stmt = $db->query("SELECT DATE(receipt_date) as date, COUNT(*) as count 
    FROM export_receipts 
    WHERE receipt_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'approved'
    GROUP BY DATE(receipt_date)");
$exports_data = [];
while ($row = $stmt->fetch()) {
    $exports_data[$row['date']] = $row['count'];
}

$labels = [];
$imports = [];
$exports = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d/m', strtotime($date));
    $imports[] = $imports_data[$date] ?? 0;
    $exports[] = $exports_data[$date] ?? 0;
}
?>
const ctx1 = document.getElementById('importExportChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Phiếu nhập',
            data: <?php echo json_encode($imports); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Phiếu xuất',
            data: <?php echo json_encode($exports); ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Biểu đồ phân bố tồn kho
<?php
$stmt = $db->query("SELECT w.name, SUM(inv.quantity) as total 
    FROM warehouses w 
    LEFT JOIN inventory inv ON w.id = inv.warehouse_id 
    WHERE w.status = 1 
    GROUP BY w.id");
$warehouse_data = [];
while ($row = $stmt->fetch()) {
    $warehouse_data[] = ['name' => $row['name'], 'total' => $row['total'] ?? 0];
}
?>
const ctx2 = document.getElementById('warehouseChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($warehouse_data, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($warehouse_data, 'total')); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<?php include 'includes/footer.php'; ?>
