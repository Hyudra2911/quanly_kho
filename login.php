<?php
require_once 'config/config.php';

// Nếu đã đăng nhập, chuyển về dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$error = '';
$max_attempts = 5;
$lock_seconds = 300;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($_SESSION['login_lock_until']) && $_SESSION['login_lock_until'] > time()) {
        $remaining = (int)($_SESSION['login_lock_until'] - time());
        $error = 'Tài khoản đang bị khóa tạm thời. Vui lòng thử lại sau ' . ceil($remaining / 60) . ' phút';
    } elseif (!empty($_SESSION['login_lock_until']) && $_SESSION['login_lock_until'] <= time()) {
        unset($_SESSION['login_lock_until'], $_SESSION['login_attempts']);
    }
    
    if (empty($error) && !verifyCSRFToken($csrf_token)) {
        $error = 'Phiên làm việc không hợp lệ, vui lòng thử lại';
    } elseif (empty($error) && (empty($username) || empty($password))) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (empty($error)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            logActivity('login', 'users', $user['id'], 'Đăng nhập hệ thống');
            
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        } else {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['login_lock_until'] = time() + $lock_seconds;
                $error = 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút';
            } else {
                $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng. Còn ' . $remaining_attempts . ' lần thử';
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
    <title>Đăng nhập - Quản Lý Kho Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px;
        }
        .auth-shell {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        }
        .auth-brand {
            background: linear-gradient(160deg, #0d6efd 0%, #3d8bfd 100%);
            color: #fff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand-icon {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 20px;
        }
        .auth-form {
            padding: 40px;
        }
        .auth-form .form-control {
            height: 46px;
            border-radius: 12px;
        }
        .btn-login {
            height: 46px;
            border-radius: 12px;
            font-weight: 600;
        }
        .auth-links a {
            text-decoration: none;
            font-weight: 500;
        }
        @media (max-width: 991.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 560px;
            }
            .auth-brand {
                padding: 28px;
            }
            .auth-form {
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="brand-icon"><i class="bi bi-box-seam"></i></div>
            <h2 class="mb-3">Quản Lý Kho Hàng</h2>
            <p class="mb-2">Theo dõi nhập xuất, tồn kho và phê duyệt chứng từ trên một hệ thống tập trung.</p>
            <div class="small opacity-75">Đăng nhập để tiếp tục làm việc.</div>
        </div>

        <div class="auth-form">
            <h4 class="mb-1">Đăng nhập</h4>
            <p class="text-muted mb-4">Nhập thông tin tài khoản của bạn</p>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </button>
                </div>
            </form>

            <div class="d-flex justify-content-between auth-links">
                <a href="forgot_password.php"><i class="bi bi-key"></i> Quên mật khẩu?</a>
                <a href="register.php"><i class="bi bi-person-plus"></i> Đăng ký</a>
            </div>

            <hr class="my-4">
            <div class="text-muted small">
                <div><i class="bi bi-shield-check"></i> Hệ thống có bảo vệ CSRF và giới hạn đăng nhập sai.</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
