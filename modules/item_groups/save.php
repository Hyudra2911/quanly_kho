<?php
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$id = $_POST['id'] ?? null;
$code = trim($_POST['code'] ?? '');
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($code) || empty($name)) {
    $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
} else {
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE item_groups SET code = ?, name = ?, description = ? WHERE id = ?");
        $stmt->execute([$code, $name, $description, $id]);
        logActivity('update', 'item_groups', $id, "Cập nhật nhóm hàng: $code");
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO item_groups (code, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$code, $name, $description]);
        logActivity('create', 'item_groups', $db->lastInsertId(), "Thêm nhóm hàng: $code");
    }
    $_SESSION['success'] = $id ? 'Cập nhật thành công' : 'Thêm thành công';
}

header('Location: index.php');
exit();
?>
