<?php
$page_title = 'Thêm nhà cung cấp';
require_once '../../config/config.php';
checkRole(['admin', 'quanly_kho']);

$db = getDB();

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
        $stmt = $db->prepare("SELECT id FROM suppliers WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Mã nhà cung cấp đã tồn tại';
        } else {
            $stmt = $db->prepare("INSERT INTO suppliers (code, name, contact_person, phone, email, address, tax_code, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $contact_person, $phone, $email, $address, $tax_code, $notes, $status]);
            
            logActivity('create', 'suppliers', $db->lastInsertId(), "Thêm nhà cung cấp: $name");
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
            <h5 class="mb-0">Thêm nhà cung cấp</h5>
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
                            <input type="text" class="form-control" name="code" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Người liên hệ</label>
                            <input type="text" class="form-control" name="contact_person">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" name="tax_code">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea class="form-control" name="address" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                        <label class="form-check-label">Hoạt động</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Thêm nhà cung cấp</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
