# BÁO CÁO HỆ THỐNG QUẢN LÝ KHO HÀNG

## I. GIỚI THIỆU CHUNG

### 1.1 Tên hệ thống
**Hệ Thống Quản Lý Kho Hàng (Warehouse Management System)**

### 1.2 Mục đích
Hệ thống được xây dựng nhằm:
- Quản lý toàn bộ quy trình nhập/xuất kho hàng
- Theo dõi tồn kho thời gian thực
- Quản lý các đối tác (nhà cung cấp, khách hàng)
- Cung cấp báo cáo chi tiết về tồn kho
- Hỗ trợ quản lý nội bộ với các chức năng điều chỉnh tồn kho
- Tạo lịch sử hoạt động (activity logs) cho mục đích audit

### 1.3 Đối tượng sử dụng
- **Quản trị viên (Admin)**: Toàn quyền quản lý hệ thống
- **Quản lý kho (Quanly_kho)**: Quản lý hoạt động kho
- **Nhân viên kho (Nhan_vien_kho)**: Thực hiện các phiếu nhập/xuất

---

## II. KIẾN TRÚC HỆ THỐNG

### 2.1 Công nghệ sử dụng
| Thành phần | Công nghệ | Phiên bản |
|-----------|-----------|---------|
| Backend | PHP | 7.4+ |
| Database | MySQL | 5.7+ |
| Frontend | HTML5, CSS3, JavaScript | Bootstrap 5.3.0 |
| Framework CSS | Bootstrap | 5.3.0 |
| Icon | Bootstrap Icons | 1.11.0 |
| Web Server | Apache | Tùy theo môi trường |

### 2.2 Cấu trúc thư mục
```
quanly_kho/
├── config/              # Cấu hình hệ thống
│   ├── config.php      # Cấu hình chung (BASE_URL, SESSION, v.v.)
│   └── database.php    # Cấu hình kết nối database
├── includes/            # Các file include chung
│   ├── header.php      # Header chung cho tất cả trang
│   └── footer.php      # Footer chung cho tất cả trang
├── modules/             # Các module chức năng
│   ├── items/          # Quản lý hàng hóa
│   ├── item_groups/    # Quản lý nhóm hàng hóa
│   ├── warehouses/     # Quản lý kho hàng
│   ├── storage_locations/ # Quản lý vị trí lưu trữ
│   ├── import_receipts/   # Quản lý phiếu nhập kho
│   ├── export_receipts/   # Quản lý phiếu xuất kho
│   ├── inventory/         # Xem tồn kho
│   ├── inventory_adjustments/ # Điều chỉnh tồn kho
│   ├── internal_transfers/ # Chuyển kho nội bộ
│   ├── suppliers/       # Quản lý nhà cung cấp
│   ├── customers/       # Quản lý khách hàng
│   ├── users/          # Quản lý người dùng
│   ├── profile/        # Hồ sơ cá nhân
│   ├── notifications/  # Thông báo
│   ├── activity_logs/  # Lịch sử hoạt động
│   ├── reports/        # Báo cáo tồn kho
│   └── backup/         # Sao lưu database
├── api/                 # API endpoints
├── assets/              # Tài nguyên tĩnh
│   ├── css/            # Stylesheet
│   └── js/             # JavaScript
├── backups/             # Thư mục lưu backup
├── index.php           # Trang Dashboard
├── login.php           # Trang đăng nhập
└── logout.php          # Đăng xuất

```

---

## III. CHỨC NĂNG CHÍNH

### 3.1 Quản lý Hàng Hóa (Modules: items, item_groups)

#### 3.1.1 Danh mục hàng hóa
- **Chức năng:**
  - Thêm mới hàng hóa
  - Chỉnh sửa thông tin hàng hóa
  - Xóa hàng hóa (soft delete)
  - Xem chi tiết hàng hóa
  
- **Thông tin quản lý:**
  - Mã hàng hóa
  - Tên hàng hóa
  - Nhóm hàng hóa
  - Mô tả chi tiết
  - Đơn vị tính
  - Tồn kho tối thiểu (min_stock) - để cảnh báo
  - Hạn sử dụng
  - Trạng thái (kích hoạt/vô hiệu hóa)

