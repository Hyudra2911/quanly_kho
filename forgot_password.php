<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$message = '';
$error = '';
$show_reset_form = false;
$verified_email = '';

if (!empty($_SESSION['forgot_reset_user_id']) && !empty($_SESSION['forgot_reset_expires']) && $_SESSION['forgot_reset_expires'] > time()) {
    $show_reset_form = true;
    $verified_email = $_SESSION['forgot_reset_email'] ?? '';
} else {
    unset($_SESSION['forgot_reset_user_id'], $_SESSION['forgot_reset_email'], $_SESSION['forgot_reset_expires']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify_email';
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại';
    } elseif ($action === 'verify_email') {
        if ($email === '') {
            $error = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ';
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT id, email FROM users WHERE status = 1 AND email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['forgot_reset_user_id'] = $user['id'];
                    $_SESSION['forgot_reset_email'] = $user['email'];
                    $_SESSION['forgot_reset_expires'] = time() + 600;
                    $show_reset_form = true;
                    $verified_email = $user['email'];
                    $message = 'Email hợp lệ. Bạn có thể đặt mật khẩu mới.';
                } else {
                    $error = 'Email không tồn tại trong hệ thống';
                    unset($_SESSION['forgot_reset_user_id'], $_SESSION['forgot_reset_email'], $_SESSION['forgot_reset_expires']);
                }
            } catch (Exception $e) {
                logSystemError('Forgot password verify email failed', ['error' => $e->getMessage(), 'email' => $email]);
                $error = 'Không thể xử lý yêu cầu lúc này';
            }
        }
    } elseif ($action === 'reset_password') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($_SESSION['forgot_reset_user_id']) || empty($_SESSION['forgot_reset_expires']) || $_SESSION['forgot_reset_expires'] <= time()) {
            $error = 'Phiên xác thực email đã hết hạn, vui lòng xác thực lại email';
            $show_reset_form = false;
            unset($_SESSION['forgot_reset_user_id'], $_SESSION['forgot_reset_email'], $_SESSION['forgot_reset_expires']);
        } elseif (strlen($new_password) < 8) {
            $error = 'Mật khẩu mới phải có ít nhất 8 ký tự';
            $show_reset_form = true;
            $verified_email = $_SESSION['forgot_reset_email'] ?? '';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp';
            $show_reset_form = true;
            $verified_email = $_SESSION['forgot_reset_email'] ?? '';
        } else {
            try {
                $db = getDB();
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['forgot_reset_user_id']]);

                logActivity('update', 'users', $_SESSION['forgot_reset_user_id'], 'Đổi mật khẩu qua trang quên mật khẩu');
                $message = 'Đổi mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.';
                $show_reset_form = false;
                unset($_SESSION['forgot_reset_user_id'], $_SESSION['forgot_reset_email'], $_SESSION['forgot_reset_expires']);
            } catch (Exception $e) {
                logSystemError('Forgot password reset failed', ['error' => $e->getMessage(), 'email' => $_SESSION['forgot_reset_email'] ?? null]);
                $error = 'Không thể cập nhật mật khẩu lúc này';
                $show_reset_form = true;
                $verified_email = $_SESSION['forgot_reset_email'] ?? '';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Quản Lý Kho Hàng</title>
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
            max-width: 520px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
            border: 0;
        }
    </style>
</head>
<body>
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <h4 class="mb-2"><i class="bi bi-key"></i> Quên mật khẩu</h4>
            <p class="text-muted mb-4">Nhập email để xác thực và đổi mật khẩu.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!$show_reset_form): ?>
            <form method="POST" class="mb-3">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="verify_email">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Xác thực email
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-info">
                Đã xác thực email: <strong><?php echo htmlspecialchars($verified_email); ?></strong>
            </div>
            <form method="POST" class="mb-3">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="reset_password">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="new_password" minlength="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-circle"></i> Đổi mật khẩu
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <div class="d-flex justify-content-between">
                <a href="login.php">Quay lại đăng nhập</a>
                <a href="register.php">Đăng ký tài khoản</a>
            </div>
        </div>
    </div>
</body>
</html>
