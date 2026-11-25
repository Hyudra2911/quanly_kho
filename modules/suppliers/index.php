<?php
$page_title = 'Quản lý nhà cung cấp';
require_once '../../config/config.php';
checkRole(['admin', 'quanly_kho']);

$db = getDB();
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM suppliers WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR code LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-truck"></i> Quản lý nhà cung cấp</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Thêm nhà cung cấp
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên, mã, SĐT, email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Tên nhà cung cấp</th>
                            <th>Người liên hệ</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['code']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['contact_person'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></td>
                            <td>
                                <?php if ($supplier['status']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ngừng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                    <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
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
