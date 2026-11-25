-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th2 06, 2026 lúc 05:44 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanly_kho`
--
CREATE DATABASE IF NOT EXISTS `quanly_kho` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quanly_kho`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, 'Đăng nhập hệ thống', '192.168.1.100', '2026-02-06 16:43:02'),
(2, 3, 'create', 'import_receipts', 1, 'Tạo phiếu nhập PN20260101', '192.168.1.101', '2026-02-06 16:43:02'),
(3, 2, 'approve', 'import_receipts', 1, 'Duyệt phiếu nhập PN20260101', '192.168.1.100', '2026-02-06 16:43:02'),
(4, 3, 'create', 'export_receipts', 1, 'Tạo phiếu xuất PX20260101', '192.168.1.101', '2026-02-06 16:43:02'),
(5, 2, 'approve', 'export_receipts', 1, 'Duyệt phiếu xuất PX20260101', '192.168.1.100', '2026-02-06 16:43:02'),
(6, 3, 'create', 'internal_transfers', 1, 'Tạo phiếu chuyển kho CK20260101', '192.168.1.101', '2026-02-06 16:43:02'),
(7, 2, 'approve', 'internal_transfers', 1, 'Duyệt phiếu chuyển kho CK20260101', '192.168.1.100', '2026-02-06 16:43:02'),
(8, 3, 'create', 'inventory_adjustments', 1, 'Tạo phiếu điều chỉnh DC20260101', '192.168.1.101', '2026-02-06 16:43:02'),
(9, 2, 'approve', 'inventory_adjustments', 1, 'Duyệt phiếu điều chỉnh DC20260101', '192.168.1.100', '2026-02-06 16:43:02'),
(10, 3, 'update', 'inventory', 1, 'Cập nhật tồn kho sản phẩm SP001', '192.168.1.101', '2026-02-06 16:43:02'),
(11, 2, 'login', 'users', 1, 'Đăng nhập hệ thống', '192.168.1.100', '2026-02-06 16:43:02'),
(12, 3, 'create', 'import_receipts', 4, 'Tạo phiếu nhập PN20260104', '192.168.1.101', '2026-02-06 16:43:02'),
(13, 1, 'logout', 'users', 1, 'Đăng xuất hệ thống', '::1', '2026-02-06 16:43:22'),
(14, 1, 'login', 'users', 1, 'Đăng nhập hệ thống', '::1', '2026-02-06 16:43:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_code` varchar(100) NOT NULL,
  `import_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `initial_quantity` int(11) NOT NULL DEFAULT 0,
  `current_quantity` int(11) NOT NULL DEFAULT 0,
  `storage_location_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `batches`
--

INSERT INTO `batches` (`id`, `item_id`, `batch_code`, `import_date`, `expiry_date`, `initial_quantity`, `current_quantity`, `storage_location_id`, `status`, `created_at`) VALUES
(1, 1, 'LOT20260101', '2026-01-10', NULL, 20, 18, 1, 1, '2026-02-06 16:43:02'),
(2, 2, 'LOT20260102', '2026-01-12', NULL, 15, 14, 2, 1, '2026-02-06 16:43:02'),
(3, 3, 'LOT20260103', '2026-01-15', NULL, 10, 9, 1, 1, '2026-02-06 16:43:02'),
(4, 4, 'LOT20260104', '2026-01-08', '2026-07-08', 100, 85, 1, 1, '2026-02-06 16:43:02'),
(5, 5, 'LOT20260105', '2026-01-10', '2026-12-31', 50, 45, 2, 1, '2026-02-06 16:43:02'),
(6, 6, 'LOT20260106', '2026-01-12', '2026-06-30', 30, 28, 4, 1, '2026-02-06 16:43:02'),
(7, 7, 'LOT20260107', '2026-01-15', '2026-07-15', 25, 23, 4, 1, '2026-02-06 16:43:02'),
(8, 8, 'LOT20260108', '2026-01-20', '2026-08-20', 100, 98, 6, 1, '2026-02-06 16:43:02'),
(9, 9, 'LOT20260109', '2026-01-05', NULL, 200, 190, 1, 1, '2026-02-06 16:43:02'),
(10, 10, 'LOT20260110', '2026-01-18', NULL, 150, 145, 5, 1, '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `payment_terms` varchar(200) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `code`, `name`, `address`, `phone`, `email`, `contact_person`, `payment_terms`, `status`, `created_at`, `updated_at`) VALUES
(1, 'KH001', 'Siêu thị Co.opMart', '111 Đường Nguyễn Huệ, TP.HCM', '0283334444', 'buy@coopmart.com.vn', 'Dương Thị F', 'Thanh toán 30 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'KH002', 'Công ty TNHH Bán Lẻ FPT', '222 Đường Thái Hà, Hà Nội', '0243334444', 'sales@fpt.com.vn', 'Trương Văn G', 'Thanh toán 15 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'KH003', 'Cửa hàng tiện lợi Circle K', '333 Đường Cộng Hòa, TP.HCM', '0284445555', 'supply@circlek.vn', 'Ngô Thị H', 'Thanh toán 10 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(4, 'KH004', 'Nhà thuốc Pharmacity', '444 Đường Tôn Đức Thắng, TP.HCM', '0288886666', 'order@pharma.vn', 'Vũ Văn I', 'Thanh toán 7 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `export_receipts`
--

CREATE TABLE `export_receipts` (
  `id` int(11) NOT NULL,
  `receipt_code` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `customer` varchar(200) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `export_receipts`
--

INSERT INTO `export_receipts` (`id`, `receipt_code`, `receipt_date`, `warehouse_id`, `customer`, `customer_id`, `created_by`, `approved_by`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'PX20260101', '2026-01-15', 1, 'Siêu thị Co.opMart', NULL, 3, 2, 'approved', 'Xuất điện thoại', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'PX20260102', '2026-01-18', 1, 'Công ty TNHH Bán Lẻ FPT', NULL, 3, 2, 'approved', 'Xuất sữa tươi', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'PX20260103', '2026-01-20', 2, 'Cửa hàng tiện lợi Circle K', NULL, 3, NULL, 'pending', 'Chờ duyệt xuất hàng', '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `export_receipt_details`
--

CREATE TABLE `export_receipt_details` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `export_receipt_details`
--

INSERT INTO `export_receipt_details` (`id`, `receipt_id`, `item_id`, `batch_id`, `quantity`, `unit_price`, `notes`) VALUES
(1, 1, 1, 1, 2, 28000000.00, NULL),
(2, 1, 2, 2, 1, 24000000.00, NULL),
(3, 2, 4, 4, 15, 35000.00, NULL),
(4, 3, 5, 5, 5, 210000.00, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `import_receipts`
--

CREATE TABLE `import_receipts` (
  `id` int(11) NOT NULL,
  `receipt_code` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `import_receipts`
--

INSERT INTO `import_receipts` (`id`, `receipt_code`, `receipt_date`, `warehouse_id`, `supplier`, `supplier_id`, `created_by`, `approved_by`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'PN20260101', '2026-01-10', 1, 'Công ty TNHH Thương Mại ABC', NULL, 3, 2, 'approved', 'Nhập hàng điện thoại tháng 1', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'PN20260102', '2026-01-12', 1, 'Công ty CP Sản Xuất XYZ', NULL, 3, 2, 'approved', 'Nhập sữa tươi', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'PN20260103', '2026-01-15', 2, 'Công ty TNHH Điện Tử Việt Nam', NULL, 3, 2, 'approved', 'Nhập laptop', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(4, 'PN20260104', '2026-01-18', 1, 'Công ty CP Thực Phẩm Sạch', NULL, 3, NULL, 'pending', 'Đang chờ duyệt nhập thực phẩm', '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `import_receipt_details`
--

CREATE TABLE `import_receipt_details` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_code` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT 0.00,
  `storage_location_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `import_receipt_details`
--

INSERT INTO `import_receipt_details` (`id`, `receipt_id`, `item_id`, `batch_code`, `quantity`, `unit_price`, `storage_location_id`, `expiry_date`, `notes`) VALUES
(1, 1, 1, 'LOT20260101', 20, 25000000.00, 1, NULL, NULL),
(2, 1, 2, 'LOT20260102', 15, 22000000.00, 2, NULL, NULL),
(3, 2, 4, 'LOT20260104', 100, 28000.00, 1, '2026-07-08', NULL),
(4, 3, 3, 'LOT20260103', 10, 35000000.00, 3, NULL, NULL),
(5, 4, 5, 'LOT20260105', 50, 190000.00, 2, '2026-12-31', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `internal_transfers`
--

CREATE TABLE `internal_transfers` (
  `id` int(11) NOT NULL,
  `transfer_code` varchar(50) NOT NULL,
  `transfer_date` date NOT NULL,
  `from_warehouse_id` int(11) NOT NULL,
  `to_warehouse_id` int(11) NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','in_transit','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `internal_transfers`
--

INSERT INTO `internal_transfers` (`id`, `transfer_code`, `transfer_date`, `from_warehouse_id`, `to_warehouse_id`, `from_location_id`, `to_location_id`, `created_by`, `approved_by`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'CK20260101', '2026-01-16', 1, 2, 1, 4, 3, 2, 'completed', 'Chuyển sữa từ kho TP.HCM lên Hà Nội', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'CK20260102', '2026-01-20', 2, 3, 4, 6, 3, 2, '', 'Chuyển hàng xuống kho Đà Nẵng', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'CK20260103', '2026-01-22', 1, 2, 2, 5, 3, NULL, 'pending', 'Đang chờ duyệt chuyển kho', '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `internal_transfer_details`
--

CREATE TABLE `internal_transfer_details` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `internal_transfer_details`
--

INSERT INTO `internal_transfer_details` (`id`, `transfer_id`, `item_id`, `batch_id`, `quantity`, `notes`) VALUES
(1, 1, 4, 4, 10, NULL),
(2, 1, 6, 6, 5, NULL),
(3, 2, 5, 5, 8, NULL),
(4, 3, 2, 2, 3, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `storage_location_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory`
--

INSERT INTO `inventory` (`id`, `item_id`, `warehouse_id`, `storage_location_id`, `batch_id`, `quantity`, `last_updated`) VALUES
(1, 1, 1, 1, 1, 18, '2026-02-06 16:43:02'),
(2, 2, 1, 2, 2, 14, '2026-02-06 16:43:02'),
(3, 3, 1, 3, 3, 9, '2026-02-06 16:43:02'),
(4, 4, 1, 1, 4, 75, '2026-02-06 16:43:02'),
(5, 5, 1, 2, 5, 45, '2026-02-06 16:43:02'),
(6, 6, 2, 4, 6, 28, '2026-02-06 16:43:02'),
(7, 7, 2, 5, 7, 23, '2026-02-06 16:43:02'),
(8, 8, 3, 6, 8, 98, '2026-02-06 16:43:02'),
(9, 9, 1, 1, 9, 190, '2026-02-06 16:43:02'),
(10, 10, 2, 4, 10, 145, '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_adjustments`
--

CREATE TABLE `inventory_adjustments` (
  `id` int(11) NOT NULL,
  `adjustment_code` varchar(50) NOT NULL,
  `adjustment_date` date NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `storage_location_id` int(11) DEFAULT NULL,
  `reason` enum('loss','damage','correction','theft','other') DEFAULT 'correction',
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory_adjustments`
--

INSERT INTO `inventory_adjustments` (`id`, `adjustment_code`, `adjustment_date`, `warehouse_id`, `storage_location_id`, `reason`, `created_by`, `approved_by`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'DC20260101', '2026-01-20', 1, NULL, 'loss', 3, 2, 'approved', 'Điều chỉnh hàng bị mất', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'DC20260102', '2026-01-22', 1, NULL, 'damage', 3, 2, 'approved', 'Điều chỉnh hàng bị hư hỏng', '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'DC20260103', '2026-01-23', 2, NULL, 'correction', 3, NULL, 'pending', 'Chờ duyệt điều chỉnh', '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_adjustment_details`
--

CREATE TABLE `inventory_adjustment_details` (
  `id` int(11) NOT NULL,
  `adjustment_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `current_quantity` int(11) NOT NULL,
  `adjusted_quantity` int(11) NOT NULL,
  `difference` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory_adjustment_details`
--

INSERT INTO `inventory_adjustment_details` (`id`, `adjustment_id`, `item_id`, `batch_id`, `current_quantity`, `adjusted_quantity`, `difference`, `notes`) VALUES
(1, 1, 1, 1, 18, 16, -2, 'Mất 2 cái iPhone'),
(2, 2, 4, 4, 75, 73, -2, 'Hỏng 2 hộp sữa'),
(3, 3, 6, 6, 28, 30, 2, 'Thêm 2 bao nước cam');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `has_expiry` tinyint(1) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `items`
--

INSERT INTO `items` (`id`, `code`, `name`, `group_id`, `unit`, `has_expiry`, `min_stock`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SP001', 'iPhone 14 Pro', 3, 'Cái', 0, 5, 'Điện thoại thông minh Apple iPhone 14 Pro 128GB', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'SP002', 'Samsung Galaxy S23', 3, 'Cái', 0, 3, 'Điện thoại Samsung Galaxy S23 256GB', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'SP003', 'Laptop Dell XPS 13', 3, 'Cái', 0, 2, 'Máy tính xách tay Dell XPS 13 FHD', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(4, 'SP004', 'Sữa tươi Vinamilk', 1, 'Hộp', 1, 10, 'Sữa tươi tiệt trùng Vinamilk 1L', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(5, 'SP005', 'Nước ngọt Coca Cola', 2, 'Thùng', 1, 15, 'Nước Coca Cola 330ml x 24 chai', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(6, 'SP006', 'Nước cam Tropicana', 2, 'Thùng', 1, 10, 'Nước cam tự nhiên Tropicana 1L x 6 bao', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(7, 'SP007', 'Kem dưỡng da', 4, 'Hộp', 1, 8, 'Kem dưỡng da ban đêm Olay', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(8, 'SP008', 'Thuốc Panadol', 5, 'Hộp', 1, 25, 'Thuốc giảm đau hạ sốt Panadol 20 viên', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(9, 'SP009', 'Giấy in A4', 6, 'Ream', 0, 50, 'Giấy in ấn A4 80gsm 500 tờ', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(10, 'SP010', 'Bút bi Thiên Long', 6, 'Hộp', 0, 100, 'Bút bi xanh Thiên Long (12 cái/hộp)', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `item_groups`
--

CREATE TABLE `item_groups` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `item_groups`
--

INSERT INTO `item_groups` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'THUC_PHAM', 'Thực phẩm', 'Các sản phẩm thực phẩm tươi sống', '2026-02-06 16:43:02'),
(2, 'DO_UONG', 'Đồ uống', 'Nước uống và đồ uống có cồn', '2026-02-06 16:43:02'),
(3, 'DIEN_TU', 'Điện tử', 'Thiết bị điện tử và linh kiện', '2026-02-06 16:43:02'),
(4, 'MY_PHAM', 'Mỹ phẩm', 'Sản phẩm làm đẹp và chăm sóc', '2026-02-06 16:43:02'),
(5, 'THUOC', 'Thuốc', 'Thuốc và sản phẩm y tế', '2026-02-06 16:43:02'),
(6, 'VAN_PHONG', 'Văn phòng phẩm', 'Dụng cụ và vật tư văn phòng', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('low_stock','expiry_warning','approval_pending','transfer_incomplete','other') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'low_stock', 'Cảnh báo tồn kho thấp', 'Sản phẩm iPhone 14 Pro còn 18 cái, dưới mức tối thiểu', 0, '2026-02-06 16:43:02'),
(2, 2, 'expiry_warning', 'Cảnh báo hàng gần hết hạn', 'Lô sữa LOT20260104 sẽ hết hạn trong 3 tháng', 0, '2026-02-06 16:43:02'),
(3, 3, 'approval_pending', 'Phiếu nhập chờ duyệt', 'Phiếu nhập PN20260104 đang chờ duyệt', 0, '2026-02-06 16:43:02'),
(4, 4, 'approval_pending', 'Phiếu xuất chờ duyệt', 'Phiếu xuất PX20260103 đang chờ duyệt', 0, '2026-02-06 16:43:02'),
(5, 2, 'transfer_incomplete', 'Chuyển kho chưa hoàn thành', 'Phiếu chuyển kho CK20260103 chưa được duyệt', 1, '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `storage_locations`
--

CREATE TABLE `storage_locations` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `shelf` varchar(100) DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `storage_locations`
--

INSERT INTO `storage_locations` (`id`, `warehouse_id`, `code`, `name`, `area`, `shelf`, `room`, `description`, `status`, `created_at`) VALUES
(1, 1, 'A1', 'Khu A - Kệ 1', 'Khu A', 'Kệ 1', 'Phòng 1', NULL, 1, '2026-02-06 16:43:02'),
(2, 1, 'A2', 'Khu A - Kệ 2', 'Khu A', 'Kệ 2', 'Phòng 1', NULL, 1, '2026-02-06 16:43:02'),
(3, 1, 'B1', 'Khu B - Kệ 1', 'Khu B', 'Kệ 1', 'Phòng 2', NULL, 1, '2026-02-06 16:43:02'),
(4, 2, 'C1', 'Khu C - Kệ 1', 'Khu C', 'Kệ 1', 'Phòng 1', NULL, 1, '2026-02-06 16:43:02'),
(5, 2, 'C2', 'Khu C - Kệ 2', 'Khu C', 'Kệ 2', 'Phòng 1', NULL, 1, '2026-02-06 16:43:02'),
(6, 3, 'D1', 'Khu D - Kệ 1', 'Khu D', 'Kệ 1', 'Phòng chính', NULL, 1, '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `payment_terms` varchar(200) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `code`, `name`, `address`, `phone`, `email`, `contact_person`, `payment_terms`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CC001', 'Công ty TNHH Thương Mại ABC', '123 Đường ABC, Quận 1, TP.HCM', '0283456789', 'contact@abc.com.vn', 'Nguyễn Văn A', 'Thanh toán 30 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'CC002', 'Công ty CP Sản Xuất XYZ', '456 Đường XYZ, Quận 3, TP.HCM', '0287654321', 'sales@xyz.com.vn', 'Trần Thị B', 'Thanh toán 15 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'CC003', 'Công ty TNHH Điện Tử Việt Nam', '789 Đường Điện Tử, Quận 2, TP.HCM', '0282468135', 'supply@electronics.vn', 'Phạm Văn C', 'Thanh toán 45 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(4, 'CC004', 'Công ty CP Thực Phẩm Sạch', '321 Đường Sạch, Quận 9, TP.HCM', '0289876543', 'info@freshfood.vn', 'Hoàng Thị D', 'Thanh toán 7 ngày', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','quanly_kho','nhanvien_nhapkho','ketoan') DEFAULT 'nhanvien_nhapkho',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin@example.com', '0912345678', 'admin', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(2, 'quanly1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Quản Lý', 'quanly@example.com', '0901234567', 'quanly_kho', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(3, 'nhanvien1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Nhân Viên', 'nhanvien1@example.com', '0902345678', 'nhanvien_nhapkho', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(4, 'nhanvien2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn Nhân Viên 2', 'nhanvien2@example.com', '0903456789', 'nhanvien_nhapkho', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02'),
(5, 'ketoan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Thị Kế Toán', 'ketoan@example.com', '0904567890', 'ketoan', 1, '2026-02-06 16:43:02', '2026-02-06 16:43:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `warehouses`
--

INSERT INTO `warehouses` (`id`, `code`, `name`, `address`, `latitude`, `longitude`, `manager_id`, `status`, `created_at`) VALUES
(1, 'KHO_01', 'Kho chính TP.HCM', '123 Đường Nguyễn Huệ, Quận 1, TP.HCM', NULL, NULL, 2, 1, '2026-02-06 16:43:02'),
(2, 'KHO_02', 'Kho phụ Hà Nội', '456 Đường Thái Hà, Quận 3, Hà Nội', NULL, NULL, 2, 1, '2026-02-06 16:43:02'),
(3, 'KHO_03', 'Kho miền Trung Đà Nẵng', '789 Đường Bạch Đằng, Quận Hải Châu, Đà Nẵng', NULL, NULL, 2, 1, '2026-02-06 16:43:02');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_batch` (`item_id`,`batch_code`),
  ADD KEY `storage_location_id` (`storage_location_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `export_receipts`
--
ALTER TABLE `export_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_code` (`receipt_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Chỉ mục cho bảng `export_receipt_details`
--
ALTER TABLE `export_receipt_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Chỉ mục cho bảng `import_receipts`
--
ALTER TABLE `import_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_code` (`receipt_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Chỉ mục cho bảng `import_receipt_details`
--
ALTER TABLE `import_receipt_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `storage_location_id` (`storage_location_id`);

--
-- Chỉ mục cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfer_code` (`transfer_code`),
  ADD KEY `from_warehouse_id` (`from_warehouse_id`),
  ADD KEY `to_warehouse_id` (`to_warehouse_id`),
  ADD KEY `from_location_id` (`from_location_id`),
  ADD KEY `to_location_id` (`to_location_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Chỉ mục cho bảng `internal_transfer_details`
--
ALTER TABLE `internal_transfer_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Chỉ mục cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inventory` (`item_id`,`warehouse_id`,`storage_location_id`,`batch_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `storage_location_id` (`storage_location_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Chỉ mục cho bảng `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adjustment_code` (`adjustment_code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `storage_location_id` (`storage_location_id`);

--
-- Chỉ mục cho bảng `inventory_adjustment_details`
--
ALTER TABLE `inventory_adjustment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adjustment_id` (`adjustment_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Chỉ mục cho bảng `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `group_id` (`group_id`);

--
-- Chỉ mục cho bảng `item_groups`
--
ALTER TABLE `item_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `storage_locations`
--
ALTER TABLE `storage_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location` (`warehouse_id`,`code`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `manager_id` (`manager_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `export_receipts`
--
ALTER TABLE `export_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `export_receipt_details`
--
ALTER TABLE `export_receipt_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `import_receipts`
--
ALTER TABLE `import_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `import_receipt_details`
--
ALTER TABLE `import_receipt_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `internal_transfer_details`
--
ALTER TABLE `internal_transfer_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `inventory_adjustment_details`
--
ALTER TABLE `inventory_adjustment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `item_groups`
--
ALTER TABLE `item_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `storage_locations`
--
ALTER TABLE `storage_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `export_receipts`
--
ALTER TABLE `export_receipts`
  ADD CONSTRAINT `export_receipts_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `export_receipts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `export_receipts_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `export_receipts_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `export_receipt_details`
--
ALTER TABLE `export_receipt_details`
  ADD CONSTRAINT `export_receipt_details_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `export_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `export_receipt_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `export_receipt_details_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `import_receipts`
--
ALTER TABLE `import_receipts`
  ADD CONSTRAINT `import_receipts_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `import_receipts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `import_receipts_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `import_receipts_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `import_receipt_details`
--
ALTER TABLE `import_receipt_details`
  ADD CONSTRAINT `import_receipt_details_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `import_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `import_receipt_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `import_receipt_details_ibfk_3` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  ADD CONSTRAINT `internal_transfers_ibfk_1` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `internal_transfers_ibfk_2` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `internal_transfers_ibfk_3` FOREIGN KEY (`from_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internal_transfers_ibfk_4` FOREIGN KEY (`to_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `internal_transfers_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `internal_transfers_ibfk_6` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `internal_transfer_details`
--
ALTER TABLE `internal_transfer_details`
  ADD CONSTRAINT `internal_transfer_details_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `internal_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_transfer_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `internal_transfer_details_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD CONSTRAINT `inventory_adjustments_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `inventory_adjustments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_adjustments_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_adjustments_ibfk_4` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `inventory_adjustment_details`
--
ALTER TABLE `inventory_adjustment_details`
  ADD CONSTRAINT `inventory_adjustment_details_ibfk_1` FOREIGN KEY (`adjustment_id`) REFERENCES `inventory_adjustments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_adjustment_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `inventory_adjustment_details_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `item_groups` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `storage_locations`
--
ALTER TABLE `storage_locations`
  ADD CONSTRAINT `storage_locations_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
