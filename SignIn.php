<?php
session_start();// 開啟 session：可以儲存登入狀態，使用者登入後的資訊（例如帳號名稱）

// 處理表單提交(當按下"登入"按鈕)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'connection.php'; // 連接資料庫

    //取得使用者在登入表單輸入的帳號和密碼。
    $username = $_POST["username"];//body裡面的名稱
    $password = $_POST["password"];

    // 防止 SQL injection (能防止惡意的 SQL 語句注入，保護網站)
    $stmt = $conn->prepare("SELECT * FROM member WHERE Account = ? LIMIT 1");//限制只會查到一筆資料

    //test錯誤
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("s", $username);//s是字串，$username是要帶入SQL裡查詢的參數
    $stmt->execute();
    $result = $stmt->get_result();//取得查詢結果

    if ($result->num_rows === 1) {//代表此會員有註冊
        $user = $result->fetch_assoc();

        // 假設密碼是加密的，password_verify 是用來比對加密密碼
        if ($password === $user['Password']) {

            //登入成功，儲存有登入過的狀態!!!很重要!!!!!
            $_SESSION['logged_in'] = true;
            $_SESSION["user"] = $user["Account"];
            $_SESSION["member_id"]=$user['id'];

                if (headers_sent($file, $line)) {
                    die("Headers already sent in $file on line $line");
                }
            

            // 若帳號是 admin，導向 admin.php
            if ($user["Account"] === "admin") {
                header("Location: admin.php");//進入到管理者頁面
            } else {
                header("Location: index.php");//登入成功!!(登入成功後自動跳到首頁)
            }

            exit();

        } else {
            $error = "密碼錯誤";
        }
    } else {
        $error = "找不到使用者";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>會員登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品頁面</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                    <!--<div id="search-area">

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
                                <li></li>
                                <li><a href="SignIn.php"><i class="icon solid fa-user"></i></a></li>
                                <li><a href="test_cart_3.php"><i class="icon solid fa-shopping-cart"></i></a></li>
                            </ul>
                        </div>
                        <form id="search-form" action="/search" method="get">
                            <input type="text" name="query" placeholder="搜尋..." />
                            <button type="submit"><i class="icon solid fa-search"></i></button>
                        </form>
                    </div>-->
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

<!--NEW END!!!-->
								<div class="nav-right"><!--購物車、搜詢欄、會員登入-->
								
								<ul>

								<!--hahaha--><!--收藏-->
								<li></li>
									<li><a href="favorite.php"><i class="icon solid fa-heart"></i></a></li>
								

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
										
										<!--<li><a href="#"><i class="icon solid fa-search"></i></a></li>-->
									</ul>
								</div>
                                <div id="search-form-wrapper" style="position: relative;">
									<form id="search-form" onsubmit="return false;">
										<input type="text" name="query" id="search-input" placeholder="搜尋..." autocomplete="off" />
										<button type="submit"><i class="icon solid fa-search"></i></button>
									</form>
									<div id="search-results" style="
										position: absolute;
										top: 100%;
										left: 0;
										width: 100%;
										background: white;
										border: 1px solid #ccc;
										z-index: 999;
										display: none;
										max-height: 300px;
										overflow-y: auto;
									">
									</div>
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
                        <h4 style="color: #ffffff;">會員登入</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST" action="SignIn.php"><!--結果這裡是要寫檔名而不是隨便寫個login.php，難怪會錯-->
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="username" class="form-label">帳號</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <div style="width: 70%;">
                                    <label for="password" class="form-label">密碼</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div style="width: 20%;">
                                    <button type="submit" class="btn btn-primary w-100" style="background-color:rgb(53, 97, 135); color: #ffffff; border: none;">
                                        登入
                                    </button>
                                </div>
                            </div>
                        </form>
                        <hr>
                        <p class="text-center">還沒有帳號？<a href="register.php">註冊新會員</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
