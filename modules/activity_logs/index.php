<?php
$page_title = 'Lịch sử hoạt động';
require_once '../../config/config.php';
checkLogin();

$db = getDB();

// Phân quyền: chỉ admin mới xem được tất cả, người khác chỉ xem của mình
$is_admin = $_SESSION['role'] == 'admin';

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$user_filter = $_GET['user_id'] ?? '';
$action_filter = $_GET['action'] ?? '';

$query = "SELECT al.*, u.username, u.full_name 
    FROM activity_logs al 
    JOIN users u ON al.user_id = u.id 
    WHERE 1=1";
$params = [];

if (!$is_admin) {
    $query .= " AND al.user_id = ?";
    $params[] = $_SESSION['user_id'];
}

if ($search) {
    $query .= " AND (al.description LIKE ? OR al.action LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($user_filter && $is_admin) {
    $query .= " AND al.user_id = ?";
    $params[] = $user_filter;
}

if ($action_filter) {
    $query .= " AND al.action = ?";
    $params[] = $action_filter;
}

// Đếm tổng số
$count_query = "SELECT COUNT(*) as total FROM ($query) as count_query";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

// Lấy dữ liệu
$query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Lấy danh sách users cho filter (chỉ admin)
$users = [];
if ($is_admin) {
    $stmt = $db->query("SELECT id, username, full_name FROM users ORDER BY full_name");
    $users = $stmt->fetchAll();
}

// Lấy danh sách actions
$stmt = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
$actions = $stmt->fetchAll(PDO::FETCH_COLUMN);

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-clock-history"></i> Lịch sử hoạt động</h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <?php if ($is_admin): ?>
                <div class="col-md-2">
                    <select class="form-select" name="user_id">
                        <option value="">Tất cả người dùng</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $user_filter == $u['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <select class="form-select" name="action">
                        <option value="">Tất cả hành động</option>
                        <?php foreach ($actions as $act): ?>
                        <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $action_filter == $act ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="index.php" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Làm mới
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <?php if ($is_admin): ?>
                            <th>Người dùng</th>
                            <?php endif; ?>
                            <th>Hành động</th>
                            <th>Bảng</th>
                            <th>Mô tả</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="<?php echo $is_admin ? 6 : 5; ?>" class="text-center text-muted">
                                Không có dữ liệu
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo formatDateTime($log['created_at']); ?></td>
                            <?php if ($is_admin): ?>
                            <td>
                                <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($log['full_name']); ?></small>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span class="badge bg-<?php 
                                    echo in_array($log['action'], ['create', 'login']) ? 'success' : 
                                        (in_array($log['action'], ['delete', 'reject']) ? 'danger' : 'info'); 
                                ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($log['table_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($log['description'] ?? ''); ?></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>">Trước</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>">Sau</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
