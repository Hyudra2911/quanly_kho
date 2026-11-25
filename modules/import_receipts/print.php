<?php
$page_title = 'In phiếu nhập';
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT ir.*, w.name as warehouse_name, w.address as warehouse_address, u1.full_name as creator_name, u2.full_name as approver_name 
    FROM import_receipts ir 
    JOIN warehouses w ON ir.warehouse_id = w.id 
    JOIN users u1 ON ir.created_by = u1.id 
    LEFT JOIN users u2 ON ir.approved_by = u2.id 
    WHERE ir.id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT ird.*, i.code as item_code, i.name as item_name, i.unit, sl.name as location_name 
    FROM import_receipt_details ird 
    JOIN items i ON ird.item_id = i.id 
    LEFT JOIN storage_locations sl ON ird.storage_location_id = sl.id 
    WHERE ird.receipt_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu nhập kho - <?php echo htmlspecialchars($receipt['receipt_code']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table th {
            text-align: left;
            padding: 5px;
            width: 150px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .details-table th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">In / Lưu PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px;">Đóng</button>
    </div>
    
    <div class="header">
        <h1>PHIẾU NHẬP KHO</h1>
        <p>Mã phiếu: <strong><?php echo htmlspecialchars($receipt['receipt_code']); ?></strong></p>
    </div>
    
    <table class="info-table">
        <tr>
            <th>Ngày nhập:</th>
            <td><?php echo formatDate($receipt['receipt_date']); ?></td>
            <th>Kho:</th>
            <td><?php echo htmlspecialchars($receipt['warehouse_name']); ?></td>
        </tr>
        <tr>
            <th>Địa chỉ kho:</th>
            <td><?php echo htmlspecialchars($receipt['warehouse_address'] ?? ''); ?></td>
            <th>Nhà cung cấp:</th>
            <td><?php echo htmlspecialchars($receipt['supplier'] ?? ''); ?></td>
        </tr>
        <tr>
            <th>Người tạo:</th>
            <td><?php echo htmlspecialchars($receipt['creator_name']); ?></td>
            <th>Người duyệt:</th>
            <td><?php echo htmlspecialchars($receipt['approver_name'] ?? 'Chưa duyệt'); ?></td>
        </tr>
        <tr>
            <th>Trạng thái:</th>
            <td>
                <strong><?php 
                    echo $receipt['status'] == 'approved' ? 'ĐÃ DUYỆT' : 
                        ($receipt['status'] == 'rejected' ? 'TỪ CHỐI' : 'CHỜ DUYỆT'); 
                ?></strong>
            </td>
            <th>Ghi chú:</th>
            <td><?php echo nl2br(htmlspecialchars($receipt['notes'] ?? '')); ?></td>
        </tr>
    </table>
    
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 10%;">Mã hàng</th>
                <th style="width: 25%;">Tên hàng</th>
                <th style="width: 8%;">Số lượng</th>
                <th style="width: 8%;">Đơn vị</th>
                <th style="width: 10%;">Mã lô</th>
                <th style="width: 12%;">Đơn giá</th>
                <th style="width: 12%;">Thành tiền</th>
                <th style="width: 10%;">Vị trí</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            $total = 0;
            foreach ($details as $d): 
                $amount = $d['quantity'] * $d['unit_price'];
                $total += $amount;
            ?>
            <tr>
                <td class="text-center"><?php echo $stt++; ?></td>
                <td><?php echo htmlspecialchars($d['item_code']); ?></td>
                <td><?php echo htmlspecialchars($d['item_name']); ?></td>
                <td class="text-right"><?php echo formatNumber($d['quantity']); ?></td>
                <td><?php echo htmlspecialchars($d['unit']); ?></td>
                <td><?php echo htmlspecialchars($d['batch_code'] ?? ''); ?></td>
                <td class="text-right"><?php echo formatNumber($d['unit_price']); ?></td>
                <td class="text-right"><?php echo formatNumber($amount); ?></td>
                <td><?php echo htmlspecialchars($d['location_name'] ?? 'N/A'); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="7" class="text-right"><strong>TỔNG CỘNG:</strong></td>
                <td class="text-right"><strong><?php echo formatNumber($total); ?></strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
    <div class="signature">
        <div class="signature-box">
            <p><strong>Người lập phiếu</strong></p>
            <p style="margin-top: 50px;"><?php echo htmlspecialchars($receipt['creator_name']); ?></p>
        </div>
        <div class="signature-box">
            <p><strong>Người duyệt</strong></p>
            <p style="margin-top: 50px;"><?php echo htmlspecialchars($receipt['approver_name'] ?? ''); ?></p>
        </div>
    </div>
</body>
</html>
