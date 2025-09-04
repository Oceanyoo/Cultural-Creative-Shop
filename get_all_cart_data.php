<?php
session_start();
include 'connection.php'; // 確保資料庫連線

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

$user_account = $_SESSION['user'];

// 取得會員ID
$stmt_member = $conn->prepare("SELECT id FROM member WHERE account = ?");
$stmt_member->bind_param("s", $user_account);
$stmt_member->execute();
$result_member = $stmt_member->get_result();
$member = $result_member->fetch_assoc();
$stmt_member->close();

$memberId = $member['id'] ?? null;

if (!$memberId) {
    echo json_encode(['status' => 'error', 'message' => '找不到會員ID']);
    exit;
}

$cart_items_data = [];
$sql = "
    SELECT sc.product_id, sc.amount, sc.choose, sc.table_name,
           p.price -- 直接從 all_products 獲取 price
    FROM shopping_cart sc
    JOIN all_products p ON sc.product_id = p.id
    WHERE sc.member_id = ?
";
$stmt_get_cart = $conn->prepare($sql);
if (!$stmt_get_cart) {
    echo json_encode(['status' => 'error', 'message' => '準備查詢購物車資料語句失敗: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt_get_cart->bind_param("i", $memberId);
$stmt_get_cart->execute();
$result_get_cart = $stmt_get_cart->get_result();

while ($row = $result_get_cart->fetch_assoc()) {
    $row['price'] = (float)$row['price']; // 確保價格是浮點數
    $cart_items_data[] = $row;
}
$stmt_get_cart->close();
$conn->close();

echo json_encode([
    'status' => 'success',
    'cart_data' => $cart_items_data
]);
?>