-- Cập nhật database cho các chức năng mới




-- Bảng nhà cung cấp
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    tax_code VARCHAR(50),
    notes TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






-- Bảng khách hàng
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    tax_code VARCHAR(50),
    notes TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






-- Bảng điều chỉnh tồn kho
CREATE TABLE IF NOT EXISTS inventory_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adjustment_code VARCHAR(50) UNIQUE NOT NULL,
    adjustment_date DATE NOT NULL,
    warehouse_id INT NOT NULL,
    storage_location_id INT,
    adjustment_type ENUM('increase', 'decrease', 'count') NOT NULL,
    reason VARCHAR(200),
    created_by INT NOT NULL,
    approved_by INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (storage_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nếu bạn đã tạo bảng inventory_adjustments trước đó (từ database.sql) thì cần ALTER để thêm các cột mới
-- (Chạy các lệnh này nếu trang "Điều chỉnh tồn kho" báo lỗi thiếu cột storage_location_id)
ALTER TABLE inventory_adjustments
    ADD COLUMN storage_location_id INT NULL AFTER warehouse_id;

ALTER TABLE inventory_adjustments
    ADD FOREIGN KEY (storage_location_id) REFERENCES storage_locations(id) ON DELETE SET NULL;






-- Bảng chi tiết điều chỉnh tồn kho
CREATE TABLE IF NOT EXISTS inventory_adjustment_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adjustment_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT,
    quantity_before INT NOT NULL DEFAULT 0,
    quantity_after INT NOT NULL DEFAULT 0,
    quantity_difference INT NOT NULL DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (adjustment_id) REFERENCES inventory_adjustments(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






-- Cập nhật bảng import_receipts để tham chiếu suppliers
ALTER TABLE import_receipts 
ADD COLUMN supplier_id INT NULL AFTER supplier,
ADD FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL;






-- Cập nhật bảng export_receipts để tham chiếu customers
ALTER TABLE export_receipts
ADD COLUMN customer_id INT NULL AFTER customer,
ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL;




