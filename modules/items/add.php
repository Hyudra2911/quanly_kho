<?php
$page_title = 'Thêm hàng hóa';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $group_id = $_POST['group_id'] ?? null;
    $unit = trim($_POST['unit'] ?? '');
    $has_expiry = isset($_POST['has_expiry']) ? 1 : 0;
    $min_stock = intval($_POST['min_stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (empty($code) || empty($name) || empty($unit)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        // Kiểm tra mã hàng đã tồn tại
        $stmt = $db->prepare("SELECT id FROM items WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $error = 'Mã hàng đã tồn tại';
        } else {
            $stmt = $db->prepare("INSERT INTO items (code, name, group_id, unit, has_expiry, min_stock, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$code, $name, $group_id ?: null, $unit, $has_expiry, $min_stock, $description])) {
                $item_id = $db->lastInsertId();
                logActivity('create', 'items', $item_id, "Thêm hàng hóa: $code - $name");
                $success = 'Thêm hàng hóa thành công';
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi thêm hàng hóa';
            }
        }
    }
}

// Lấy danh sách nhóm hàng
$stmt = $db->query("SELECT * FROM item_groups ORDER BY name");
$groups = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-plus-circle"></i> Thêm hàng hóa</h1>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="code" class="form-label">Mã hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" required value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Nhóm hàng</label>
                            <select class="form-select" id="group_id" name="group_id">
                                <option value="">-- Chọn nhóm hàng --</option>
                                <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo (isset($_POST['group_id']) && $_POST['group_id'] == $group['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="unit" class="form-label">Đơn vị tính <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit" name="unit" required value="<?php echo htmlspecialchars($_POST['unit'] ?? ''); ?>" placeholder="VD: cái, kg, thùng...">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_expiry" name="has_expiry" <?php echo (isset($_POST['has_expiry'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="has_expiry">
                                    Có hạn sử dụng
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="min_stock" class="form-label">Tồn kho tối thiểu</label>
                            <input type="number" class="form-control number-input" id="min_stock" name="min_stock" min="0" value="<?php echo htmlspecialchars($_POST['min_stock'] ?? '0'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
