<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$error = '';
$success = '';
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$token_valid = false;

if ($token !== '') {
    try {
        ensurePasswordResetTable();
        $db = getDB();
        $token_hash = hash('sha256', $token);
        $stmt = $db->prepare("SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.username
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token_hash = ?
            LIMIT 1");
        $stmt->execute([$token_hash]);
        $reset = $stmt->fetch();

        if ($reset && empty($reset['used_at']) && strtotime($reset['expires_at']) > time()) {
            $token_valid = true;
        } else {
            $error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn';
        }
    } catch (Exception $e) {
        logSystemError('Reset password token verify failed', ['error' => $e->getMessage()]);
        $error = 'Không thể xác thực liên kết đặt lại mật khẩu';
    }
} else {
    $error = 'Thiếu mã đặt lại mật khẩu';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu mới phải có ít nhất 8 ký tự';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $reset['user_id']]);

            $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
            $stmt->execute([$reset['id']]);

            $db->commit();

            $success = 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay.';
            $token_valid = false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            logSystemError('Reset password update failed', ['error' => $e->getMessage(), 'user_id' => $reset['user_id'] ?? null]);
            $error = 'Không thể cập nhật mật khẩu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Quản Lý Kho Hàng</title>
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
            <h4 class="mb-2"><i class="bi bi-shield-lock"></i> Đặt lại mật khẩu</h4>
            <p class="text-muted mb-4">Nhập mật khẩu mới cho tài khoản của bạn.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="btn btn-primary w-100">Đăng nhập</a>
            <?php elseif ($token_valid): ?>
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" class="form-control" name="password" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                </div>
                <div class="d-grid mb-3">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-circle"></i> Cập nhật mật khẩu
                    </button>
                </div>
            </form>
            <a href="login.php">Quay lại đăng nhập</a>
            <?php else: ?>
            <a href="forgot_password.php" class="btn btn-outline-primary w-100">Tạo liên kết mới</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
