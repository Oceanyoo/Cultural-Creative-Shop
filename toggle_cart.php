<?php
session_start();
include 'connection.php';

// 開啟錯誤顯示，方便除錯
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 設定回傳的內容類型為 JSON
header('Content-Type: application/json');

// 檢查使用者是否已登入，如果沒有則回傳 403 錯誤
if (!isset($_SESSION['user'])) {
    http_response_code(403); // HTTP 403 Forbidden
    echo json_encode(['status' => 'error', 'message' => '請先登入 (帳號資訊未設定)']);
    exit;
}

$user_account = $_SESSION['user']; // 取得登入的使用者帳號

// 從 member 資料表取得 member_id
$stmt = $conn->prepare("SELECT id FROM member WHERE account = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '準備查詢會員ID語句失敗: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $user_account); // 綁定使用者帳號
$stmt->execute(); // 執行查詢
$result = $stmt->get_result(); // 取得查詢結果
$member = $result->fetch_assoc(); // 獲取會員資料
$stmt->close(); // 關閉語句

$memberId = $member['id'] ?? null; // 取得會員ID，如果沒有則為 null

// 從 POST 請求取得 product_id, action, table_name
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;
// 預設的 table_name 可以根據你的商品來源設定，例如 'pr', 'pr2'
// 請確保傳遞的 table_name 也在允許的列表中
$tableName = $_POST['table_name'] ?? 'pr2'; // 假設預設為 pr2 或你實際的商品表名稱

// 驗證輸入參數
// 檢查 memberId, productId 是否存在且有效，並且 tableName 在允許的列表中
// (請根據你的實際需求調整允許的 table_name 列表)
//if (!$memberId || !$productId || !in_array($tableName, ['pr', 'pr2'])) {

// 對於 'remove' 操作，可以放寬 table_name 的驗證，如果它確實不需要
if (!$memberId || !$productId || ($action !== 'remove' && !in_array($tableName, ['pr', 'pr2']))){
    http_response_code(400); // HTTP 400 Bad Request
    echo json_encode(['status' => 'error', 'message' => '缺少必要的參數或參數無效 (會員ID, 商品ID 或 資料表名稱)']);
    exit;
}

// 根據 action 執行操作
if ($action === 'add') {
    // 預設數量和選中狀態
    $amount = 1;
    $choose = 0;

    // 使用 INSERT IGNORE 嘗試插入資料
    // 如果 member_id 和 product_id 的組合已經存在 (因為是主鍵)，則會忽略此次插入，不產生錯誤
    // 注意：如果已存在，amount 和 choose 不會更新，將維持首次插入時的值
    $stmt = $conn->prepare("INSERT IGNORE INTO shopping_cart (member_id, product_id, amount, choose, table_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '準備 INSERT 語句失敗: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("iiiis", $memberId, $productId, $amount, $choose, $tableName); // 's' for table_name string
    if ($stmt->execute()) {
        // 如果影響的行數為 0，表示是 IGNORE (已存在)，否則為新增成功
        if ($stmt->affected_rows === 0) {
            echo json_encode(['status' => 'info', 'message' => '商品已在購物車中', 'action_performed' => 'already_in_cart']);
        } else {
            echo json_encode(['status' => 'success', 'message' => '商品已加入購物車', 'action_performed' => 'added']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '執行 INSERT 失敗: ' . $stmt->error]);
    }
} elseif ($action === 'remove') {
    // 移除購物車商品
    $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE member_id = ? AND product_id = ? ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '準備 DELETE 語句失敗: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ii", $memberId, $productId); // 's' for table_name string
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => '商品已從購物車移除', 'action_performed' => 'removed']);
        } else {
            echo json_encode(['status' => 'info', 'message' => '購物車中沒有此商品', 'action_performed' => 'not_in_cart']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '執行 DELETE 失敗: ' . $stmt->error]);
    }
} else {
    // 無效的操作
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '無效的操作 (action)']);
}

$conn->close(); // 關閉資料庫連線
?>