#### 3.1.2 Nhóm hàng hóa
- **Chức năng:**
  - Tạo, chỉnh sửa, xóa nhóm hàng
  - Phân loại hàng hóa theo nhóm
  
- **Mục đích:**
  - Tổ chức hàng hóa theo danh mục
  - Dễ dàng tìm kiếm và lọc

### 3.2 Quản lý Kho và Vị trí lưu trữ

#### 3.2.1 Danh sách kho
- **Chức năng:**
  - Tạo và quản lý các kho hàng
  - Cập nhật thông tin kho (địa chỉ, quản trị viên, v.v.)
  - Xem chi tiết kho
  
- **Thông tin:**
  - Mã kho
  - Tên kho
  - Địa chỉ
  - Người quản lý
  - Số điện thoại liên hệ
  - Ghi chú
  - Trạng thái

#### 3.2.2 Vị trí lưu trữ (Storage Locations)
- **Chức năng:**
  - Quản lý vị trí cụ thể trong kho (kệ, tầng, v.v.)
  - Thiết lập dung lượng tối đa cho từng vị trí
  
- **Mục đích:**
  - Theo dõi vị trí chính xác của hàng hóa
  - Tối ưu hóa không gian lưu trữ
  - Dễ dàng tìm kiếm hàng trong kho

### 3.3 Quản lý Nhập/Xuất Kho

#### 3.3.1 Phiếu Nhập Kho (Import Receipts)
- **Chức năng:**
  - Tạo phiếu nhập từ nhà cung cấp
  - Thêm chi tiết từng mặt hàng (mã item, số lượng, giá)
  - Duyệt/từ chối phiếu nhập
  - In phiếu nhập
  - Xem lịch sử phiếu
  
- **Quy trình:**
  1. Nhân viên tạo phiếu nhập
  2. Chọn nhà cung cấp
  3. Thêm các mặt hàng
  4. Gửi duyệt
  5. Quản lý duyệt phiếu
  6. Khi duyệt, tồn kho tự động cập nhật

- **Trạng thái phiếu:**
  - Pending (Chờ duyệt)
  - Approved (Được duyệt)
  - Rejected (Từ chối)

#### 3.3.2 Phiếu Xuất Kho (Export Receipts)
- **Chức năng:**
  - Tạo phiếu xuất cho khách hàng
  - Thêm chi tiết từng mặt hàng
  - Duyệt/từ chối phiếu xuất
  - In phiếu xuất
  
- **Quy trình:**
  1. Nhân viên tạo phiếu xuất
  2. Chọn khách hàng
  3. Thêm các mặt hàng từ tồn kho có sẵn
  4. Gửi duyệt
  5. Quản lý duyệt và xác nhận xuất
  6. Khi duyệt, tồn kho tự động trừ đi

### 3.4 Quản lý Điều chỉnh Tồn kho

#### 3.4.1 Phiếu Điều chỉnh (Inventory Adjustments)
- **Chức năng:**
  - Tạo phiếu điều chỉnh tồn kho
  - Điều chỉnh số lượng hàng (tăng/giảm)
  - Xác định lý do điều chỉnh
  - Duyệt/từ chối điều chỉnh
  
- **Tình huống sử dụng:**
  - Hàng bị mất/hư hỏng
  - Tồn kho không khớp với thực tế
  - Điều chỉnh do kiểm kê
  - Lỗi nhập/xuất trước đó

### 3.5 Chuyển Kho Nội bộ

#### 3.5.1 Chuyển hàng giữa các kho
- **Chức năng:**
  - Tạo phiếu chuyển hàng từ kho này sang kho khác
  - Theo dõi trạng thái chuyển (pending, in_transit, completed)
  - Xác nhận hàng nhận ở kho đích
  
- **Quy trình:**
  1. Tạo phiếu chuyển (kho nguồn → kho đích)
  2. Thêm chi tiết hàng cần chuyển
  3. Xác nhận tại kho đích khi nhận hàng
  4. Cập nhật tồn kho

### 3.6 Quản lý Đối tác

#### 3.6.1 Nhà cung cấp (Suppliers)
- **Chức năng:**
  - Tạo, chỉnh sửa, xóa nhà cung cấp
  - Lưu thông tin liên hệ
  - Theo dõi lịch sử giao dịch
  
