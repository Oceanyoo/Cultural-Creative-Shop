<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['member_id'])) {
    http_response_code(403);
    echo "請先登入";
    exit;
}

$member_id = $_SESSION['member_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($product_id > 0) {
    $stmt = $conn->prepare("DELETE FROM favorite WHERE member_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $member_id, $product_id);
    if ($stmt->execute()) {
        echo "取消成功";
    } else {
        http_response_code(500);
        echo "刪除失敗";
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo "資料錯誤";
}
?>
