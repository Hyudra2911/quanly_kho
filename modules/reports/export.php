<?php
require_once '../../config/config.php';
checkLogin();

$type = $_GET['type'] ?? 'inventory';
$format = $_GET['format'] ?? 'excel';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';

$db = getDB();

// Titles array
$titles = [
    'inventory' => 'BÁO CÁO TỒN KHO',
    'import' => 'BÁO CÁO NHẬP KHO',
    'export' => 'BÁO CÁO XUẤT KHO',
    'import_export' => 'BÁO CÁO NHẬP KHO',
    'transfers' => 'BÁO CÁO CHUYỂN KHO',
    'expiring' => 'BÁO CÁO HÀNG SẮP HẾT HẠN',
    'revenue' => 'BÁO CÁO DOANH THU'
];

if ($format == 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="bao_cao_' . $type . '_' . date('Ymd_His') . '.xls"');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1" style="border-collapse: collapse;">';
    
    // Phần header
    echo '<tr style="background-color: #4CAF50; color: white;"><td colspan="10" align="center" style="font-weight: bold; padding: 10px;">';
    echo htmlspecialchars($titles[$type] ?? 'BÁO CÁO');
    echo '</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="10">';
    echo '<strong>Từ ngày:</strong> ' . formatDate($start_date) . ' &nbsp;&nbsp;&nbsp;&nbsp; ';
    echo '<strong>Đến ngày:</strong> ' . formatDate($end_date) . ' &nbsp;&nbsp;&nbsp;&nbsp; ';
    echo '<strong>Ngày lập:</strong> ' . formatDateTime(date('Y-m-d H:i:s'));
    echo '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="10">';
    echo '<strong>Người xuất:</strong> ' . htmlspecialchars($_SESSION['full_name'] ?? 'N/A');
    echo '</td>';
    echo '</tr>';
    
    echo '<tr></tr>'; // dòng trống
    
    if ($type == 'inventory') {
        echo '<tr style="background-color: #f0f0f0;">';
        echo '<th>STT</th>';
        echo '<th>Mã hàng</th>';
        echo '<th>Tên hàng</th>';
        echo '<th>Tồn kho</th>';
        echo '<th>Đơn vị</th>';
        echo '</tr>';
        
        $stmt = $db->query("SELECT i.code, i.name, i.unit, SUM(inv.quantity) as total 
            FROM items i 
            LEFT JOIN inventory inv ON i.id = inv.item_id 
            WHERE i.status = 1 
            GROUP BY i.id 
            ORDER BY total DESC");
        
        $stt = 1;
        $total_qty = 0;
        while ($row = $stmt->fetch()) {
            $total_qty += $row['total'] ?? 0;
            echo '<tr>';
            echo '<td>' . $stt++ . '</td>';
            echo '<td>' . htmlspecialchars($row['code']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td align="right">' . formatNumber($row['total'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars($row['unit']) . '</td>';
            echo '</tr>';
        }
        
        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="3">TỔNG CỘNG</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td></td>';
        echo '</tr>';
        
    } elseif ($type == 'import' || $type == 'import_export') {
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>STT</th>';
        echo '<th>Mã phiếu</th>';
        echo '<th>Ngày</th>';
        echo '<th>Kho</th>';
        echo '<th>Nhà cung cấp</th>';
        echo '<th>Trạng thái</th>';
        echo '<th>Số mặt hàng</th>';
        echo '<th>Tổng SL nhập</th>';
        echo '<th>Giá trị nhập (VNĐ)</th>';
        echo '<th>Người tạo</th>';
        echo '<th>Người duyệt</th>';
        echo '</tr>';

        $sql = "SELECT ir.receipt_code, ir.receipt_date, ir.supplier, ir.status,
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

        $total_item_count = 0;
        $total_qty = 0;
        $total_amount = 0;

        if (empty($imports)) {
            echo '<tr><td colspan="11" align="center">Không có dữ liệu</td></tr>';
        } else {
            $stt = 1;
            foreach ($imports as $row) {
                $total_item_count += (int)$row['item_count'];
                $total_qty += (float)$row['total_qty'];
                $total_amount += (float)$row['total_amount'];

                echo '<tr>';
                echo '<td>' . $stt++ . '</td>';
                echo '<td>' . htmlspecialchars($row['receipt_code']) . '</td>';
                echo '<td>' . formatDate($row['receipt_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['warehouse_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['supplier'] ?? '') . '</td>';
                echo '<td>' . ($row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt')) . '</td>';
                echo '<td align="right">' . formatNumber($row['item_count']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_qty']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_amount']) . '</td>';
                echo '<td>' . htmlspecialchars($row['creator_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['approver_name'] ?? '-') . '</td>';
                echo '</tr>';
            }
        }

        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="6">TỔNG CỘNG</td>';
        echo '<td align="right">' . formatNumber($total_item_count) . '</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td align="right">' . formatNumber($total_amount) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';

    } elseif ($type == 'export') {
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>STT</th>';
        echo '<th>Mã phiếu</th>';
        echo '<th>Ngày</th>';
        echo '<th>Kho</th>';
        echo '<th>Khách hàng</th>';
        echo '<th>Trạng thái</th>';
        echo '<th>Số mặt hàng</th>';
        echo '<th>Tổng SL xuất</th>';
        echo '<th>Giá trị xuất (VNĐ)</th>';
        echo '<th>Người tạo</th>';
        echo '<th>Người duyệt</th>';
        echo '</tr>';

        $sql = "SELECT er.receipt_code, er.receipt_date, er.customer, er.status,
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

        $total_item_count = 0;
        $total_qty = 0;
        $total_amount = 0;

        if (empty($exports)) {
            echo '<tr><td colspan="11" align="center">Không có dữ liệu</td></tr>';
        } else {
            $stt = 1;
            foreach ($exports as $row) {
                $total_item_count += (int)$row['item_count'];
                $total_qty += (float)$row['total_qty'];
                $total_amount += (float)$row['total_amount'];

                echo '<tr>';
                echo '<td>' . $stt++ . '</td>';
                echo '<td>' . htmlspecialchars($row['receipt_code']) . '</td>';
                echo '<td>' . formatDate($row['receipt_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['warehouse_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['customer'] ?? '') . '</td>';
                echo '<td>' . ($row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt')) . '</td>';
                echo '<td align="right">' . formatNumber($row['item_count']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_qty']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_amount']) . '</td>';
                echo '<td>' . htmlspecialchars($row['creator_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['approver_name'] ?? '-') . '</td>';
                echo '</tr>';
            }
        }

        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="6">TỔNG CỘNG</td>';
        echo '<td align="right">' . formatNumber($total_item_count) . '</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td align="right">' . formatNumber($total_amount) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';
        
    } elseif ($type == 'transfers') {
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>STT</th>';
        echo '<th>Mã phiếu</th>';
        echo '<th>Ngày chuyển</th>';
        echo '<th>Từ kho</th>';
        echo '<th>Đến kho</th>';
        echo '<th>Số mặt hàng</th>';
        echo '<th>Tổng số lượng</th>';
        echo '<th>Người tạo</th>';
        echo '</tr>';
        
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
        
        $stt = 1;
        $total_items = 0;
        $total_qty = 0;
        
        if ($stmt->rowCount() == 0) {
            echo '<tr><td colspan="8" align="center">Không có dữ liệu</td></tr>';
        } else {
            while ($row = $stmt->fetch()) {
                $total_items += $row['item_count'] ?? 0;
                $total_qty += $row['total_quantity'] ?? 0;
                
                echo '<tr>';
                echo '<td>' . $stt++ . '</td>';
                echo '<td>' . htmlspecialchars($row['transfer_code']) . '</td>';
                echo '<td>' . formatDate($row['transfer_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['from_warehouse'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['to_warehouse'] ?? 'N/A') . '</td>';
                echo '<td align="center">' . formatNumber($row['item_count'] ?? 0) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_quantity'] ?? 0) . '</td>';
                echo '<td>' . htmlspecialchars($row['created_by_name'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
        }
        
        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="5">TỔNG CỘNG</td>';
        echo '<td align="center">' . $total_items . '</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td></td>';
        echo '</tr>';
        
    } elseif ($type == 'expiring') {
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>STT</th>';
        echo '<th>Mã hàng</th>';
        echo '<th>Tên hàng</th>';
        echo '<th>Mã lô</th>';
        echo '<th>Số lượng</th>';
        echo '<th>Hạn sử dụng</th>';
        echo '<th>Vị trí</th>';
        echo '</tr>';
        
        $stmt = $db->query("SELECT b.*, i.code, i.name, i.unit, sl.name as location_name
            FROM batches b 
            JOIN items i ON b.item_id = i.id 
            LEFT JOIN storage_locations sl ON b.storage_location_id = sl.id
            WHERE b.expiry_date IS NOT NULL 
            AND b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            AND b.current_quantity > 0
            ORDER BY b.expiry_date");
        
        $stt = 1;
        $total_qty = 0;
        while ($row = $stmt->fetch()) {
            $total_qty += $row['current_quantity'] ?? 0;
            echo '<tr>';
            echo '<td>' . $stt++ . '</td>';
            echo '<td>' . htmlspecialchars($row['code']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['batch_code']) . '</td>';
            echo '<td align="right">' . formatNumber($row['current_quantity']) . '</td>';
            echo '<td>' . formatDate($row['expiry_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['location_name'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        
        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="4">TỔNG CỘNG</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td></td>';
        echo '</tr>';
    } elseif ($type == 'revenue') {
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>STT</th>';
        echo '<th>Ngày</th>';
        echo '<th>Số phiếu xuất</th>';
        echo '<th>Tổng SL xuất</th>';
        echo '<th>Doanh thu (VNĐ)</th>';
        echo '</tr>';

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

        $stt = 1;
        $total_receipts = 0;
        $total_qty = 0;
        $total_revenue = 0;

        if (empty($revenues)) {
            echo '<tr><td colspan="5" align="center">Không có dữ liệu</td></tr>';
        } else {
            foreach ($revenues as $row) {
                $total_receipts += (int)$row['receipt_count'];
                $total_qty += (float)$row['total_qty'];
                $total_revenue += (float)$row['total_revenue'];

                echo '<tr>';
                echo '<td>' . $stt++ . '</td>';
                echo '<td>' . formatDate($row['date']) . '</td>';
                echo '<td align="center">' . formatNumber($row['receipt_count']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_qty']) . '</td>';
                echo '<td align="right">' . formatNumber($row['total_revenue']) . '</td>';
                echo '</tr>';
            }
        }

        echo '<tr style="background-color: #e8f5e9; font-weight: bold;">';
        echo '<td colspan="2">TỔNG CỘNG</td>';
        echo '<td align="center">' . formatNumber($total_receipts) . '</td>';
        echo '<td align="right">' . formatNumber($total_qty) . '</td>';
        echo '<td align="right">' . formatNumber($total_revenue) . '</td>';
        echo '</tr>';
    }
    
    echo '</table></body></html>';
    exit();
} elseif ($format == 'pdf') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Báo cáo</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            .header h2 { margin: 0; }
            .report-info { margin-bottom: 15px; font-size: 13px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f0f0f0; font-weight: bold; }
            tbody tr:nth-child(odd) { background-color: #f9f9f9; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            @media print {
                body { margin: 0; padding: 10px; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2><?php echo htmlspecialchars($titles[$type] ?? 'BÁO CÁO'); ?></h2>
        </div>
        
        <div class="report-info">
            <strong>Từ ngày:</strong> <?php echo formatDate($start_date); ?> &nbsp;&nbsp;&nbsp;&nbsp; 
            <strong>Đến ngày:</strong> <?php echo formatDate($end_date); ?> &nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Ngày lập:</strong> <?php echo formatDateTime(date('Y-m-d H:i:s')); ?><br>
            <strong>Người xuất:</strong> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'N/A'); ?>
        </div>
        
        <?php
        if ($type == 'inventory') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Mã hàng</th>
                    <th>Tên hàng</th>
                    <th class="text-right">Tồn kho</th>
                    <th>Đơn vị</th>
                </tr>
                <?php
                $stmt = $db->query("SELECT i.code, i.name, i.unit, SUM(inv.quantity) as total 
                    FROM items i 
                    LEFT JOIN inventory inv ON i.id = inv.item_id 
                    WHERE i.status = 1 
                    GROUP BY i.id 
                    ORDER BY total DESC");
                
                $stt = 1;
                $total_qty = 0;
                while ($row = $stmt->fetch()) {
                    $total_qty += $row['total'] ?? 0;
                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total'] ?? 0) . '</td>';
                    echo '<td>' . htmlspecialchars($row['unit']) . '</td>';
                    echo '</tr>';
                }
                ?>
                <tr style="background-color: #e8f5e9; font-weight: bold;">
                    <td colspan="3">TỔNG CỘNG</td>
                    <td class="text-right"><?php echo formatNumber($total_qty); ?></td>
                    <td></td>
                </tr>
            </table>
            <?php
        } elseif ($type == 'import' || $type == 'import_export') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Mã phiếu</th>
                    <th>Ngày</th>
                    <th>Kho</th>
                    <th>Nhà cung cấp</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Số mặt hàng</th>
                    <th class="text-right">Tổng SL nhập</th>
                    <th class="text-right">Giá trị nhập</th>
                    <th>Người tạo</th>
                    <th>Người duyệt</th>
                </tr>
                <?php
                $sql = "SELECT ir.receipt_code, ir.receipt_date, ir.supplier, ir.status,
                    w.name as warehouse,
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
                $sql .= " GROUP BY ir.id ORDER BY ir.receipt_date DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $imports = $stmt->fetchAll();
                
                $stt = 1;
                $total_items = 0;
                $total_qty = 0;
                $total_amount = 0;
                foreach ($imports as $row) {
                    $total_items += $row['item_count'] ?? 0;
                    $total_qty += $row['total_qty'] ?? 0;
                    $total_amount += $row['total_amount'] ?? 0;
                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['receipt_code']) . '</td>';
                    echo '<td>' . formatDate($row['receipt_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['warehouse'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($row['supplier'] ?? '-') . '</td>';
                    echo '<td>' . ($row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt')) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['item_count'] ?? 0) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_qty'] ?? 0) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_amount'] ?? 0) . '</td>';
                    echo '<td>' . htmlspecialchars($row['creator_name'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($row['approver_name'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                if (empty($imports)) {
                    echo '<tr><td colspan="11" class="text-center">Không có dữ liệu</td></tr>';
                }
                ?>
                <tr style="background-color: #e8f5e9; font-weight: bold;">
                    <td colspan="6">TỔNG NHẬP KHO</td>
                    <td class="text-right"><?php echo formatNumber($total_items); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_qty); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_amount); ?></td>
                    <td colspan="2"></td>
                </tr>
            </table>
            <?php
        } elseif ($type == 'export') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Mã phiếu</th>
                    <th>Ngày</th>
                    <th>Kho</th>
                    <th>Khách hàng</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Số mặt hàng</th>
                    <th class="text-right">Tổng SL xuất</th>
                    <th class="text-right">Giá trị xuất</th>
                    <th>Người tạo</th>
                    <th>Người duyệt</th>
                </tr>
                <?php
                $sql = "SELECT er.receipt_code, er.receipt_date, er.customer, er.status,
                    w.name as warehouse,
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
                $sql .= " GROUP BY er.id ORDER BY er.receipt_date DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $exports = $stmt->fetchAll();

                $stt = 1;
                $total_items = 0;
                $total_qty = 0;
                $total_amount = 0;
                foreach ($exports as $row) {
                    $total_items += $row['item_count'] ?? 0;
                    $total_qty += $row['total_qty'] ?? 0;
                    $total_amount += $row['total_amount'] ?? 0;

                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['receipt_code']) . '</td>';
                    echo '<td>' . formatDate($row['receipt_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['warehouse'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($row['customer'] ?? '-') . '</td>';
                    echo '<td>' . ($row['status'] == 'approved' ? 'Đã duyệt' : ($row['status'] == 'rejected' ? 'Từ chối' : 'Chờ duyệt')) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['item_count'] ?? 0) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_qty'] ?? 0) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_amount'] ?? 0) . '</td>';
                    echo '<td>' . htmlspecialchars($row['creator_name'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($row['approver_name'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                if (empty($exports)) {
                    echo '<tr><td colspan="11" class="text-center">Không có dữ liệu</td></tr>';
                }
                ?>
                <tr style="background-color: #e8f5e9; font-weight: bold;">
                    <td colspan="6">TỔNG XUẤT KHO</td>
                    <td class="text-right"><?php echo formatNumber($total_items); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_qty); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_amount); ?></td>
                    <td colspan="2"></td>
                </tr>
            </table>
            <?php
        } elseif ($type == 'transfers') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Mã phiếu</th>
                    <th>Ngày chuyển</th>
                    <th>Từ kho</th>
                    <th>Đến kho</th>
                    <th class="text-right">Số mặt hàng</th>
                    <th class="text-right">Tổng số lượng</th>
                    <th>Người tạo</th>
                </tr>
                <?php
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
                    ORDER BY it.transfer_date DESC");
                $stmt->execute([$start_date, $end_date]);
                
                $stt = 1;
                $total_items = 0;
                $total_qty = 0;
                
                while ($row = $stmt->fetch()) {
                    $total_items += $row['item_count'] ?? 0;
                    $total_qty += $row['total_quantity'] ?? 0;
                    
                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['transfer_code']) . '</td>';
                    echo '<td>' . formatDate($row['transfer_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['from_warehouse'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($row['to_warehouse'] ?? 'N/A') . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['item_count'] ?? 0) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_quantity'] ?? 0) . '</td>';
                    echo '<td>' . htmlspecialchars($row['created_by_name'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                
                if ($stmt->rowCount() == 0) {
                    echo '<tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>';
                }
                ?>
                <tr style="background-color: #e8f5e9; font-weight: bold;">
                    <td colspan="5">TỔNG CỘNG</td>
                    <td class="text-right"><?php echo formatNumber($total_items); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_qty); ?></td>
                    <td></td>
                </tr>
            </table>
            <?php
        } elseif ($type == 'expiring') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Mã hàng</th>
                    <th>Tên hàng</th>
                    <th>Mã lô</th>
                    <th class="text-right">Số lượng</th>
                    <th>Hạn sử dụng</th>
                    <th>Còn lại</th>
                    <th>Vị trí</th>
                    <th>Kho</th>
                </tr>
                <?php
                $stmt = $db->prepare("SELECT b.*, i.code, i.name, i.unit, sl.name as location_name, w.name as warehouse_name,
                    DATEDIFF(b.expiry_date, CURDATE()) as days_until_expiry
                    FROM batches b
                    JOIN items i ON b.item_id = i.id
                    LEFT JOIN storage_locations sl ON b.storage_location_id = sl.id
                    LEFT JOIN warehouses w ON sl.warehouse_id = w.id
                    WHERE b.expiry_date IS NOT NULL 
                        AND b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                        AND b.current_quantity > 0
                    ORDER BY b.expiry_date ASC");
                $stmt->execute();
                $expiring = $stmt->fetchAll();
                
                $stt = 1;
                foreach ($expiring as $row) {
                    $days = $row['days_until_expiry'];
                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['batch_code']) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['current_quantity']) . '</td>';
                    echo '<td>' . formatDate($row['expiry_date']) . '</td>';
                    echo '<td>';
                    if ($days <= 0) {
                        echo '<strong style="color: red;">Hết hạn</strong>';
                    } elseif ($days <= 7) {
                        echo '<strong style="color: red;">' . $days . ' ngày</strong>';
                    } elseif ($days <= 30) {
                        echo '<strong style="color: orange;">' . $days . ' ngày</strong>';
                    } else {
                        echo $days . ' ngày';
                    }
                    echo '</td>';
                    echo '<td>' . htmlspecialchars($row['location_name'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($row['warehouse_name'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                if (empty($expiring)) {
                    echo '<tr><td colspan="9" class="text-center">Không có hàng sắp hết hạn</td></tr>';
                }
                ?>
            </table>
            <?php
        } elseif ($type == 'revenue') {
            ?>
            <table>
                <tr>
                    <th>STT</th>
                    <th>Ngày</th>
                    <th class="text-right">Số phiếu xuất</th>
                    <th class="text-right">Tổng SL xuất</th>
                    <th class="text-right">Doanh thu (VNĐ)</th>
                </tr>
                <?php
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

                $stt = 1;
                $total_receipts = 0;
                $total_qty = 0;
                $total_revenue = 0;

                foreach ($revenues as $row) {
                    $total_receipts += (int)$row['receipt_count'];
                    $total_qty += (float)$row['total_qty'];
                    $total_revenue += (float)$row['total_revenue'];

                    echo '<tr>';
                    echo '<td class="text-center">' . $stt++ . '</td>';
                    echo '<td>' . formatDate($row['date']) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['receipt_count']) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_qty']) . '</td>';
                    echo '<td class="text-right">' . formatNumber($row['total_revenue']) . '</td>';
                    echo '</tr>';
                }

                if (empty($revenues)) {
                    echo '<tr><td colspan="5" class="text-center">Không có dữ liệu doanh thu</td></tr>';
                }
                ?>
                <tr style="background-color: #e8f5e9; font-weight: bold;">
                    <td colspan="2">TỔNG CỘNG</td>
                    <td class="text-right"><?php echo formatNumber($total_receipts); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_qty); ?></td>
                    <td class="text-right"><?php echo formatNumber($total_revenue); ?></td>
                </tr>
            </table>
            <?php
        }
        ?>
    </body>
    </html>
    <?php
    exit();
}
?>
