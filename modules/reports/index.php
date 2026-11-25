<?php
$page_title = 'Báo cáo và thống kê';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$type = $_GET['type'] ?? 'inventory';
$period = $_GET['period'] ?? 'month';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';

$warehouses = [];
if (in_array($type, ['import', 'export'])) {
    $stmt = $db->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name");
    $warehouses = $stmt->fetchAll();
}

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-graph-up"></i> Báo cáo</h1>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="list-group list-group-flush">
                    <a href="?type=inventory" class="list-group-item list-group-item-action <?php echo $type == 'inventory' ? 'active' : ''; ?>">
                        <i class="bi bi-clipboard-data"></i> Tồn kho
                    </a>
                    <a href="?type=import" class="list-group-item list-group-item-action <?php echo $type == 'import' ? 'active' : ''; ?>">
                        <i class="bi bi-box-arrow-in-down"></i> Nhập kho
                    </a>
                    <a href="?type=export" class="list-group-item list-group-item-action <?php echo $type == 'export' ? 'active' : ''; ?>">
                        <i class="bi bi-box-arrow-up"></i> Xuất kho
                    </a>
                    <a href="?type=transfers" class="list-group-item list-group-item-action <?php echo $type == 'transfers' ? 'active' : ''; ?>">
                        <i class="bi bi-arrow-repeat"></i> Chuyển kho
                    </a>
                    <a href="?type=expiring" class="list-group-item list-group-item-action <?php echo $type == 'expiring' ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-x"></i> Hàng sắp hết hạn
                    </a>
                    <a href="?type=revenue" class="list-group-item list-group-item-action <?php echo $type == 'revenue' ? 'active' : ''; ?>">
                        <i class="bi bi-cash-stack"></i> Doanh thu
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="mb-3" id="reportForm">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <?php if (in_array($type, ['import', 'export'])): ?>
                            <div class="col-md-2">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="">Tất cả</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kho</label>
                                <select class="form-select" name="warehouse_id" id="warehouse_id">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?php echo $warehouse['id']; ?>" <?php echo (string)$warehouse_id === (string)$warehouse['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($warehouse['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-<?php echo in_array($type, ['import', 'export']) ? '2' : '3'; ?>">
                                <label class="form-label">&nbsp;</label>
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-download"></i> Xuất
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" id="exportExcel">
                                            <i class="bi bi-file-earmark-excel"></i> Excel
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" id="exportPdf" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($type == 'inventory'): ?>
                        <?php
                        try {
                            $stmt = $db->query("SELECT i.code, i.name, i.unit, SUM(inv.quantity) as total 
                                FROM items i 
                                LEFT JOIN inventory inv ON i.id = inv.item_id 
                                WHERE i.status = 1 
                                GROUP BY i.id 
                                ORDER BY total DESC");
                            $data = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu tồn kho: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $data = [];
                        }
                        ?>
                        <h5>Báo cáo tồn kho</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã hàng</th>
                                        <th>Tên hàng</th>
                                        <th>Tồn kho</th>
                                        <th>Đơn vị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo formatNumber($row['total'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($type == 'import' || $type == 'import_export'): ?>
                        <?php
                        try {
                            $sql = "SELECT ir.id, ir.receipt_code, ir.receipt_date, ir.supplier, ir.status,
                                w.name as warehouse_name,
                                creator.full_name as creator_name,
                                approver.full_name as approver_name,
                                COALESCE(COUNT(DISTINCT ird.id), 0) as item_count,
                                COALESCE(SUM(ird.quantity), 0) as total_qty,
                                COALESCE(SUM(ird.quantity * ird.unit_price), 0) as total_amount
                                FROM import_receipts ir 
                                LEFT JOIN warehouses w ON ir.warehouse_id = w.id
                                LEFT JOIN users creator ON ir.created_by = creator.id
                                LEFT JOIN users approver ON ir.approved_by = approver.id
                                LEFT JOIN import_receipt_details ird ON ir.id = ird.receipt_id
                                WHERE ir.receipt_date BETWEEN ? AND ?";
                            $params = [$start_date, $end_date];

                            if ($status !== '') {
                                $sql .= " AND ir.status = ?";
                                $params[] = $status;
                            }
                            if ($warehouse_id !== '') {
                                $sql .= " AND ir.warehouse_id = ?";
                                $params[] = (int)$warehouse_id;
                            }

                            $sql .= " GROUP BY ir.id ORDER BY ir.receipt_date DESC, ir.receipt_code DESC";
                            $stmt = $db->prepare($sql);
                            $stmt->execute($params);
                            $imports = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu nhập kho: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $imports = [];
                        }

                        $total_item_count = 0;
                        $total_qty = 0;
                        $total_amount = 0;
                        ?>
                        <h5>Báo cáo nhập kho</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Ngày</th>
                                        <th>Kho</th>
                                        <th>Nhà cung cấp</th>
                                        <th>Trạng thái</th>
                                        <th>Số mặt hàng</th>
                                        <th>Tổng SL nhập</th>
                                        <th>Giá trị nhập (VNĐ)</th>
                                        <th>Người tạo</th>
                                        <th>Người duyệt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($imports)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($imports as $row): ?>
                                    <?php
                                    $total_item_count += (int)$row['item_count'];
                                    $total_qty += (float)$row['total_qty'];
                                    $total_amount += (float)$row['total_amount'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['receipt_code']); ?></td>
                                        <td><?php echo formatDate($row['receipt_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['warehouse_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['supplier'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo $row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatNumber($row['item_count']); ?></td>
                                        <td><?php echo formatNumber($row['total_qty']); ?></td>
                                        <td><?php echo formatNumber($row['total_amount']); ?></td>
                                        <td><?php echo htmlspecialchars($row['creator_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['approver_name'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold">
                                        <td colspan="5">Tổng cộng</td>
                                        <td><?php echo formatNumber($total_item_count); ?></td>
                                        <td><?php echo formatNumber($total_qty); ?></td>
                                        <td><?php echo formatNumber($total_amount); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($type == 'export'): ?>
                        <?php
                        try {
                            $sql = "SELECT er.id, er.receipt_code, er.receipt_date, er.customer, er.status,
                                w.name as warehouse_name,
                                creator.full_name as creator_name,
                                approver.full_name as approver_name,
                                COALESCE(COUNT(DISTINCT erd.id), 0) as item_count,
                                COALESCE(SUM(erd.quantity), 0) as total_qty,
                                COALESCE(SUM(erd.quantity * erd.unit_price), 0) as total_amount
                                FROM export_receipts er
                                LEFT JOIN warehouses w ON er.warehouse_id = w.id
                                LEFT JOIN users creator ON er.created_by = creator.id
                                LEFT JOIN users approver ON er.approved_by = approver.id
                                LEFT JOIN export_receipt_details erd ON er.id = erd.receipt_id
                                WHERE er.receipt_date BETWEEN ? AND ?";
                            $params = [$start_date, $end_date];

                            if ($status !== '') {
                                $sql .= " AND er.status = ?";
                                $params[] = $status;
                            }
                            if ($warehouse_id !== '') {
                                $sql .= " AND er.warehouse_id = ?";
                                $params[] = (int)$warehouse_id;
                            }

                            $sql .= " GROUP BY er.id ORDER BY er.receipt_date DESC, er.receipt_code DESC";
                            $stmt = $db->prepare($sql);
                            $stmt->execute($params);
                            $exports = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu xuất kho: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $exports = [];
                        }

                        $total_item_count = 0;
                        $total_qty = 0;
                        $total_amount = 0;
                        ?>
                        <h5>Báo cáo xuất kho</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Ngày</th>
                                        <th>Kho</th>
                                        <th>Khách hàng</th>
                                        <th>Trạng thái</th>
                                        <th>Số mặt hàng</th>
                                        <th>Tổng SL xuất</th>
                                        <th>Giá trị xuất (VNĐ)</th>
                                        <th>Người tạo</th>
                                        <th>Người duyệt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($exports)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($exports as $row): ?>
                                    <?php
                                    $total_item_count += (int)$row['item_count'];
                                    $total_qty += (float)$row['total_qty'];
                                    $total_amount += (float)$row['total_amount'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['receipt_code']); ?></td>
                                        <td><?php echo formatDate($row['receipt_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['warehouse_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo $row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatNumber($row['item_count']); ?></td>
                                        <td><?php echo formatNumber($row['total_qty']); ?></td>
                                        <td><?php echo formatNumber($row['total_amount']); ?></td>
                                        <td><?php echo htmlspecialchars($row['creator_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['approver_name'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold">
                                        <td colspan="5">Tổng cộng</td>
                                        <td><?php echo formatNumber($total_item_count); ?></td>
                                        <td><?php echo formatNumber($total_qty); ?></td>
                                        <td><?php echo formatNumber($total_amount); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($type == 'transfers'): ?>
                        <?php
                        try {
                            $stmt = $db->prepare("SELECT it.*, 
                                w1.name as from_warehouse, w2.name as to_warehouse,
                                u.full_name as created_by_name,
                                COALESCE(COUNT(DISTINCT itd.id), 0) as item_count,
                                COALESCE(SUM(itd.quantity), 0) as total_quantity
                                FROM internal_transfers it
                                LEFT JOIN warehouses w1 ON it.from_warehouse_id = w1.id
                                LEFT JOIN warehouses w2 ON it.to_warehouse_id = w2.id
                                LEFT JOIN users u ON it.created_by = u.id
                                LEFT JOIN internal_transfer_details itd ON it.id = itd.transfer_id
                                WHERE it.transfer_date BETWEEN ? AND ?
                                AND it.status = 'completed'
                                GROUP BY it.id
                                ORDER BY it.transfer_date DESC, it.transfer_code DESC");
                            $stmt->execute([$start_date, $end_date]);
                            $transfers = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu chuyển kho: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $transfers = [];
                        }
                        ?>
                        <h5>Báo cáo chuyển kho</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Ngày chuyển</th>
                                        <th>Từ kho</th>
                                        <th>Đến kho</th>
                                        <th>Số mặt hàng</th>
                                        <th>Tổng số lượng</th>
                                        <th>Người tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transfers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($transfers as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t['transfer_code']); ?></td>
                                        <td><?php echo formatDate($t['transfer_date']); ?></td>
                                        <td><?php echo htmlspecialchars($t['from_warehouse'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($t['to_warehouse'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatNumber($t['item_count'] ?? 0); ?></td>
                                        <td><?php echo formatNumber($t['total_quantity'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($t['created_by_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($type == 'expiring'): ?>
                        <?php
                        try {
                            // Chỉ lấy hàng đã được duyệt (đã vào kho): từ bảng batches
                            $stmt = $db->query("SELECT b.*, i.code, i.name, i.unit, w.name as warehouse_name
                                FROM batches b 
                                JOIN items i ON b.item_id = i.id 
                                LEFT JOIN storage_locations sl ON b.storage_location_id = sl.id
                                LEFT JOIN warehouses w ON sl.warehouse_id = w.id
                                WHERE b.expiry_date IS NOT NULL 
                                AND b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                AND b.current_quantity > 0
                                AND b.status = 1
                                ORDER BY b.expiry_date ASC");
                            $expiring_batches = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu hàng sắp hết hạn: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $expiring_batches = [];
                        }
                        ?>
                        <h5>Hàng sắp hết hạn (30 ngày tới)</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã hàng</th>
                                        <th>Tên hàng</th>
                                        <th>Mã lô</th>
                                        <th>Số lượng</th>
                                        <th>Hạn sử dụng</th>
                                        <th>Kho</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($expiring_batches)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Không có hàng sắp hết hạn trong 30 ngày tới</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($expiring_batches as $e): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($e['code']); ?></td>
                                        <td><?php echo htmlspecialchars($e['name']); ?></td>
                                        <td><?php echo htmlspecialchars($e['batch_code']); ?></td>
                                        <td><?php echo formatNumber($e['current_quantity']); ?></td>
                                        <td><?php echo formatDate($e['expiry_date']); ?></td>
                                        <td><?php echo htmlspecialchars($e['warehouse_name'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-success">Đã duyệt</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($type == 'revenue'): ?>
                        <?php
                        try {
                            $stmt = $db->prepare("SELECT DATE(er.receipt_date) as date,
                                COUNT(DISTINCT er.id) as receipt_count,
                                COALESCE(SUM(erd.quantity), 0) as total_qty,
                                COALESCE(SUM(erd.quantity * erd.unit_price), 0) as total_revenue
                                FROM export_receipts er
                                LEFT JOIN export_receipt_details erd ON er.id = erd.receipt_id
                                WHERE er.receipt_date BETWEEN ? AND ?
                                AND er.status = 'approved'
                                GROUP BY DATE(er.receipt_date)
                                ORDER BY DATE(er.receipt_date)");
                            $stmt->execute([$start_date, $end_date]);
                            $revenues = $stmt->fetchAll();
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi truy vấn dữ liệu doanh thu: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $revenues = [];
                        }

                        $total_receipts = 0;
                        $total_qty = 0;
                        $total_revenue = 0;

                        foreach ($revenues as $row) {
                            $total_receipts += (int)$row['receipt_count'];
                            $total_qty += (float)$row['total_qty'];
                            $total_revenue += (float)$row['total_revenue'];
                        }
                        ?>
                        <h5>Báo cáo doanh thu</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Tổng doanh thu kỳ này</div>
                                        <div class="h4 mb-0 text-success"><?php echo formatNumber($total_revenue); ?> VNĐ</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Tổng phiếu xuất</div>
                                        <div class="h4 mb-0 text-primary"><?php echo formatNumber($total_receipts); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Tổng số lượng xuất</div>
                                        <div class="h4 mb-0 text-info"><?php echo formatNumber($total_qty); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Số phiếu xuất</th>
                                        <th>Tổng số lượng xuất</th>
                                        <th>Doanh thu (VNĐ)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($revenues)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($revenues as $row): ?>
                                    <tr>
                                        <td><?php echo formatDate($row['date']); ?></td>
                                        <td><?php echo formatNumber($row['receipt_count']); ?></td>
                                        <td><?php echo formatNumber($row['total_qty']); ?></td>
                                        <td><?php echo formatNumber($row['total_revenue']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold">
                                        <td>Tổng cộng</td>
                                        <td><?php echo formatNumber($total_receipts); ?></td>
                                        <td><?php echo formatNumber($total_qty); ?></td>
                                        <td><?php echo formatNumber($total_revenue); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tự động submit form khi thay đổi ngày
document.getElementById('start_date').addEventListener('change', function() {
    document.getElementById('reportForm').submit();
});

document.getElementById('end_date').addEventListener('change', function() {
    document.getElementById('reportForm').submit();
});

// Cập nhật link export khi thay đổi ngày
function updateExportLinks() {
    const type = '<?php echo $type; ?>';
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const statusElem = document.getElementById('status');
    const warehouseElem = document.getElementById('warehouse_id');
    const status = statusElem ? statusElem.value : '';
    const warehouseId = warehouseElem ? warehouseElem.value : '';
    let extraParams = '';

    if (status) {
        extraParams += '&status=' + encodeURIComponent(status);
    }
    if (warehouseId) {
        extraParams += '&warehouse_id=' + encodeURIComponent(warehouseId);
    }
    
    document.getElementById('exportExcel').href = 
        'export.php?type=' + type + '&format=excel&start_date=' + startDate + '&end_date=' + endDate + extraParams;
    document.getElementById('exportPdf').href = 
        'export.php?type=' + type + '&format=pdf&start_date=' + startDate + '&end_date=' + endDate + extraParams;
}

// Cập nhật link khi thay đổi ngày
document.getElementById('start_date').addEventListener('change', updateExportLinks);
document.getElementById('end_date').addEventListener('change', updateExportLinks);

const statusElem = document.getElementById('status');
if (statusElem) {
    statusElem.addEventListener('change', function() {
        document.getElementById('reportForm').submit();
    });
}

const warehouseElem = document.getElementById('warehouse_id');
if (warehouseElem) {
    warehouseElem.addEventListener('change', function() {
        document.getElementById('reportForm').submit();
    });
}

// Khởi tạo link export khi trang load
updateExportLinks();
</script>

<?php include '../../includes/footer.php'; ?>
