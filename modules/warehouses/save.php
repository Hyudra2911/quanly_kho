<?php
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$id = $_POST['id'] ?? null;
$code = trim($_POST['code'] ?? '');
$name = trim($_POST['name'] ?? '');
$address = trim($_POST['address'] ?? '');
$latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
$manager_id = $_POST['manager_id'] ?? null;
$status = isset($_POST['status']) ? 1 : 0;

if (empty($code) || empty($name)) {
    $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
} else {
    if ($id) {
        $stmt = $db->prepare("UPDATE warehouses SET code = ?, name = ?, address = ?, latitude = ?, longitude = ?, manager_id = ?, status = ? WHERE id = ?");
        $stmt->execute([$code, $name, $address, $latitude, $longitude, $manager_id ?: null, $status, $id]);
        logActivity('update', 'warehouses', $id, "Cập nhật kho: $code");
    } else {
        $stmt = $db->prepare("INSERT INTO warehouses (code, name, address, latitude, longitude, manager_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $address, $latitude, $longitude, $manager_id ?: null, $status]);
        logActivity('create', 'warehouses', $db->lastInsertId(), "Thêm kho: $code");
    }
    $_SESSION['success'] = $id ? 'Cập nhật thành công' : 'Thêm thành công';
}

header('Location: index.php');
exit();
?>
