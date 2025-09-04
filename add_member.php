<?php
include 'connection.php'; // 資料庫連線

$error = ''; // 預設錯誤訊息為空

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $_POST["account"];
    $password = $_POST["password"];
    $birthday = $_POST["birthday"];
    $email = $_POST["email"];

    // 檢查是否重複帳號
    $check_sql = "SELECT * FROM member WHERE Account = '$account'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "帳號已存在，請換一個帳號。";
    } else {
        $sql = "INSERT INTO member (Account, Password, Birthday, `E-mail`) 
                VALUES ('$account', '$password', '$birthday', '$email')";

        if (mysqli_query($conn, $sql)) {
            header("Location: admin.php"); // 成功新增後返回管理頁面
            exit();
        } else {
            $error = "新增失敗：" . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>會員新增</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"><!--新加的-->

    <style>
        /* 自訂 Bootstrap 輪播箭頭樣式 */

        /* 1. 移除原本的綠色箭頭圖標與背景 */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-image: none !important;
            background-color: #8fa5b4 !important; /* 淺藍色背景 */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            position: relative;
        }

        /* 2. 自訂箭頭圖示樣式（白色箭頭） */
        .carousel-control-prev-icon::after,
        .carousel-control-next-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.6em;
            height: 0.6em;
            border: solid white;
            border-width: 0 4px 4px 0;
            display: inline-block;
            padding: 5px;
            transform: translate(-50%, -50%) rotate(135deg);
        }


        /* 4. hover 效果：變深藍 + 加陰影（可選） */
        .carousel-control-prev-icon:hover,
        .carousel-control-next-icon:hover {
            background-color: #6c8596 !important;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        /* 清除左右按鈕背景 */
        .carousel-control-prev,
        .carousel-control-next {
            background: transparent !important;
            border: none !important;
            width: 50px; /* 或你要更小 */
        }

        /* 將按鈕移到圖片之上、取消長條背景 */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: #A5C0DC; /* 你要的藍色 */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            background-size: 50% 50%;
            background-position: center;
            background-repeat: no-repeat;
            box-shadow: none !important;
            opacity: 1;
        }

        /* 移除 Bootstrap 內建的 icon 遮罩線條效果 */
        .carousel-control-prev-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 8 8'%3E%3Cpath d='M5.5 0L4.78.72 1.5 4l3.28 3.28.72-.72L2.94 4l2.56-2.56z'/%3E%3C/svg%3E");
        }

        .carousel-control-next-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 8 8'%3E%3Cpath d='M2.5 0l.72.72L6.5 4l-3.28 3.28-.72-.72L5.06 4 2.5 1.44z'/%3E%3C/svg%3E");
        }


        /* 滑鼠移上去時變色（例如深藍） */
        .btn-primary:hover {
            background-color: #6c8596;
            border-color: #6c8596;
            color: white;
        }

        .btn:hover {
        background-color: #649fd1 !important;
        }

        .heart-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: #ccc; /* 預設灰色 */
            transition: color 0.3s, transform 0.2s;
            padding: 0;
            margin: 0;
        }


        /* 確保 footer 是 flex 排列 */
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

            #search-area {
                display: flex;
                align-items: center;
                gap: 10px;
                position: absolute;
                top: 1.3em;
                right: 2em;
            }

            /*NEW!!! */
            .btn-outline-login {
                color: #6c8596; /* 文字顏色（改成你想要的藍灰） */
                border-color: #6c8596; /* 邊框顏色 */
            }

            .btn-outline-login:hover {
                background-color: #6c8596;
                color: white;
                border-color: #6c8596;
            }
        </style>
</head>
<body class="bg-light">
        <div id="page-wrapper">    
        <!-- 導覽列 + 搜尋欄（替代 nav 和 picture_function） -->
        <section id="header">
            <div class="container">
                <div id="title">
                    <a href="index.php" id="logo-link">
                        <img src="images/title.png" alt="商標" id="logo">
                    </a>
                    <div id="search-area">
<!--NEW!!!-->
								<?php if (isset($_SESSION["user"])): ?>
									<div class="text-end me-3 d-flex align-items-center gap-2">
										<span style="color: #555;">使用者：<?= htmlspecialchars($_SESSION["user"]) ?> ♡</span>
										<a href="logout.php" class="btn btn-outline-secondary btn-sm">登出</a>
									</div>
								<?php else: ?>
									<div class="text-end me-3">
										<a href="SignIn.php" class="btn btn-outline-login btn-sm">登入</a>
									</div>
								<?php endif; ?>

                        <div class="nav-right">
                            <ul>
                                <!--hahaha--><!--收藏-->
								<li></li>
									<li><a href="#"><i class="icon solid fa-heart"></i></a></li>
								<!--hahaha-->
                                
                                <li>
                                            <?php
                                                if (isset($_SESSION['user']) && $_SESSION['user'] === 'admin') {
                                                    echo '<a href="admin.php"><i class="icon solid fa-user"></i></a>';/*管理者後台*/ 
                                                } else if (isset($_SESSION['user'])) {
                                                    echo '<a href="profile.php"><i class="icon solid fa-user"></i></a>';/*會員中心*/ 
                                                } else {
                                                    echo '<a href="SignIn.php"><i class="icon solid fa-user"></i></a>';/*一般登入*/ 
                                                }
                                                ?>
                                </li>                               
                                <li><a href="test_cart_3.php"><i class="icon solid fa-shopping-cart"></i></a></li>
                            </ul>
                        </div>
                        <form id="search-form" action="/search" method="get">
                            <input type="text" name="query" placeholder="搜尋..." />
                            <button type="submit"><i class="icon solid fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <!-- 主導覽列 -->
                <nav id="nav">
                    <ul>
                        <li><a href="index.php"><span>首頁 homepage</span></a></li>
                        <li>
                            <a href="02.php"><span>♡ Ashely painting ♡</span></a>
                            <ul>
                                <li><a href="02.php?filter=type:2">徽章</a></li>
                                <li><a href="02.php?filter=type:1">明信片</a></li>
                                <li><a href="02.php?filter=type:3">吊飾</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="01.php"><span>♡ Anycolor ♡</span></a>
                            <ul>
                                <li><a href="01.php?filter=type:4">項鍊</a></li>
                                <li><a href="01.php?filter=type:5">手鍊</a></li>
                                <li><a href="01.php?filter=type:6">戒指</a></li>
                            </ul>
                        </li>
                        <li><a href="02.php?filter=special:new"><span>新品上市 New!!</span></a></li>
                        <li><a href="02.php?filter=special:hot"><span>熱銷商品 Hot!!</span></a></li>
                    </ul>
                </nav>
            </div>
        </section>
    </div>
<br>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-white text-center"  style="background-color:rgb(77, 102, 123);">
                        <h4 style="color: #ffffff;">會員新增</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST" action="add_member.php"><!--結果這裡是要寫檔名而不是隨便寫個login.php，難怪會錯-->
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="account" class="form-label">帳號</label>
                                    <input type="text" name="account" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="password" class="form-label">密碼</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="birthday" class="form-label">生日</label>
                                    <!--<input type="date" name="birthday" class="form-control" required>-->
                                    <input type="text" id="birthday" name="birthday" class="form-control" required>
                                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                                    <script>
                                        flatpickr("#birthday", {
                                            dateFormat: "m/d/Y"
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center">
                                <div style="width: 20%;">
                                    <button type="submit" class="btn btn-primary w-100" style="background-color:rgb(53, 97, 135); color: #ffffff; border: none;">
                                        新增
                                    </button>
                                </div>
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

