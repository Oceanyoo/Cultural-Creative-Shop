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

$productId = $_POST['product_id'] ?? null;
$choose = $_POST['choose'] ?? null; // '0' 或 '1'

error_log("Received product_id: " . var_export($productId, true));
error_log("Received choose: " . var_export($choose, true));

if (is_null($productId) || is_null($choose) || !in_array($choose, [0, 1])) {
    echo json_encode(['status' => 'error', 'message' => '無效的商品ID或選取狀態']);
    exit;
}

// 1. 更新購物車中該商品的 choose 狀態
$stmt_update = $conn->prepare("UPDATE shopping_cart SET `choose` = ? WHERE member_id = ? AND product_id = ?");
if (!$stmt_update) {
    echo json_encode(['status' => 'error', 'message' => '準備更新選取狀態語句失敗: ' . $conn->error]);
    exit;
}
$stmt_update->bind_param("iii", $choose, $memberId, $productId);

if (!$stmt_update->execute()) {
    echo json_encode(['status' => 'error', 'message' => '執行更新選取狀態失敗: ' . $stmt_update->error]);
    $stmt_update->close();
    $conn->close();
    exit;
}
$stmt_update->close();

// 2. 獲取所有購物車商品的最新數據 (包括 amount, choose 和 price)
$cart_items_data = [];
$sql = "
    SELECT sc.product_id, sc.amount, sc.choose, sc.table_name,
           p.price 
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
    'message' => '購物車選取狀態更新成功，並取得最新購物車數據',
    'cart_data' => $cart_items_data
]);
?>