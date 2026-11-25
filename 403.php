<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 640px;">
            <div class="card-body p-5 text-center">
                <h1 class="display-4 text-danger">403</h1>
                <h4 class="mb-3">Không có quyền truy cập</h4>
                <p class="text-muted">Bạn không có quyền thực hiện thao tác này.</p>
                <a href="login.php" class="btn btn-primary">Về trang đăng nhập</a>
            </div>
        </div>
    </div>
</body>
</html>
