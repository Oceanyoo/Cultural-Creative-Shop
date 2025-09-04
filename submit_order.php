<?php
session_start();
include 'connection.php'; // 確保這個文件包含您的資料庫連接 ($conn)

header('Content-Type: application/json'); // 設定響應頭為 JSON

// 檢查使用者是否已登入並有 member_id
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['status' => 'error', 'message' => '請先登入。']);
    exit;
}

$member_id = $_SESSION['member_id'];
$conn->begin_transaction(); // 開始事務處理，確保操作的原子性

try {
    // 1. 查詢購物車中所有 choose 為 1 的商品
    $sql_fetch_chosen_items = "SELECT c.product_id, c.amount, p.price
                               FROM shopping_cart c
                               JOIN all_products p ON c.product_id = p.id
                               WHERE c.member_id = ? AND c.choose = 1";
    $stmt_fetch = $conn->prepare($sql_fetch_chosen_items);
    if (!$stmt_fetch) {
        throw new Exception("Prepare fetch SQL 失敗: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt_fetch->bind_param("i", $member_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $chosen_items = $result_fetch->fetch_all(MYSQLI_ASSOC);
    $stmt_fetch->close();

    if (empty($chosen_items)) {
        throw new Exception("購物車中沒有選取的商品可以送出訂單。");
    }

    // --- 這裡是最重要的修改部分 ---
    // 2. 生成一個唯一的「訂單批次號碼」
    //    您可以從資料庫獲取當前最大的 order_batch_id + 1
    //    或者使用時間戳+會員ID (雖然不是簡單的1,2,3...但能保證唯一性且同次訂單一致)
    //    如果您堅持 1, 2, 3 這樣遞增，且要保證唯一性，需要額外的鎖定機制或從專門的計數器表獲取。
    //    這裡我提供一個從資料庫獲取最大值並加 1 的方法，但請注意並發問題 (多個用戶同時下單可能獲取相同號碼)。
    //    在小型應用中可能足夠，大型應用建議使用方案一的獨立 orders 表或資料庫序列。

    $sql_get_max_order_batch_id = "SELECT MAX(order_id) AS max_id FROM order_records";
    $result_max_id = $conn->query($sql_get_max_order_batch_id);
    $row_max_id = $result_max_id->fetch_assoc();
    $current_max_id = $row_max_id['max_id'];
    $new_order_batch_id = ($current_max_id === null) ? 1 : $current_max_id + 1;

    // 如果要更穩健地處理並發，可以考慮一個單獨的 'order_counters' 表：
    // CREATE TABLE order_counters (id INT PRIMARY KEY, current_order_id INT);
    // INSERT INTO order_counters VALUES (1, 0);
    // 然後用 UPDATE order_counters SET current_order_id = LAST_INSERT_ID(current_order_id + 1) WHERE id = 1;
    // 獲取 LAST_INSERT_ID(); 這種方式更安全。
    // 但為了簡化和滿足您在單一表的需求，我們暫時用 MAX + 1。

    // 3. 準備插入 order_records
    //    新增 order_batch_id 欄位
    $sql_insert_order = "INSERT INTO order_records (member_id, order_id, product_id, amount, total_money) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert_order);
    if (!$stmt_insert) {
        throw new Exception("Prepare insert SQL 失敗: (" . $conn->errno . ") " . $conn->error);
    }

    foreach ($chosen_items as $item) {
        $product_id = $item['product_id'];
        $amount = $item['amount'];
        $price_per_item = $item['price'];
        $item_total_money = $price_per_item * $amount; // 單一商品項目的總金額

        // 綁定時，使用 $new_order_batch_id
        $stmt_insert->bind_param("iiiid", $member_id, $new_order_batch_id, $product_id, $amount, $item_total_money); // 注意類型字串和變數順序
        if (!$stmt_insert->execute()) {
            throw new Exception("插入訂單記錄失敗: " . $stmt_insert->error);
        }
    }
    $stmt_insert->close();

    // 4. 準備從 shopping_cart 移除已提交的商品
    $sql_delete_cart_items = "DELETE FROM shopping_cart WHERE member_id = ? AND choose = 1";
    $stmt_delete = $conn->prepare($sql_delete_cart_items);
    if (!$stmt_delete) {
        throw new Exception("Prepare delete SQL 失敗: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt_delete->bind_param("i", $member_id);
    if (!$stmt_delete->execute()) {
        throw new Exception("從購物車移除商品失敗: " . $stmt_delete->error);
    }
    $stmt_delete->close();

    $conn->commit(); // 提交事務
    // 返回新的 order_batch_id
    echo json_encode(['status' => 'success', 'message' => '訂單已成功送出並移除購物車商品。', 'order_id' => $new_order_batch_id]);

} catch (Exception $e) {
    $conn->rollback(); // 發生錯誤時回滾事務
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>