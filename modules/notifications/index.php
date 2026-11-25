<?php
$page_title = 'Thông báo';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

// Đánh dấu đã đọc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['read'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Yêu cầu không hợp lệ';
        header('Location: index.php');
        exit();
    }

    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['read'], $_SESSION['user_id']]);
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-bell"></i> Thông báo</h1>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
            <p class="text-muted text-center">Không có thông báo</p>
            <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notif): ?>
                <div class="list-group-item <?php echo !$notif['is_read'] ? 'list-group-item-primary' : ''; ?>">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                        <small><?php echo formatDateTime($notif['created_at']); ?></small>
                    </div>
                    <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                    <?php if (!$notif['is_read']): ?>
                    <form method="POST" class="mt-2">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="read" value="<?php echo $notif['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-primary">Đánh dấu đã đọc</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
