<?php
include 'connection.php'; // 引入資料庫連線

$error = '';
$success = '';

// 檢查網址是否有帶 id
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 撈出該會員資料
    $sql = "SELECT * FROM member WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $account = $row['Account'];
        $password = $row['Password'];
        $birthday = $row['Birthday'];
        $email = $row['E-mail'];
    } else {
        $error = "找不到該會員資料";
    }
} else {
    $error = "未指定會員 ID";
}

// 處理表單送出（更新會員資料）
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $account = $_POST["account"];
    $password = $_POST["password"];
    $birthday = $_POST["birthday"];
    $email = $_POST["email"];

    // 更新資料庫
    $update_sql = "UPDATE member 
                   SET Account='$account', Password='$password', Birthday='$birthday', `E-mail`='$email' 
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
    <title>編輯會員資料</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header text-white text-center" style="background-color:rgb(77, 102, 123);">
                    <h4>編輯會員資料</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (empty($error)): ?>
                        <form method="POST" action="edit_member.php">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                            <div class="mb-3">
                                <label for="account" class="form-label">帳號</label>
                                <input type="text" name="account" class="form-control" value="<?= htmlspecialchars($account) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">密碼</label>
                                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($password) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="birthday" class="form-label">生日</label>
                                <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($birthday) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                            </div>
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
