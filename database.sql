-- Database: quanly_kho
-- Tạo database và các bảng cho hệ thống quản lý kho hàng

CREATE DATABASE IF NOT EXISTS quanly_kho CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quanly_kho;

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'quanly_kho', 'nhanvien_nhapkho', 'ketoan') DEFAULT 'nhanvien_nhapkho',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng nhóm hàng hóa
CREATE TABLE IF NOT EXISTS item_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng hàng hóa
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    group_id INT,
    unit VARCHAR(50) NOT NULL,
    has_expiry TINYINT(1) DEFAULT 0,
    min_stock INT DEFAULT 0,
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES item_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng kho hàng
CREATE TABLE IF NOT EXISTS warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    manager_id INT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng vị trí lưu trữ
CREATE TABLE IF NOT EXISTS storage_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    area VARCHAR(100),
    shelf VARCHAR(100),
    room VARCHAR(100),
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_location (warehouse_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lô hàng
CREATE TABLE IF NOT EXISTS batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    batch_code VARCHAR(100) NOT NULL,
    import_date DATE NOT NULL,
    expiry_date DATE,
    initial_quantity INT NOT NULL DEFAULT 0,
    current_quantity INT NOT NULL DEFAULT 0,
    storage_location_id INT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (storage_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL,
    UNIQUE KEY unique_batch (item_id, batch_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phiếu nhập kho
CREATE TABLE IF NOT EXISTS import_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_code VARCHAR(50) UNIQUE NOT NULL,
    receipt_date DATE NOT NULL,
    warehouse_id INT NOT NULL,
    supplier VARCHAR(200),
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết phiếu nhập kho
CREATE TABLE IF NOT EXISTS import_receipt_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_code VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) DEFAULT 0,
    storage_location_id INT,
    expiry_date DATE,
    notes TEXT,
    FOREIGN KEY (receipt_id) REFERENCES import_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (storage_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phiếu xuất kho
CREATE TABLE IF NOT EXISTS export_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_code VARCHAR(50) UNIQUE NOT NULL,
    receipt_date DATE NOT NULL,
    warehouse_id INT NOT NULL,
    customer VARCHAR(200),
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết phiếu xuất kho
CREATE TABLE IF NOT EXISTS export_receipt_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (receipt_id) REFERENCES export_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chuyển kho nội bộ
CREATE TABLE IF NOT EXISTS internal_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transfer_code VARCHAR(50) UNIQUE NOT NULL,
    transfer_date DATE NOT NULL,
    from_warehouse_id INT NOT NULL,
    to_warehouse_id INT NOT NULL,
    from_location_id INT,
    to_location_id INT,
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (from_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (to_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết chuyển kho
CREATE TABLE IF NOT EXISTS internal_transfer_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT,
    quantity INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (transfer_id) REFERENCES internal_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng tồn kho (tính toán real-time)
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    storage_location_id INT,
    batch_id INT,
    quantity INT NOT NULL DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (storage_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL,
    UNIQUE KEY unique_inventory (item_id, warehouse_id, storage_location_id, batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng thông báo
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('low_stock', 'expiry_warning', 'approval_pending', 'transfer_incomplete', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng điều chỉnh tồn kho
CREATE TABLE IF NOT EXISTS inventory_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adjustment_code VARCHAR(50) UNIQUE NOT NULL,
    adjustment_date DATE NOT NULL,
    warehouse_id INT NOT NULL,
    reason ENUM('loss', 'damage', 'correction', 'theft', 'other') DEFAULT 'correction',
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết điều chỉnh tồn kho
CREATE TABLE IF NOT EXISTS inventory_adjustment_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adjustment_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT,
    current_quantity INT NOT NULL,
    adjusted_quantity INT NOT NULL,
    difference INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (adjustment_id) REFERENCES inventory_adjustments(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng nhà cung cấp
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    contact_person VARCHAR(100),
    payment_terms VARCHAR(200),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng khách hàng
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    contact_person VARCHAR(100),
    payment_terms VARCHAR(200),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lịch sử thao tác
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================
-- CHÈN DỮ LIỆU NGƯỜI DÙNG
-- ======================================================================
INSERT INTO users (username, password, full_name, email, phone, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin@example.com', '0912345678', 'admin'),
('quanly1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Quản Lý', 'quanly@example.com', '0901234567', 'quanly_kho'),
('nhanvien1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Nhân Viên', 'nhanvien1@example.com', '0902345678', 'nhanvien_nhapkho'),
('nhanvien2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn Nhân Viên 2', 'nhanvien2@example.com', '0903456789', 'nhanvien_nhapkho'),
('ketoan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Thị Kế Toán', 'ketoan@example.com', '0904567890', 'ketoan');

-- Password mặc định cho tất cả: "password"

-- ======================================================================
-- CHÈN DỮ LIỆU NHÓM HÀNG HÓA
-- ======================================================================
INSERT INTO item_groups (code, name, description) VALUES
('THUC_PHAM', 'Thực phẩm', 'Các sản phẩm thực phẩm tươi sống'),
('DO_UONG', 'Đồ uống', 'Nước uống và đồ uống có cồn'),
('DIEN_TU', 'Điện tử', 'Thiết bị điện tử và linh kiện'),
('MY_PHAM', 'Mỹ phẩm', 'Sản phẩm làm đẹp và chăm sóc'),
('THUOC', 'Thuốc', 'Thuốc và sản phẩm y tế'),
('VAN_PHONG', 'Văn phòng phẩm', 'Dụng cụ và vật tư văn phòng');

-- ======================================================================
-- CHÈN DỮ LIỆU KHO HÀNG
-- ======================================================================
INSERT INTO warehouses (code, name, address, manager_id, status) VALUES
('KHO_01', 'Kho chính TP.HCM', '123 Đường Nguyễn Huệ, Quận 1, TP.HCM', 2, 1),
('KHO_02', 'Kho phụ Hà Nội', '456 Đường Thái Hà, Quận 3, Hà Nội', 2, 1),
('KHO_03', 'Kho miền Trung Đà Nẵng', '789 Đường Bạch Đằng, Quận Hải Châu, Đà Nẵng', 2, 1);

-- ======================================================================
-- CHÈN DỮ LIỆU VỊ TRÍ LƯU TRỮ
-- ======================================================================
INSERT INTO storage_locations (warehouse_id, code, name, area, shelf, room, status) VALUES
(1, 'A1', 'Khu A - Kệ 1', 'Khu A', 'Kệ 1', 'Phòng 1', 1),
(1, 'A2', 'Khu A - Kệ 2', 'Khu A', 'Kệ 2', 'Phòng 1', 1),
(1, 'B1', 'Khu B - Kệ 1', 'Khu B', 'Kệ 1', 'Phòng 2', 1),
(2, 'C1', 'Khu C - Kệ 1', 'Khu C', 'Kệ 1', 'Phòng 1', 1),
(2, 'C2', 'Khu C - Kệ 2', 'Khu C', 'Kệ 2', 'Phòng 1', 1),
(3, 'D1', 'Khu D - Kệ 1', 'Khu D', 'Kệ 1', 'Phòng chính', 1);

-- ======================================================================
-- CHÈN DỮ LIỆU HÀNG HÓA
-- ======================================================================
INSERT INTO items (code, name, group_id, unit, has_expiry, min_stock, description, status) VALUES
('SP001', 'iPhone 14 Pro', 3, 'Cái', 0, 5, 'Điện thoại thông minh Apple iPhone 14 Pro 128GB', 1),
('SP002', 'Samsung Galaxy S23', 3, 'Cái', 0, 3, 'Điện thoại Samsung Galaxy S23 256GB', 1),
('SP003', 'Laptop Dell XPS 13', 3, 'Cái', 0, 2, 'Máy tính xách tay Dell XPS 13 FHD', 1),
('SP004', 'Sữa tươi Vinamilk', 1, 'Hộp', 1, 10, 'Sữa tươi tiệt trùng Vinamilk 1L', 1),
('SP005', 'Nước ngọt Coca Cola', 2, 'Thùng', 1, 15, 'Nước Coca Cola 330ml x 24 chai', 1),
('SP006', 'Nước cam Tropicana', 2, 'Thùng', 1, 10, 'Nước cam tự nhiên Tropicana 1L x 6 bao', 1),
('SP007', 'Kem dưỡng da', 4, 'Hộp', 1, 8, 'Kem dưỡng da ban đêm Olay', 1),
('SP008', 'Thuốc Panadol', 5, 'Hộp', 1, 25, 'Thuốc giảm đau hạ sốt Panadol 20 viên', 1),
('SP009', 'Giấy in A4', 6, 'Ream', 0, 50, 'Giấy in ấn A4 80gsm 500 tờ', 1),
('SP010', 'Bút bi Thiên Long', 6, 'Hộp', 0, 100, 'Bút bi xanh Thiên Long (12 cái/hộp)', 1);

-- ======================================================================
-- CHÈN DỮ LIỆU LÔ HÀNG
-- ======================================================================
INSERT INTO batches (item_id, batch_code, import_date, expiry_date, initial_quantity, current_quantity, storage_location_id, status) VALUES
(1, 'LOT20260101', '2026-01-10', NULL, 20, 18, 1, 1),
(2, 'LOT20260102', '2026-01-12', NULL, 15, 14, 2, 1),
(3, 'LOT20260103', '2026-01-15', NULL, 10, 9, 1, 1),
(4, 'LOT20260104', '2026-01-08', '2026-07-08', 100, 85, 1, 1),
(5, 'LOT20260105', '2026-01-10', '2026-12-31', 50, 45, 2, 1),
(6, 'LOT20260106', '2026-01-12', '2026-06-30', 30, 28, 4, 1),
(7, 'LOT20260107', '2026-01-15', '2026-07-15', 25, 23, 4, 1),
(8, 'LOT20260108', '2026-01-20', '2026-08-20', 100, 98, 6, 1),
(9, 'LOT20260109', '2026-01-05', NULL, 200, 190, 1, 1),
(10, 'LOT20260110', '2026-01-18', NULL, 150, 145, 5, 1);

-- ======================================================================
-- CHÈN DỮ LIỆU NHÂN CUNG CẤP
-- ======================================================================
INSERT INTO suppliers (code, name, address, phone, email, contact_person, payment_terms, status) VALUES
('CC001', 'Công ty TNHH Thương Mại ABC', '123 Đường ABC, Quận 1, TP.HCM', '0283456789', 'contact@abc.com.vn', 'Nguyễn Văn A', 'Thanh toán 30 ngày', 1),
('CC002', 'Công ty CP Sản Xuất XYZ', '456 Đường XYZ, Quận 3, TP.HCM', '0287654321', 'sales@xyz.com.vn', 'Trần Thị B', 'Thanh toán 15 ngày', 1),
('CC003', 'Công ty TNHH Điện Tử Việt Nam', '789 Đường Điện Tử, Quận 2, TP.HCM', '0282468135', 'supply@electronics.vn', 'Phạm Văn C', 'Thanh toán 45 ngày', 1),
('CC004', 'Công ty CP Thực Phẩm Sạch', '321 Đường Sạch, Quận 9, TP.HCM', '0289876543', 'info@freshfood.vn', 'Hoàng Thị D', 'Thanh toán 7 ngày', 1);

-- ======================================================================
-- CHÈN DỮ LIỆU KHÁCH HÀNG
-- ======================================================================
INSERT INTO customers (code, name, address, phone, email, contact_person, payment_terms, status) VALUES
('KH001', 'Siêu thị Co.opMart', '111 Đường Nguyễn Huệ, TP.HCM', '0283334444', 'buy@coopmart.com.vn', 'Dương Thị F', 'Thanh toán 30 ngày', 1),
('KH002', 'Công ty TNHH Bán Lẻ FPT', '222 Đường Thái Hà, Hà Nội', '0243334444', 'sales@fpt.com.vn', 'Trương Văn G', 'Thanh toán 15 ngày', 1),
('KH003', 'Cửa hàng tiện lợi Circle K', '333 Đường Cộng Hòa, TP.HCM', '0284445555', 'supply@circlek.vn', 'Ngô Thị H', 'Thanh toán 10 ngày', 1),
('KH004', 'Nhà thuốc Pharmacity', '444 Đường Tôn Đức Thắng, TP.HCM', '0288886666', 'order@pharma.vn', 'Vũ Văn I', 'Thanh toán 7 ngày', 1);

-- ======================================================================
-- CHÈN DỮ LIỆU PHIẾU NHẬP KHO
-- ======================================================================
INSERT INTO import_receipts (receipt_code, receipt_date, warehouse_id, supplier, created_by, approved_by, status, notes) VALUES
('PN20260101', '2026-01-10', 1, 'Công ty TNHH Thương Mại ABC', 3, 2, 'approved', 'Nhập hàng điện thoại tháng 1'),
('PN20260102', '2026-01-12', 1, 'Công ty CP Sản Xuất XYZ', 3, 2, 'approved', 'Nhập sữa tươi'),
('PN20260103', '2026-01-15', 2, 'Công ty TNHH Điện Tử Việt Nam', 3, 2, 'approved', 'Nhập laptop'),
('PN20260104', '2026-01-18', 1, 'Công ty CP Thực Phẩm Sạch', 3, NULL, 'pending', 'Đang chờ duyệt nhập thực phẩm');

-- ======================================================================
-- CHÈN DỮ LIỆU CHI TIẾT PHIẾU NHẬP
-- ======================================================================
INSERT INTO import_receipt_details (receipt_id, item_id, batch_code, quantity, unit_price, storage_location_id, expiry_date) VALUES
(1, 1, 'LOT20260101', 20, 25000000.00, 1, NULL),
(1, 2, 'LOT20260102', 15, 22000000.00, 2, NULL),
(2, 4, 'LOT20260104', 100, 28000.00, 1, '2026-07-08'),
(3, 3, 'LOT20260103', 10, 35000000.00, 3, NULL),
(4, 5, 'LOT20260105', 50, 190000.00, 2, '2026-12-31');

-- ======================================================================
-- CHÈN DỮ LIỆU PHIẾU XUẤT KHO
-- ======================================================================
INSERT INTO export_receipts (receipt_code, receipt_date, warehouse_id, customer, created_by, approved_by, status, notes) VALUES
('PX20260101', '2026-01-15', 1, 'Siêu thị Co.opMart', 3, 2, 'approved', 'Xuất điện thoại'),
('PX20260102', '2026-01-18', 1, 'Công ty TNHH Bán Lẻ FPT', 3, 2, 'approved', 'Xuất sữa tươi'),
('PX20260103', '2026-01-20', 2, 'Cửa hàng tiện lợi Circle K', 3, NULL, 'pending', 'Chờ duyệt xuất hàng');

-- ======================================================================
-- CHÈN DỮ LIỆU CHI TIẾT PHIẾU XUẤT
-- ======================================================================
INSERT INTO export_receipt_details (receipt_id, item_id, batch_id, quantity, unit_price) VALUES
(1, 1, 1, 2, 28000000.00),
(1, 2, 2, 1, 24000000.00),
(2, 4, 4, 15, 35000.00),
(3, 5, 5, 5, 210000.00);

-- ======================================================================
-- CHÈN DỮ LIỆU CHUYỂN KHO NỘI BỘ
-- ======================================================================
INSERT INTO internal_transfers (transfer_code, transfer_date, from_warehouse_id, to_warehouse_id, from_location_id, to_location_id, created_by, approved_by, status, notes) VALUES
('CK20260101', '2026-01-16', 1, 2, 1, 4, 3, 2, 'completed', 'Chuyển sữa từ kho TP.HCM lên Hà Nội'),
('CK20260102', '2026-01-20', 2, 3, 4, 6, 3, 2, 'approved', 'Chuyển hàng xuống kho Đà Nẵng'),
('CK20260103', '2026-01-22', 1, 2, 2, 5, 3, NULL, 'pending', 'Đang chờ duyệt chuyển kho');

-- ======================================================================
-- CHÈN DỮ LIỆU CHI TIẾT CHUYỂN KHO
-- ======================================================================
INSERT INTO internal_transfer_details (transfer_id, item_id, batch_id, quantity) VALUES
(1, 4, 4, 10),
(1, 6, 6, 5),
(2, 5, 5, 8),
(3, 2, 2, 3);

-- ======================================================================
-- CHÈN DỮ LIỆU TỒNKHO
-- ======================================================================
INSERT INTO inventory (item_id, warehouse_id, storage_location_id, batch_id, quantity) VALUES
(1, 1, 1, 1, 18),
(2, 1, 2, 2, 14),
(3, 1, 3, 3, 9),
(4, 1, 1, 4, 75),
(5, 1, 2, 5, 45),
(6, 2, 4, 6, 28),
(7, 2, 5, 7, 23),
(8, 3, 6, 8, 98),
(9, 1, 1, 9, 190),
(10, 2, 4, 10, 145);

-- ======================================================================
-- CHÈN DỮ LIỆU PHIẾU ĐIỀU CHỈNH TỒN KHO
-- ======================================================================
INSERT INTO inventory_adjustments (adjustment_code, adjustment_date, warehouse_id, reason, created_by, approved_by, status, notes) VALUES
('DC20260101', '2026-01-20', 1, 'loss', 3, 2, 'approved', 'Điều chỉnh hàng bị mất'),
('DC20260102', '2026-01-22', 1, 'damage', 3, 2, 'approved', 'Điều chỉnh hàng bị hư hỏng'),
('DC20260103', '2026-01-23', 2, 'correction', 3, NULL, 'pending', 'Chờ duyệt điều chỉnh');

-- ======================================================================
-- CHÈN DỮ LIỆU CHI TIẾT PHIẾU ĐIỀU CHỈNH
-- ======================================================================
INSERT INTO inventory_adjustment_details (adjustment_id, item_id, batch_id, current_quantity, adjusted_quantity, difference, notes) VALUES
(1, 1, 1, 18, 16, -2, 'Mất 2 cái iPhone'),
(2, 4, 4, 75, 73, -2, 'Hỏng 2 hộp sữa'),
(3, 6, 6, 28, 30, 2, 'Thêm 2 bao nước cam');

-- ======================================================================
-- CHÈN DỮ LIỆU THÔNG BÁO
-- ======================================================================
INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
(2, 'low_stock', 'Cảnh báo tồn kho thấp', 'Sản phẩm iPhone 14 Pro còn 18 cái, dưới mức tối thiểu', 0),
(2, 'expiry_warning', 'Cảnh báo hàng gần hết hạn', 'Lô sữa LOT20260104 sẽ hết hạn trong 3 tháng', 0),
(3, 'approval_pending', 'Phiếu nhập chờ duyệt', 'Phiếu nhập PN20260104 đang chờ duyệt', 0),
(4, 'approval_pending', 'Phiếu xuất chờ duyệt', 'Phiếu xuất PX20260103 đang chờ duyệt', 0),
(2, 'transfer_incomplete', 'Chuyển kho chưa hoàn thành', 'Phiếu chuyển kho CK20260103 chưa được duyệt', 1);

-- ======================================================================
-- CHÈN DỮ LIỆU LỊCH SỬ THAO TÁC
-- ======================================================================
INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES
(1, 'login', 'users', 1, 'Đăng nhập hệ thống', '192.168.1.100'),
(3, 'create', 'import_receipts', 1, 'Tạo phiếu nhập PN20260101', '192.168.1.101'),
(2, 'approve', 'import_receipts', 1, 'Duyệt phiếu nhập PN20260101', '192.168.1.100'),
(3, 'create', 'export_receipts', 1, 'Tạo phiếu xuất PX20260101', '192.168.1.101'),
(2, 'approve', 'export_receipts', 1, 'Duyệt phiếu xuất PX20260101', '192.168.1.100'),
(3, 'create', 'internal_transfers', 1, 'Tạo phiếu chuyển kho CK20260101', '192.168.1.101'),
(2, 'approve', 'internal_transfers', 1, 'Duyệt phiếu chuyển kho CK20260101', '192.168.1.100'),
(3, 'create', 'inventory_adjustments', 1, 'Tạo phiếu điều chỉnh DC20260101', '192.168.1.101'),
(2, 'approve', 'inventory_adjustments', 1, 'Duyệt phiếu điều chỉnh DC20260101', '192.168.1.100'),
(3, 'update', 'inventory', 1, 'Cập nhật tồn kho sản phẩm SP001', '192.168.1.101'),
(2, 'login', 'users', 1, 'Đăng nhập hệ thống', '192.168.1.100'),
(3, 'create', 'import_receipts', 4, 'Tạo phiếu nhập PN20260104', '192.168.1.101');
