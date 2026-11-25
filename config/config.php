<?php
// Cấu hình chung
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Include database
require_once __DIR__ . '/database.php';

// Đường dẫn
define('BASE_URL', 'http://localhost/quanly_kho/');
define('ROOT_PATH', __DIR__ . '/../');

// Google Maps API Key
define('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY_HERE');

// Groq Chat API
define('GROQ_API_KEY', '');
define('GROQ_MODEL', 'llama-3.1-8b-instant');

// Cấu hình phân quyền
define('ROLES', [
    'admin' => 'Quản trị viên',
    'quanly_kho' => 'Quản lý kho',
    'nhanvien_nhapkho' => 'Nhân viên nhập kho',
    'ketoan' => 'Kế toán'
]);

// Kiểm tra đăng nhập
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Kiểm tra quyền
function checkRole($allowed_roles = []) {
    checkLogin();
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ' . BASE_URL . 'index.php?error=permission_denied');
        exit();
    }
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return !empty($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
}

// Lấy thông tin user hiện tại
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Ghi log hoạt động
function logActivity($action, $table_name = null, $record_id = null, $description = null) {
    $db = getDB();
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $table_name, $record_id, $description, $ip_address]);
}

// Format số
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

// Format ngày
function formatDate($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    return date('d/m/Y H:i:s', strtotime($datetime));
}

function ensurePasswordResetTable() {
    $db = getDB();
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_reset (user_id),
        KEY idx_token_hash (token_hash),
        CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->exec($sql);
}

function logSystemError($message, $context = []) {
    $log_dir = ROOT_PATH . 'logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    $entry = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_id' => $_SESSION['user_id'] ?? null
    ];

    file_put_contents(
        $log_dir . '/app-error.log',
        json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
}
?>
