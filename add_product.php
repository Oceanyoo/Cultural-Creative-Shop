<?php
include 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $img_path = $_POST["img_path"];
    $type = intval($_POST["type"]);

    // 1. 插入 all_products（不指定 id，由資料庫自動生成）
    $insert_product_sql = "INSERT INTO all_products (name, price, description, type)
                           VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_product_sql);
    mysqli_stmt_bind_param($stmt, "sdsi", $name, $price, $description, $type);
    $success_product = mysqli_stmt_execute($stmt);

    if ($success_product) {
        // 取得剛剛新增的商品 id
        $new_product_id = mysqli_insert_id($conn);

        // 2. 插入 all_products_img，使用剛取得的 product_id
        $insert_img_sql = "INSERT INTO all_products_img (product_id, img_path, is_main)
                           VALUES (?, ?, 1)";//這裡已經有主動設定為1了
        $stmt_img = mysqli_prepare($conn, $insert_img_sql);
        mysqli_stmt_bind_param($stmt_img, "is", $new_product_id, $img_path);
        $success_img = mysqli_stmt_execute($stmt_img);

        // 3. 根據 type 判斷要插入 pr 或 pr2
        if ($type >= 1 && $type <= 3) {
            $insert_pr_sql = "INSERT INTO pr (id, name, price, img, type)
                              VALUES (?, ?, ?, ?, ?)";
            $stmt_pr = mysqli_prepare($conn, $insert_pr_sql);
            mysqli_stmt_bind_param($stmt_pr, "isdsi", $new_product_id, $name, $price, $img_path, $type);
            $success_extra = mysqli_stmt_execute($stmt_pr);
        } elseif ($type >= 4 && $type <= 6) {
            $insert_pr2_sql = "INSERT INTO pr2 (id, name, price, img, type)
                               VALUES (?, ?, ?, ?, ?)";
            $stmt_pr2 = mysqli_prepare($conn, $insert_pr2_sql);
            mysqli_stmt_bind_param($stmt_pr2, "isdsi", $new_product_id, $name, $price, $img_path, $type);
            $success_extra = mysqli_stmt_execute($stmt_pr2);
        } else {
            $error = "無效的 type 值（只能為 1-6）。";
        }

        if ($success_img && isset($success_extra) && $success_extra) {
            header("Location: admin.php");
            exit();
        } else {
            $error = "新增失敗：" . mysqli_error($conn);
        }

    } else {
        $error = "新增商品失敗：" . mysqli_error($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>新增商品</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header text-white text-center" style="background-color:rgb(77, 102, 123);">
                    <h4>新增商品</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST" action="add_product.php">
                        <div class="mb-3">
                            <label for="name" class="form-label">商品名稱</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">價格</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">商品描述</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="img_path" class="form-label">圖片路徑</label>
                            <input type="text" name="img_path" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">類型 (1~3為手繪周邊;4~6為串珠商品)</label>
                            <input type="number" name="type" class="form-control" min="1" max="6" required>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary" style="background-color:rgb(53, 97, 135); border: none;">
                                新增商品
                            </button>
                        </div>
                    </form>
                    <hr>
                    <p class="text-center"><a href="admin.php">返回後端管理</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

