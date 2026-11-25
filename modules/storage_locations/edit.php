<?php
require_once '../../config/config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM storage_locations WHERE id = ?");
$stmt->execute([$id]);
$location = $stmt->fetch();

if (!$location) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $shelf = trim($_POST['shelf'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    
    $stmt = $db->prepare("UPDATE storage_locations SET code = ?, name = ?, area = ?, shelf = ?, room = ?, description = ?, status = ? WHERE id = ?");
    $stmt->execute([$code, $name, $area, $shelf, $room, $description, $status, $id]);
    logActivity('update', 'storage_locations', $id, "Cập nhật vị trí: $code");
    header('Location: index.php');
    exit();
}

$page_title = 'Sửa vị trí lưu trữ';
include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã vị trí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required value="<?php echo htmlspecialchars($location['code']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên vị trí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($location['name']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Khu vực</label>
                            <input type="text" class="form-control" name="area" value="<?php echo htmlspecialchars($location['area'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Kệ</label>
                            <input type="text" class="form-control" name="shelf" value="<?php echo htmlspecialchars($location['shelf'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phòng</label>
                            <input type="text" class="form-control" name="room" value="<?php echo htmlspecialchars($location['room'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="description" rows="2"><?php echo htmlspecialchars($location['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" <?php echo $location['status'] ? 'checked' : ''; ?>>
                        <label class="form-check-label">Hoạt động</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
