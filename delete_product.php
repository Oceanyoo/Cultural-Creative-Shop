<?php
include 'connection.php'; // 資料庫連線

$error = ''; // 錯誤訊息初始化

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // 安全轉整數避免 SQL 注入

    // 先取得該商品的 type
    $check_sql = "SELECT * FROM all_products WHERE id = '$id'";
    $check_result = mysqli_query($conn, $check_sql);

    if (!$check_result) {
        die("查詢錯誤：" . mysqli_error($conn));
    }

    if (mysqli_num_rows($check_result) === 0) {
        $error = "找不到該商品，無法刪除。";
    } else {
        $product = mysqli_fetch_assoc($check_result);
        $type = intval($product['type']);

        // 依據 type 確定使用 pr 或 pr2 表
        $target_table = ($type >= 1 && $type <= 3) ? 'pr' : 'pr2';

        // 刪除圖片資料
        $delete_img_sql = "DELETE FROM all_products_img WHERE product_id = '$id'";
        mysqli_query($conn, $delete_img_sql);

        // 刪除 pr 或 pr2 的對應資料
        $delete_pr_sql = "DELETE FROM $target_table WHERE id = '$id'";
        mysqli_query($conn, $delete_pr_sql);

        // 最後刪除主產品表
        $delete_product_sql = "DELETE FROM all_products WHERE id = '$id'";
        if (mysqli_query($conn, $delete_product_sql)) {
            header("Location: admin.php"); // 成功刪除後跳轉
            exit();
        } else {
            $error = "刪除失敗：" . mysqli_error($conn);
        }
    }
} else {
    $error = "未提供商品 ID，無法進行刪除。";
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>刪除商品</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-white text-center bg-danger">
                        <h4>刪除商品</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                            <div class="text-center">
                                <a href="admin.php" class="btn btn-secondary">返回後端管理</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
