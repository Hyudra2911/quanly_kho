<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại';
    } elseif ($username === '' || $full_name === '' || $password === '' || $confirm_password === '') {
        $error = 'Vui lòng nhập đầy đủ các trường bắt buộc';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $username)) {
        $error = 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch dưới và dài 4-30 ký tự';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        try {
            $db = getDB();

            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Tên đăng nhập đã tồn tại';
            }

            if ($error === '' && $email !== '') {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email đã được sử dụng';
                }
            }

            if ($error === '') {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, password, full_name, email, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, 'nhanvien_nhapkho', 1)");
                $stmt->execute([$username, $hashed_password, $full_name, $email ?: null, $phone ?: null]);

                $success = 'Đăng ký thành công. Bạn có thể đăng nhập ngay.';
            }
        } catch (Exception $e) {
            logSystemError('Register failed', ['error' => $e->getMessage(), 'username' => $username, 'email' => $email]);
            $error = 'Không thể tạo tài khoản lúc này';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản Lý Kho Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card {
            width: 100%;
            max-width: 620px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
            border: 0;
        }
    </style>
</head>
<body>
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <h4 class="mb-2"><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h4>
            <p class="text-muted mb-4">Tạo tài khoản để sử dụng hệ thống quản lý kho.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="btn btn-primary w-100">Đăng nhập</a>
            <?php else: ?>
            <form method="POST">
                <?php echo csrfField(); ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" minlength="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-circle"></i> Tạo tài khoản
                    </button>
                </div>
            </form>
            <a href="login.php">Đã có tài khoản? Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
