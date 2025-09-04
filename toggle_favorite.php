<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$user = $_SESSION['user'];
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;
$tableName = $_POST['table_name'] ?? 'pr';  // 預設是 pr

// 取得 member_id
$stmt = $conn->prepare("SELECT id FROM member WHERE account = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$memberId = $member['id'] ?? null;

if ($memberId && $productId && in_array($tableName, ['pr', 'pr2'])) {
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO favorite (member_id, product_id, table_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $memberId, $productId, $tableName);
        $stmt->execute();
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM favorite WHERE member_id = ? AND product_id = ? AND table_name = ?");
        $stmt->bind_param("iis", $memberId, $productId, $tableName);
        $stmt->execute();
    }
}
?>
