<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 640px;">
            <div class="card-body p-5 text-center">
                <h1 class="display-4 text-warning">404</h1>
                <h4 class="mb-3">Không tìm thấy trang</h4>
                <p class="text-muted">Đường dẫn bạn truy cập không tồn tại hoặc đã thay đổi.</p>
                <a href="index.php" class="btn btn-primary">Về trang chủ</a>
            </div>
        </div>
    </div>
</body>
</html>
