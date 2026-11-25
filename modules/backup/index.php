<?php
$page_title = 'Sao lưu và khôi phục';
require_once '../../config/config.php';
checkRole(['admin']);

$db = getDB();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yêu cầu không hợp lệ';
    } elseif ($_POST['action'] == 'backup') {
        // Tạo backup
        $backup_file = 'backup_' . date('YmdHis') . '.sql';
        $backup_path = __DIR__ . '/../../backups/' . $backup_file;
        
        // Tạo thư mục nếu chưa có
        if (!is_dir(__DIR__ . '/../../backups')) {
            mkdir(__DIR__ . '/../../backups', 0777, true);
        }
        
        // Lấy danh sách bảng
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            // Drop table
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // Create table
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $create = $stmt->fetch();
            $output .= $create['Create Table'] . ";\n\n";
            
            // Insert data
            $stmt = $db->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll();
            
            if (!empty($rows)) {
                $output .= "INSERT INTO `$table` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $row_values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $row_values[] = 'NULL';
                        } else {
                            $row_values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(",", $row_values) . ")";
                }
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backup_path, $output);
        
        logActivity('backup', 'database', null, "Tạo backup: $backup_file");
        $success = "Đã tạo backup thành công: $backup_file";
    }
}

// Lấy danh sách backup
$backups = [];
$backup_dir = __DIR__ . '/../../backups';
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '/backup_*.sql');
    rsort($files);
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-database"></i> Sao lưu và khôi phục</h1>
    </div>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-download"></i> Tạo sao lưu</h5>
                </div>
                <div class="card-body">
                    <p>Tạo file backup toàn bộ database hiện tại.</p>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="backup">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc chắn muốn tạo backup?')">
                            <i class="bi bi-download"></i> Tạo backup ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Hướng dẫn khôi phục</h5>
                </div>
                <div class="card-body">
                    <p>Để khôi phục database từ file backup:</p>
                    <ol>
                        <li>Tải file backup về máy</li>
                        <li>Mở phpMyAdmin hoặc MySQL command line</li>
                        <li>Chọn database <code>quanly_kho</code></li>
                        <li>Import file backup đã tải</li>
                    </ol>
                    <p class="text-danger"><strong>Lưu ý:</strong> Khôi phục sẽ ghi đè toàn bộ dữ liệu hiện tại!</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-folder"></i> Danh sách backup</h5>
        </div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
            <p class="text-muted">Chưa có file backup nào</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tên file</th>
                            <th>Kích thước</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                            <td><?php echo formatDateTime($backup['date']); ?></td>
                            <td>
                                <a href="download.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-download"></i> Tải về
                                </a>
                                <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa file backup này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
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

<?php include '../../includes/footer.php'; ?>
