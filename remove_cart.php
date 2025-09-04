<?php   
session_start();
include 'connection.php'; // 確保 connection.php 檔案路徑正確

// --- 開啟錯誤報告 (僅用於開發階段，上線前請移除或註解掉) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ------------------------------------------------------------

header('Content-Type: application/json'); // 移除購物車也應該回傳 JSON，以便前端處理

// 檢查是否登入（判斷 $_SESSION['user'] 是否存在）
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

// 從 $_SESSION['user'] 獲取會員帳號
$user_account = $_SESSION['user'];

// 從資料庫查詢對應的 member_id
$stmt = $conn->prepare("SELECT id FROM member WHERE account = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '準備查詢會員ID語句失敗: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $user_account);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close(); // 關閉查詢 member_id 的語句

// 如果沒有找到對應的 member_id
if (!$member || !isset($member['id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '無效的會員帳號或會員ID未找到']);
    exit;
}

$member_id = $member['id']; // 成功獲取到 member_id


$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($product_id > 0) {
    $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE member_id = ? AND product_id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '準備 DELETE 語句失敗: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ii", $member_id, $product_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => '已從購物車移除']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '執行 DELETE 失敗: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '資料錯誤: product_id 無效']);
}
?>