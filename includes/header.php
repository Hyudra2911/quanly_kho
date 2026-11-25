<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Quản Lý Kho Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'has-sidebar' : ''; ?>">
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sidebar-nav">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none" type="button" id="mobileMenuToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <i class="bi bi-box-seam"></i> <span class="d-none d-sm-inline">Quản Lý Kho Hàng</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="goodsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-box"></i> Hàng Hóa
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/items/index.php">Danh mục hàng hóa</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/item_groups/index.php">Nhóm hàng hóa</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="warehouseDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> Kho Hàng
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/warehouses/index.php">Danh sách kho</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/storage_locations/index.php">Vị trí lưu trữ</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="importExportDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-arrow-left-right"></i> Nhập Xuất
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/import_receipts/index.php">Phiếu nhập kho</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/export_receipts/index.php">Phiếu xuất kho</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/inventory_adjustments/index.php">Điều chỉnh tồn kho</a></li>
                        </ul>
                    </li>
                    <?php if (in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="partnersDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> Đối Tác
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/suppliers/index.php">Nhà cung cấp</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/customers/index.php">Khách hàng</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/internal_transfers/index.php">
                            <i class="bi bi-arrow-repeat"></i> Chuyển Kho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/inventory/index.php">
                            <i class="bi bi-clipboard-data"></i> Tồn Kho
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/users/index.php">
                            <i class="bi bi-people-fill"></i> Quản Lý Nhân Sự
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>modules/reports/index.php">
                            <i class="bi bi-graph-up"></i> Báo Cáo
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <!-- 'Người Dùng' and 'Sao Lưu' moved into admin dropdown to keep admin block at bottom -->
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/profile/index.php">
                                <i class="bi bi-person"></i> Hồ sơ cá nhân
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/notifications/index.php">
                                <i class="bi bi-bell"></i> Thông báo
                                <?php
                                $db = getDB();
                                $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
                                $stmt->execute([$_SESSION['user_id']]);
                                $unread = $stmt->fetch()['count'];
                                if ($unread > 0):
                                ?>
                                <span class="badge bg-danger"><?php echo $unread; ?></span>
                                <?php endif; ?>
                            </a></li>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/activity_logs/index.php">
                                <i class="bi bi-clock-history"></i> Lịch sử hoạt động
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/backup/index.php">
                                <i class="bi bi-database"></i> Sao Lưu
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i> Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Sidebar Menu -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>
        <div class="mobile-sidebar-content">
            <div class="mobile-sidebar-header">
                <h5><i class="bi bi-box-seam"></i> Quản Lý Kho Hàng</h5>
                <button class="btn-close-mobile" id="closeMobileMenu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="mobile-sidebar-body">
                <ul class="mobile-menu-list">
                    <li>
                        <a href="<?php echo BASE_URL; ?>index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="mobile-menu-dropdown">
                        <a href="#" class="mobile-menu-toggle">
                            <i class="bi bi-box"></i> Hàng Hóa <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="mobile-submenu">
                            <li><a href="<?php echo BASE_URL; ?>modules/items/index.php">Danh mục hàng hóa</a></li>
                            <li><a href="<?php echo BASE_URL; ?>modules/item_groups/index.php">Nhóm hàng hóa</a></li>
                        </ul>
                    </li>
                    <li class="mobile-menu-dropdown">
                        <a href="#" class="mobile-menu-toggle">
                            <i class="bi bi-building"></i> Kho Hàng <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="mobile-submenu">
                            <li><a href="<?php echo BASE_URL; ?>modules/warehouses/index.php">Danh sách kho</a></li>
                            <li><a href="<?php echo BASE_URL; ?>modules/storage_locations/index.php">Vị trí lưu trữ</a></li>
                        </ul>
                    </li>
                    <li class="mobile-menu-dropdown">
                        <a href="#" class="mobile-menu-toggle">
                            <i class="bi bi-arrow-left-right"></i> Nhập Xuất <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="mobile-submenu">
                            <li><a href="<?php echo BASE_URL; ?>modules/import_receipts/index.php">Phiếu nhập kho</a></li>
                            <li><a href="<?php echo BASE_URL; ?>modules/export_receipts/index.php">Phiếu xuất kho</a></li>
                            <li><a href="<?php echo BASE_URL; ?>modules/inventory_adjustments/index.php">Điều chỉnh tồn kho</a></li>
                        </ul>
                    </li>
                    <?php if (in_array($_SESSION['role'], ['admin', 'quanly_kho'])): ?>
                    <li class="mobile-menu-dropdown">
                        <a href="#" class="mobile-menu-toggle">
                            <i class="bi bi-building"></i> Đối Tác <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="mobile-submenu">
                            <li><a href="<?php echo BASE_URL; ?>modules/suppliers/index.php">Nhà cung cấp</a></li>
                            <li><a href="<?php echo BASE_URL; ?>modules/customers/index.php">Khách hàng</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/internal_transfers/index.php">
                            <i class="bi bi-arrow-repeat"></i> Chuyển Kho
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/inventory/index.php">
                            <i class="bi bi-clipboard-data"></i> Tồn Kho
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/users/index.php">
                            <i class="bi bi-people-fill"></i> Quản Lý Nhân Sự
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/reports/index.php">
                            <i class="bi bi-graph-up"></i> Báo Cáo
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/users/index.php">
                            <i class="bi bi-people"></i> Người Dùng
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/backup/index.php">
                            <i class="bi bi-database"></i> Sao Lưu
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="mobile-menu-divider"></li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/profile/index.php">
                            <i class="bi bi-person"></i> Hồ sơ cá nhân
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/notifications/index.php">
                            <i class="bi bi-bell"></i> Thông báo
                            <?php
                            $db = getDB();
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
                            $stmt->execute([$_SESSION['user_id']]);
                            $unread = $stmt->fetch()['count'];
                            if ($unread > 0):
                            ?>
                            <span class="badge bg-danger float-end"><?php echo $unread; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>modules/activity_logs/index.php">
                            <i class="bi bi-clock-history"></i> Lịch sử hoạt động
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
