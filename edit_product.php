<?php
include 'connection.php'; // 引入資料庫連線

$error = '';
$success = '';

// 檢查網址是否有帶 id
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 撈出該產品資料
    $sql = "SELECT * FROM all_products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $name = $row['name'];
        $price = $row['price'];
        $description = $row['description'];
        $type = $row['type'];
    } else {
        $error = "找不到該產品資料";
    }
} else {
    $error = "未指定產品 ID";
}

// 處理表單送出（更新產品資料）
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $name = $_POST["name"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $type = $_POST["type"];

    // 更新資料庫
    $update_sql = "UPDATE all_products
                   SET name='$name', price='$price', description='$description', type='$type' 
                   WHERE id=$id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: admin.php"); // 修改成功返回後台
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
    <title>編輯產品資料</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header text-white text-center" style="background-color:rgb(77, 102, 123);">
                    <h4>編輯產品資料</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (empty($error)): ?>
                        <form method="POST" action="edit_product.php?id=<?= htmlspecialchars($id) ?>">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                            <div class="mb-3">
                                <label for="name" class="form-label">商品名稱</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">價格</label>
                                <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">商品描述</label>
                                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($description) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">商品類型</label>
                                <input type="text" name="type" class="form-control" value="<?= htmlspecialchars($type) ?>" required>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary" style="background-color:rgb(53, 97, 135); border: none;">儲存修改</button>
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
