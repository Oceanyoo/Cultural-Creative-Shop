<?php
include 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reviewer_account = $_POST["reviewer_name"]; // 用 Account 查
    $product_id = intval($_POST["product_id"]);
    $content = $_POST["content"];
    $rating = intval($_POST["rating"]);

    // 1. 從 member 表找出 Account 對應的 id
    $find_member_sql = "SELECT id FROM member WHERE Account = ?";
    $stmt_member = mysqli_prepare($conn, $find_member_sql);
    mysqli_stmt_bind_param($stmt_member, "s", $reviewer_account);
    mysqli_stmt_execute($stmt_member);
    mysqli_stmt_bind_result($stmt_member, $member_id);

    if (mysqli_stmt_fetch($stmt_member)) {
        mysqli_stmt_close($stmt_member);

        // 2. 確認商品是否存在
        $check_product_sql = "SELECT name FROM all_products WHERE id = ?";
        $stmt_product = mysqli_prepare($conn, $check_product_sql);
        mysqli_stmt_bind_param($stmt_product, "i", $product_id);
        mysqli_stmt_execute($stmt_product);
        mysqli_stmt_store_result($stmt_product);

        if (mysqli_stmt_num_rows($stmt_product) == 0) {
            $error = "找不到對應的商品。";
        } else {
            mysqli_stmt_close($stmt_product);

            // 3. 寫入 review
            $insert_sql = "INSERT INTO reviews (member_id, product_id, review_text, rating)
                           VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, "iisi", $member_id, $product_id, $content, $rating);

            if (mysqli_stmt_execute($stmt_insert)) {
                header("Location: admin.php");
                exit();
            } else {
                $error = "新增評論失敗：" . mysqli_error($conn);
            }
        }
    } else {
        $error = "找不到該會員帳號。";
    }
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>新增評論</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header text-white text-center" style="background-color:rgb(77, 102, 123);">
                    <h4>新增評論</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST" action="add_review.php">
                        <div class="mb-3">
                            <label for="reviewer_name" class="form-label">會員名稱</label>
                            <input type="text" name="reviewer_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">選擇商品</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">請選擇商品</option>
                                <?php
                                $product_sql = "SELECT id, name FROM all_products";
                                $result = mysqli_query($conn, $product_sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$row['id']}'>{$row['id']} - {$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">評論內容</label>
                            <textarea name="content" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="rating" class="form-label">評分 (1~5 顆星)</label>
                            <select name="rating" class="form-select" required>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?>/5</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary" style="background-color:rgb(53, 97, 135); border: none;">
                                新增評論
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
