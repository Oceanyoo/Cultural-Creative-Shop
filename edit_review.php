<?php
include 'connection.php'; // 引入資料庫連線

$error = '';
$success = '';
$review_text = '';

// 檢查是否有帶 review ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 使用 JOIN 撈出完整資料（含商品名稱、會員帳號）
    $sql = "
        SELECT reviews.*, all_products.name AS product_name, member.Account AS member_account
        FROM reviews
        JOIN all_products ON reviews.product_id = all_products.id
        JOIN member ON reviews.member_id = member.id
        WHERE reviews.id = $id
    ";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $member_account = $row['member_account'];
        $product_name = $row['product_name'];
        $review_text = $row['review_text'];
        $rating = $row['rating'];
        $created_at = $row['created_at'];
    } else {
        $error = "找不到該筆評論資料";
    }
} else {
    $error = "未指定評論 ID";
}

// 處理表單送出（只允許修改 review_text）
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $review_text = mysqli_real_escape_string($conn, $_POST["review_text"]);

    $update_sql = "UPDATE reviews SET review_text='$review_text' WHERE id=$id";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: admin.php"); // 成功後返回後台或列表頁
        exit();
    } else {
        $error = "更新失敗：" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>編輯商品評論</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header text-white text-center" style="background-color:rgb(77, 102, 123);">
                    <h4>編輯商品評論</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php elseif (!empty($id)): ?>
                        <form method="POST" action="edit_review.php">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>會員</th>
                                        <th>商品名稱</th>
                                        <th>內容</th>
                                        <th>評分</th>
                                        <th>留言時間</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= htmlspecialchars($member_account) ?></td>
                                        <td><?= htmlspecialchars($product_name) ?></td>
                                        <td>
                                            <textarea name="review_text" class="form-control" rows="4" required><?= htmlspecialchars($review_text) ?></textarea>
                                        </td>
                                        <td><?= htmlspecialchars($rating) ?> / 5</td>
                                        <td><?= htmlspecialchars($created_at) ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary" style="background-color:rgb(53, 97, 135); border: none;">
                                    儲存修改
                                </button>
                            </div>
                        </form>
                        <hr>
                        <p class="text-center"><a href="admin.php">返回後端管理</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
