<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

$warehouse_id = $_GET['warehouse_id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT id, code, name FROM storage_locations WHERE warehouse_id = ? AND status = 1 ORDER BY name");
$stmt->execute([$warehouse_id]);
$locations = $stmt->fetchAll();

echo json_encode($locations);
