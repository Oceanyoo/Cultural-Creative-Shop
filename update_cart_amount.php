<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { // 或者檢查 $_SESSION['member_id']
    echo json_encode(['success' => false, 'msg' => '未登入']);
    exit;
}

// 從 $_SESSION['user'] 獲取會員帳號 (如果 member_id 是從這裡查來的)
$user_account = $_SESSION['user'];

require 'connection.php';

// 從資料庫查詢對應的 member_id
$stmt_member = $conn->prepare("SELECT id FROM member WHERE account = ?");
if (!$stmt_member) {
    echo json_encode(['success' => false, 'msg' => '準備查詢會員ID語句失敗: ' . $conn->error]);
    exit;
}
$stmt_member->bind_param("s", $user_account);
$stmt_member->execute();
$result_member = $stmt_member->get_result();
$member = $result_member->fetch_assoc();
$stmt_member->close();

if (!$member || !isset($member['id'])) {
    echo json_encode(['success' => false, 'msg' => '無效的會員帳號或會員ID未找到']);
    exit;
}
$member_id = $member['id'];

$data = json_decode(file_get_contents('php://input'), true);
// 從前端接收 product_id，而不是 cart_id
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$amount = isset($data['amount']) ? intval($data['amount']) : 0;

// 檢查 product_id 和 amount 是否有效
if ($product_id <= 0 || $amount <= 0) { // 數量至少為1
    echo json_encode(['success' => false, 'msg' => '無效的商品ID或數量']);
    exit;
}

// 更新 SQL 語句，使用 product_id 和 member_id
$sql = "UPDATE shopping_cart SET amount = ? WHERE member_id = ? AND product_id = ?"; // 修改這裡
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'msg' => '準備 UPDATE 語句失敗: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iii", $amount, $member_id, $product_id); // 綁定參數順序要對應
$execute_success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $execute_success]);
?>