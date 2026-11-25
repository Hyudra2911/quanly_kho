<?php
$page_title = 'Quản lý hàng hóa';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$search = $_GET['search'] ?? '';
$group_id = $_GET['group_id'] ?? '';

$query = "SELECT i.*, ig.name as group_name 
    FROM items i 
    LEFT JOIN item_groups ig ON i.group_id = ig.id 
    WHERE 1=1";

$params = [];

if ($search) {
    $query .= " AND (i.code LIKE ? OR i.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($group_id) {
    $query .= " AND i.group_id = ?";
    $params[] = $group_id;
}

$query .= " ORDER BY i.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Lấy danh sách nhóm hàng
$stmt = $db->query("SELECT * FROM item_groups ORDER BY name");
$groups = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-box"></i> Quản lý hàng hóa</h1>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách hàng hóa</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm mới
            </a>
        </div>
        <div class="card-body">
            <!-- Form tìm kiếm -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm mã/tên hàng..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="group_id">
                            <option value="">Tất cả nhóm hàng</option>
                            <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['id']; ?>" <?php echo $group_id == $group['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group['name']); ?>
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
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã hàng</th>
                            <th>Tên hàng</th>
                            <th>Nhóm hàng</th>
                            <th>Đơn vị</th>
                            <th>Hạn sử dụng</th>
                            <th>Tồn tối thiểu</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có dữ liệu</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['code']); ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['group_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                            <td>
                                <?php if ($item['has_expiry']): ?>
                                    <span class="badge bg-info">Có</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Không</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatNumber($item['min_stock']); ?></td>
                            <td>
                                <?php if ($item['status']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ngừng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info btn-action" title="Xem">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning btn-action" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hàng hóa này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
