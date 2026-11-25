<?php
require_once '../config/config.php';
checkLogin();

header('Content-Type: application/json');

$item_id = $_GET['item_id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT id, batch_code FROM batches WHERE item_id = ? AND status = 1 AND current_quantity > 0 ORDER BY batch_code");
$stmt->execute([$item_id]);
$batches = $stmt->fetchAll();

echo json_encode($batches);