- **Thông tin:**
  - Mã NCC
  - Tên công ty
  - Địa chỉ
  - Số điện thoại
  - Email
  - Người liên hệ
  - Ghi chú

#### 3.6.2 Khách hàng (Customers)
- **Chức năng:**
  - Tạo, chỉnh sửa, xóa khách hàng
  - Lưu thông tin liên hệ
  - Theo dõi lịch sử mua hàng
  
- **Thông tin:**
  - Mã khách hàng
  - Tên
  - Địa chỉ
  - Số điện thoại
  - Email
  - Ghi chú

### 3.7 Xem Tồn kho (Inventory)
- **Chức năng:**
  - Xem danh sách hàng hóa hiện có trong kho
  - Lọc theo kho, theo loại hàng
  - Hiển thị số lượng tồn
  - Cảnh báo hàng dưới mức tối thiểu
  - Xuất báo cáo tồn kho

### 3.8 Báo cáo (Reports)
- **Chức năng:**
  - Tạo báo cáo tồn kho
  - Theo dõi nhập xuất kho theo kỳ
  - Xuất báo cáo dưới định dạng PDF/Excel
  - Thống kê giá trị tồn kho

### 3.9 Quản lý Người dùng (Chỉ Admin)

#### 3.9.1 Quản lý tài khoản
- **Chức năng:**
  - Tạo tài khoản người dùng mới
  - Chỉnh sửa thông tin người dùng
  - Xóa người dùng
  - Gán vai trò (role)
  - Kích hoạt/vô hiệu hóa tài khoản
  
- **Vai trò (Roles):**
  - `admin`: Toàn quyền quản lý hệ thống
  - `quanly_kho`: Quản lý các hoạt động kho
  - `nhan_vien_kho`: Thực hiện các phiếu nhập/xuất

### 3.10 Sao Lưu Database (Chỉ Admin)

#### 3.10.1 Chức năng sao lưu
- **Chức năng:**
  - Tạo bản sao lưu database
  - Tải về bản sao lưu
  - Xóa bản sao lưu cũ
  - Lịch sử sao lưu
  
- **Mục đích:**
  - Đảm bảo an toàn dữ liệu
  - Phục hồi dữ liệu khi cần

### 3.11 Lịch sử Hoạt động (Chỉ Admin)

#### 3.11.1 Activity Logs
- **Chức năng:**
  - Ghi lại tất cả hoạt động trong hệ thống
  - Hiển thị ai, khi nào, làm gì
  
- **Mục đích:**
  - Audit trail (dấu vết kiểm toán)
  - Truy vết thay đổi dữ liệu
  - Xác định trách nhiệm

### 3.12 Hồ sơ Cá nhân (Profile)
- **Chức năng:**
  - Xem thông tin tài khoản
  - Thay đổi mật khẩu
  - Cập nhật thông tin cá nhân

### 3.13 Thông báo (Notifications)
- **Chức năng:**
  - Nhận thông báo về các sự kiện quan trọng
  - Đánh dấu đã đọc
  - Xóa thông báo

---

## IV. CÁC TÍNH NĂNG NỔI BẬT

### 4.1 Tính năng Bảo mật
- **Xác thực đăng nhập:**
  - Sử dụng session PHP
  - Mật khẩu được mã hóa (password_hash)
  - Kiểm tra quyền truy cập theo vai trò (role-based)
  
- **Bảo vệ dữ liệu:**
  - Prepared statements chống SQL injection
  - CSRF protection (nếu được triển khai)
  - Escape output để chống XSS

### 4.2 Giao diện Responsive
- **Mobile-friendly:**
  - Navbar thu gọn trên thiết bị di động
  - Menu mobile sidebar
  - Bố cục tự động điều chỉnh
  
- **Công nghệ:**
  - Bootstrap 5.3.0
  - CSS Grid/Flexbox
  - Media queries

### 4.3 Thông báo Thời gian Thực
- **Ghi thông báo:**
  - Khi phiếu chờ duyệt
  - Khi hàng sắp hết tồn kho
  - Khi hết hạn sử dụng
  
- **Hiển thị:**
  - Badge số lượng thông báo chưa đọc
  - Danh sách thông báo

### 4.4 Xuất Báo cáo
- **Định dạng:**
  - Xuất PDF
  - Xuất Excel
  
