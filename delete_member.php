<?php
include 'connection.php'; // 資料庫連線

$error = ''; // 預設錯誤訊息為空

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // 保險處理：轉整數

    // 檢查帳號是否存在
    $check_sql = "SELECT * FROM member WHERE id = '$id'";
    $check_result = mysqli_query($conn, $check_sql);

    if (!$check_result) {
        die("查詢錯誤：" . mysqli_error($conn));
    }

    if (mysqli_num_rows($check_result) === 0) {
        $error = "找不到該帳號，無法刪除。";
    } else {
        $delete_sql = "DELETE FROM member WHERE id = '$id'";
        if (mysqli_query($conn, $delete_sql)) {
            header("Location: admin.php"); // 成功刪除後返回管理頁面
            exit();
        } else {
            $error = "刪除失敗：" . mysqli_error($conn);
        }
    }
} else {
    $error = "未提供帳號，無法進行刪除。";
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>刪除會員</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-white text-center bg-danger">
                        <h4>刪除會員</h4>
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
