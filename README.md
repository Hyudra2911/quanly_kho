# Hệ thống Quản Lý Kho Hàng

Hệ thống quản lý kho hàng được xây dựng bằng PHP và MySQL, hỗ trợ đầy đủ các chức năng quản lý kho hiện đại.

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx
- XAMPP/WAMP/LAMP (khuyến nghị)

## Cài đặt

1. **Import database:**
   - Mở phpMyAdmin hoặc MySQL command line
   - Import file `database.sql` để tạo database và các bảng

2. **Cấu hình kết nối:**
   - Mở file `config/database.php`
   - Điều chỉnh thông tin kết nối database nếu cần:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'quanly_kho');
     ```

3. **Cấu hình BASE_URL:**
   - Mở file `config/config.php`
   - Điều chỉnh BASE_URL theo đường dẫn của bạn:
     ```php
     define('BASE_URL', 'http://localhost/quanly_kho/');
     ```

4. **Truy cập hệ thống:**
   - Mở trình duyệt và truy cập: `http://localhost/quanly_kho/login.php`
   - Đăng nhập với tài khoản mặc định:
     - Username: `admin`
     - Password: `password`

## Các chức năng chính

### 1. Quản lý hàng hóa
- Danh mục hàng hóa với đầy đủ thông tin
- Quản lý nhóm hàng hóa
- Theo dõi hạn sử dụng
- Cảnh báo tồn kho tối thiểu

### 2. Quản lý kho và vị trí lưu trữ
- Quản lý nhiều kho hàng
- Quản lý vị trí lưu trữ chi tiết (khu vực, kệ, phòng)
- Theo dõi tồn kho theo từng vị trí

### 3. Quản lý nhập xuất kho
- Tạo và quản lý phiếu nhập kho
- Tạo và quản lý phiếu xuất kho
- Hệ thống duyệt phiếu nhập/xuất
- Quản lý lô hàng và hạn sử dụng

### 4. Chuyển kho nội bộ
- Chuyển hàng hóa giữa các kho
- Theo dõi lịch sử chuyển kho
- Cảnh báo khi số lượng vượt quá tồn kho

### 5. Quản lý tồn kho
- Theo dõi tồn kho real-time
- Hiển thị tồn kho theo kho, vị trí, lô hàng
- Cảnh báo tồn kho thấp
- Quản lý hàng tồn đọng, hàng sắp hết hạn

### 6. Báo cáo và thống kê
- Báo cáo tồn kho
- Báo cáo nhập xuất theo thời gian
- Báo cáo chuyển kho
- Báo cáo hàng sắp hết hạn

### 7. Hệ thống thông báo
- Thông báo tồn kho thấp
- Thông báo hàng hết hạn
- Thông báo phiếu chờ duyệt

### 8. Quản lý người dùng và bảo mật
- Phân quyền theo vai trò (admin, quản lý kho, nhân viên, kế toán)
- Quản lý đăng nhập, đổi mật khẩu
- Lưu lịch sử thao tác người dùng
- Quản lý hồ sơ cá nhân (đổi mật khẩu, cập nhật thông tin)
- Xem lịch sử hoạt động chi tiết

### 9. Quản lý đối tác
- Quản lý nhà cung cấp (thông tin liên hệ, địa chỉ, mã số thuế)
- Quản lý khách hàng (thông tin liên hệ, địa chỉ, mã số thuế)
- Liên kết với phiếu nhập/xuất

### 10. Điều chỉnh tồn kho
- Tạo phiếu điều chỉnh tồn kho (tăng, giảm, kiểm kê)
- Duyệt phiếu điều chỉnh
- Tự động cập nhật tồn kho sau khi duyệt

### 11. Xuất báo cáo và in ấn
- Xuất báo cáo ra Excel (.xls)
- Xuất báo cáo ra PDF
- In phiếu nhập kho
- In phiếu xuất kho

### 12. Biểu đồ và thống kê
- Biểu đồ nhập xuất theo thời gian
- Biểu đồ phân bố tồn kho theo kho
- Dashboard với thống kê trực quan

### 13. Sao lưu và khôi phục
- Tạo backup database tự động
- Tải file backup về máy
- Quản lý danh sách backup

## Cấu trúc thư mục

```
quanly_kho/
├── config/           # Cấu hình và kết nối database
├── includes/         # Header và footer
├── modules/          # Các module chức năng
│   ├── items/        # Quản lý hàng hóa
│   ├── warehouses/   # Quản lý kho
│   ├── import_receipts/  # Phiếu nhập
│   ├── export_receipts/  # Phiếu xuất
│   ├── internal_transfers/ # Chuyển kho
│   ├── inventory/    # Tồn kho
│   ├── inventory_adjustments/ # Điều chỉnh tồn kho
│   ├── reports/      # Báo cáo
│   ├── notifications/ # Thông báo
│   ├── users/        # Người dùng
│   ├── suppliers/    # Nhà cung cấp
│   ├── customers/    # Khách hàng
│   ├── profile/      # Hồ sơ cá nhân
│   ├── activity_logs/ # Lịch sử hoạt động
│   └── backup/       # Sao lưu
├── api/              # API helpers
├── backups/          # Thư mục lưu backup (tự động tạo)
├── assets/           # CSS, JS, images
├── database.sql      # File SQL tạo database
├── login.php         # Trang đăng nhập
├── logout.php        # Đăng xuất
└── index.php         # Dashboard
```

## Vai trò người dùng

- **Admin**: Toàn quyền quản lý hệ thống
- **Quản lý kho**: Duyệt phiếu nhập/xuất, quản lý kho
- **Nhân viên nhập kho**: Tạo phiếu nhập/xuất
- **Kế toán**: Xem báo cáo và thống kê

## Cài đặt bổ sung

Sau khi import database, chạy thêm file `database_updates.sql` để tạo các bảng mới:
- Bảng `suppliers` (nhà cung cấp)
- Bảng `customers` (khách hàng)
- Bảng `inventory_adjustments` (điều chỉnh tồn kho)
- Bảng `inventory_adjustment_details` (chi tiết điều chỉnh)

## Lưu ý

- Mật khẩu mặc định cho tất cả tài khoản mẫu là: `password`
- Nên đổi mật khẩu sau lần đăng nhập đầu tiên
- Hệ thống sử dụng PDO để bảo mật chống SQL Injection
- Tất cả mật khẩu được mã hóa bằng password_hash()
- Thư mục `backups/` sẽ được tạo tự động khi tạo backup lần đầu
- Để in phiếu, sử dụng chức năng "In / Lưu PDF" trong trình duyệt

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. Cấu hình database đúng chưa
2. Quyền truy cập file/folder
3. PHP version và extensions (PDO, pdo_mysql)

Thêm hàng hóa: tạo mã hàng/master data (code, tên, đơn vị, nhóm, hạn dùng, tồn tối thiểu). Đây là danh mục nền tảng cho tất cả nghiệp vụ. Nếu bỏ, bạn không có mã hàng để chọn khi nhập/xuất, báo cáo, tồn kho.
Nhập hàng hóa: tạo chứng từ nhập kho cho các mã đã tồn tại. Đây là nghiệp vụ phát sinh số lượng, lô, vị trí, đơn giá và cập nhật tồn/giá trị (qua bảng chi tiết). Nếu bỏ, kho không có giao dịch nhập, tồn kho luôn bằng 0, không có lịch sử phiếu nhập.