- **Nội dung:**
  - Báo cáo tồn kho
  - Báo cáo nhập/xuất
  - Báo cáo giá trị hàng tồn

---

## V. QCivil TRÌNH VÀ QUY ĐỊNH

### 5.1 Quy trình Phiếu Nhập

```
┌─────────────────────────────────────────────────┐
│ 1. Tạo Phiếu Nhập                               │
│    - Chọn NCC, ngày nhập, v.v.                  │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 2. Thêm Chi Tiết Mặt Hàng                       │
│    - Chọn Item, số lượng, giá                   │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 3. Lưu & Gửi Duyệt                              │
│    - Trạng thái: Pending                        │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 4. Duyệt Phiếu (Admin/Quanly_kho)               │
│    - Approved: Cập nhật tồn kho                 │
│    - Rejected: Từ chối, không cập nhật tồn      │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 5. Cập Nhật Tồn Kho                             │
│    - Tồn kho tăng                               │
│    - Ghi Activity Log                           │
└─────────────────────────────────────────────────┘
```

### 5.2 Quy trình Phiếu Xuất

```
┌─────────────────────────────────────────────────┐
│ 1. Tạo Phiếu Xuất                               │
│    - Chọn Khách hàng, ngày xuất, v.v.           │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 2. Thêm Chi Tiết Mặt Hàng                       │
│    - Chọn Item từ tồn kho có sẵn                │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 3. Lưu & Gửi Duyệt                              │
│    - Trạng thái: Pending                        │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 4. Duyệt Phiếu (Admin/Quanly_kho)               │
│    - Approved: Cập nhật tồn kho                 │
│    - Rejected: Từ chối, không cập nhật tồn      │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│ 5. Cập Nhật Tồn Kho                             │
│    - Tồn kho giảm                               │
│    - Ghi Activity Log                           │
└─────────────────────────────────────────────────┘
```

---

## VI. CẤU TRÚC DATABASE

### 6.1 Các Bảng Chính

| Bảng | Mô tả |
|------|-------|
| `users` | Lưu thông tin người dùng, vai trò, quyền |
| `items` | Danh mục hàng hóa |
| `item_groups` | Nhóm hàng hóa |
| `warehouses` | Danh sách kho hàng |
| `storage_locations` | Vị trí lưu trữ trong kho |
| `inventory` | Tồn kho hiện tại |
| `import_receipts` | Phiếu nhập kho |
| `import_receipt_details` | Chi tiết từng mặt hàng trong phiếu nhập |
| `export_receipts` | Phiếu xuất kho |
| `export_receipt_details` | Chi tiết từng mặt hàng trong phiếu xuất |
| `inventory_adjustments` | Phiếu điều chỉnh tồn kho |
| `adjustment_details` | Chi tiết điều chỉnh từng mặt hàng |
| `internal_transfers` | Phiếu chuyển kho nội bộ |
| `suppliers` | Nhà cung cấp |
| `customers` | Khách hàng |
| `notifications` | Thông báo cho người dùng |
| `activity_logs` | Lịch sử hoạt động |
| `backups` | Lịch sử sao lưu |

---

## VII. HƯỚNG DẪN SỬ DỤNG

### 7.1 Đăng nhập
1. Truy cập `login.php`
2. Nhập username và password
3. Nếu là lần đầu, sử dụng:
   - Username: `admin`
   - Password: `password`

### 7.2 Quy trình cơ bản

#### 7.2.1 Thiết lập ban đầu
1. **Tạo Kho hàng**: Modules → Kho Hàng → Danh sách kho
2. **Tạo Nhóm hàng**: Modules → Hàng Hóa → Nhóm hàng hóa
3. **Tạo Danh mục hàng hóa**: Modules → Hàng Hóa → Danh mục hàng hóa
4. **Tạo Nhà cung cấp**: Modules → Đối Tác → Nhà cung cấp
5. **Tạo Khách hàng**: Modules → Đối Tác → Khách hàng
6. **Tạo Tài khoản Người dùng**: Modules → Người Dùng (Chỉ Admin)

