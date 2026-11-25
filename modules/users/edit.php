<?php
require_once '../../config/config.php';
checkRole(['admin']);

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'nhanvien_nhapkho';
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (empty($username) || empty($full_name)) {
        $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Tên đăng nhập đã tồn tại';
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$username, $hashed_password, $full_name, $email, $phone, $role, $status, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$username, $full_name, $email, $phone, $role, $status, $id]);
            }
            logActivity('update', 'users', $id, "Cập nhật người dùng: $username");
            header('Location: index.php?success=1');
            exit();
        }
    }
}

$page_title = 'Sửa người dùng';
include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                <?php foreach (ROLES as $key => $name): ?>
                                <option value="<?php echo $key; ?>" <?php echo $user['role'] == $key ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" <?php echo $user['status'] ? 'checked' : ''; ?>>
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
