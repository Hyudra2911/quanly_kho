<?php
$page_title = 'Sửa nhà cung cấp';
require_once '../../config/config.php';
checkRole(['admin', 'quanly_kho']);

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $tax_code = trim($_POST['tax_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (empty($code) || empty($name)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        $stmt = $db->prepare("SELECT id FROM suppliers WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Mã nhà cung cấp đã tồn tại';
        } else {
            $stmt = $db->prepare("UPDATE suppliers SET code = ?, name = ?, contact_person = ?, phone = ?, email = ?, address = ?, tax_code = ?, notes = ?, status = ? WHERE id = ?");
            $stmt->execute([$code, $name, $contact_person, $phone, $email, $address, $tax_code, $notes, $status, $id]);
            
            logActivity('update', 'suppliers', $id, "Cập nhật nhà cung cấp: $name");
            header('Location: index.php?success=1');
            exit();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Sửa nhà cung cấp</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required value="<?php echo htmlspecialchars($supplier['code']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($supplier['name']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Người liên hệ</label>
                            <input type="text" class="form-control" name="contact_person" value="<?php echo htmlspecialchars($supplier['contact_person'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($supplier['phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($supplier['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" name="tax_code" value="<?php echo htmlspecialchars($supplier['tax_code'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($supplier['notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" <?php echo $supplier['status'] ? 'checked' : ''; ?>>
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