#### 7.2.2 Nhập hàng vào kho
1. Modules → Nhập Xuất → Phiếu nhập kho → Thêm mới
2. Chọn Nhà cung cấp
3. Thêm chi tiết từng hàng (item, số lượng, giá)
4. Lưu & Gửi duyệt
5. Admin duyệt phiếu
6. Tồn kho tự động cập nhật

#### 7.2.3 Xuất hàng từ kho
1. Modules → Nhập Xuất → Phiếu xuất kho → Thêm mới
2. Chọn Khách hàng
3. Thêm chi tiết từng hàng từ tồn kho có sẵn
4. Lưu & Gửi duyệt
5. Admin duyệt phiếu
6. Tồn kho tự động trừ đi

#### 7.2.4 Xem tồn kho
1. Modules → Tồn Kho
2. Xem danh sách hàng hóa hiện có
3. Lọc theo kho nếu cần
4. Xuất báo cáo PDF hoặc Excel

---

## VIII. THỐNG KÊ VÀ DASHBOARD

### 8.1 Dashboard Chính

Trang Dashboard hiển thị:
- **Tổng số hàng hóa**: Số lượng mặt hàng trong hệ thống
- **Tổng số kho**: Số kho được quản lý
- **Tổng số vị trí lưu trữ**: Số vị trí trong các kho
- **Giá trị tồn kho**: Tổng giá trị hàng hóa hiện có
- **Phiếu nhập chờ duyệt**: Số phiếu nhập đang chờ approval
- **Phiếu xuất chờ duyệt**: Số phiếu xuất đang chờ approval
- **Chuyển kho chưa hoàn thành**: Phiếu chuyển đang thực hiện
- **Hàng tồn kho thấp**: Cảnh báo hàng dưới mức tối thiểu

---

## IX. QUẢN LÝ LỖI VÀ KHẮC PHỤC

### 9.1 Các lỗi thường gặp

| Lỗi | Nguyên nhân | Cách khắc phục |
|-----|-----------|---------------|
| Không kết nối DB | Config sai hoặc MySQL không chạy | Kiểm tra config/database.php, khởi động MySQL |
| 404 Not Found | URL hoặc BASE_URL sai | Kiểm tra config/config.php |
| Session timeout | Phiên hết hạn | Đăng nhập lại |
| Không có quyền truy cập | Vai trò không phù hợp | Kiểm tra role của user |
| Tồn kho sai | Phiếu nhập/xuất chưa duyệt | Duyệt phiếu để cập nhật tồn |

### 9.2 Backup & Restore
- **Backup**: Admin → Modules → Sao Lưu → Tạo Backup
- **Restore**: Sử dụng file backup (`*.sql`) để restore database

---

## X. BẢO TRỢ VÀ PHÁT TRIỂN

### 10.1 Bảo dưỡng hệ thống
- **Thường xuyên:**
  - Kiểm tra logs (activity_logs)
  - Cập nhật dữ liệu đối tác
  - Theo dõi tồn kho
  
- **Định kỳ:**
  - Sao lưu database (hàng tuần)
  - Kiểm tra tài khoản người dùng
  - Cập nhật mật khẩu

### 10.2 Phát triển tương lai
Các tính năng có thể thêm vào:
- Quản lý lô (batch) hàng hóa
- Tích hợp barcode scanning
- Báo cáo nâng cao (pivot table, chart, v.v.)
- API RESTful
- Mobile app (iOS/Android)
- Tích hợp thanh toán (nếu bán hàng)
- Tích hợp email notification
- Dashboard analytics nâng cao

---

## XI. KẾT LUẬN

Hệ thống Quản Lý Kho Hàng được thiết kế để:
- ✅ Cung cấp giao diện thân thiện, dễ sử dụng
- ✅ Quản lý toàn bộ quy trình kho hiệu quả
- ✅ Đảm bảo dữ liệu chính xác và an toàn
- ✅ Cung cấp báo cáo chi tiết
- ✅ Hỗ trợ nhiều loại kho và mặt hàng
- ✅ Tích hợp hệ thống vai trò và quyền hạn

Hệ thống này sẵn sàng để triển khai cho các doanh nghiệp cần quản lý kho hàng một cách chuyên nghiệp.

---

**Ngày lập báo cáo:** 22/01/2026  
**Phiên bản hệ thống:** 1.0  
**Tác giả báo cáo:** Development Team
