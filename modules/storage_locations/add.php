<?php
$page_title = 'Thêm vị trí lưu trữ';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$warehouse_id = $_GET['warehouse_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $warehouse_id = $_POST['warehouse_id'];
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $shelf = trim($_POST['shelf'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($warehouse_id) || empty($code) || empty($name)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $stmt = $db->prepare("INSERT INTO storage_locations (warehouse_id, code, name, area, shelf, room, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$warehouse_id, $code, $name, $area, $shelf, $room, $description])) {
            logActivity('create', 'storage_locations', $db->lastInsertId(), "Thêm vị trí: $code");
            header('Location: index.php?warehouse_id=' . $warehouse_id);
            exit();
        }
    }
}

$stmt = $db->query("SELECT * FROM warehouses WHERE status = 1 ORDER BY name");
$warehouses = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kho <span class="text-danger">*</span></label>
                    <select class="form-select" name="warehouse_id" required>
                        <option value="">-- Chọn kho --</option>
                        <?php foreach ($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>" <?php echo ($warehouse_id == $wh['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($wh['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã vị trí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên vị trí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Khu vực</label>
                            <input type="text" class="form-control" name="area">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Kệ</label>
                            <input type="text" class="form-control" name="shelf">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phòng</label>
                            <input type="text" class="form-control" name="room">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
