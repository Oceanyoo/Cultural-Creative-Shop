<?php
session_start(); // 一定要在最上方，且只出現一次！
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // 尚未登入，導回登入頁面
    header("Location: SignIn.php");
    exit();
}

// 取得會員資料
$members = mysqli_query($conn, "SELECT * FROM member"); // 表格名稱依你實際資料表名稱為準
// 取得商品資料
$products = mysqli_query($conn, "SELECT * FROM all_products"); // 表格名稱依你實際資料表名稱為準
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理者後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        /*我要改修改、刪除跟新增的btn顏色*/
        a.btn-success {
            background-color:#25688B !important;
        }

        a.btn-warning {
            background-color: #F7DC6F !important;
        }

        a.btn-danger {
            background-color: #E74C3C !important;
        }


        /*因為description太多字了 所以後面讓他用...代替*/ 
        .truncate {
            max-width: 200px;      /* 控制最大寬度，可依需求調整 */
            white-space: nowrap;   /* 不換行 */
            overflow: hidden;      /* 超出隱藏 */
            text-overflow: ellipsis; /* 超出以 ... 表示 */
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
    <h2 class="mb-4">會員管理</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>帳號</th>
                <th>密碼</th>
                <th>生日</th>
                <th>Email</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($members)): ?><!--這個member是指變數，不是資料表名稱-->
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['Account'] ?></td>
                    <td><?= $row['Password'] ?></td>
                    <td><?= $row['Birthday'] ?></td>
                    <td><?= $row['E-mail'] ?></td>
                    <td>
                        <a href="edit_member.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">修改</a>
                        <a href="delete_member.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="add_member.php" class="btn btn-success">新增會員</a>

    <hr class="my-5">

    <h2 class="mb-4">商品管理</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>價格</th>
                <th>商品介紹</th>
                <th>類型</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['price'] ?></td>
                    <td class="truncate"><?= $row['description'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">修改</a>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="add_product.php" class="btn btn-success">新增商品</a>

    <hr class="my-5">

<h2 class="mb-4">商品評論管理</h2>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>會員</th>
            <th>商品名稱</th>
            <th>內容</th>
            <th>評分</th>
            <th>留言時間</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php

            // 加入 JOIN 查詢取得商品名稱與會員帳號
            $reviews = mysqli_query($conn, "
                SELECT reviews.*, all_products.name AS product_name, member.Account AS member_account
                FROM reviews
                JOIN all_products ON reviews.product_id = all_products.id
                JOIN member ON reviews.member_id = member.id
                ORDER BY reviews.created_at DESC
            ");

        while($row = mysqli_fetch_assoc($reviews)):
        ?>
            <tr>
                <td><?= htmlspecialchars($row['member_account']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td class="truncate"><?= htmlspecialchars($row['review_text']) ?></td>
                <td><?= $row['rating'] ?> / 5</td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="edit_review.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">修改</a>
                    <a href="delete_review.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除此評論嗎？')">刪除</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<a href="add_review.php" class="btn btn-success">新增評論</a>


</div>

</body>
</html>
