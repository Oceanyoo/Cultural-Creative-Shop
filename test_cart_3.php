<?php
session_start();
include 'connection.php';

// 檢查使用者是否已登入並有 member_id
if (!isset($_SESSION['member_id'])) {
    // 如果沒有，導向登入頁或顯示錯誤
    header('Location: SignIn.php'); // 或者直接 die("請先登入");
    exit;
}

$member_id = $_SESSION['member_id'];

/*$sql = "SELECT p.name, p.price, c.amount, p.id AS product_id
        FROM shopping_cart c
        JOIN all_products p ON c.product_id = p.id
        WHERE c.member_id = ?";*/

// 查詢購物車內容，包含商品名稱、價格、數量、商品ID、table_name
// 並額外加入對 all_products_img 表的 JOIN，以取得主圖片路徑
$sql = "SELECT 
            p.name, 
            p.price, 
            c.amount, 
            c.product_id, 
            c.table_name, 
            api.img_path, 
            c.product_id AS cart_item_id,
            c.choose
        FROM 
            shopping_cart c
        JOIN 
            all_products p ON c.product_id = p.id
        LEFT JOIN -- 使用 LEFT JOIN 以防有些商品還沒有圖片，不會導致購物車商品不顯示
            all_products_img api ON c.product_id = api.product_id AND api.is_main = 1
        WHERE 
            c.member_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare SQL 失敗: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param("i", $member_id);
$stmt->execute();

$result = $stmt->get_result();

/*while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}*/

$cart_items = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>串珠商品頁面</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>


        /* 商品標題改成灰色 */
        .card-title {
            color: #555555;
            font-weight: bold;
        }

        /* 按鈕底色改為鮮藍色 */
        .btn-primary {
            background-color: #A5C0DC;
            border-color: #A5C0DC;
            color: white;
        }

        /* 滑鼠移上去時變色（例如深藍） */
        .btn-primary:hover {
            background-color: #6c8596;
            border-color: #6c8596;
            color: white;
        }

        .heart-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: #ccc;
            transition: color 0.3s, transform 0.2s;
            padding: 0;
            margin: 0;
        }

        .heart-icon {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s, transform 0.2s;
        }

        .heart-icon:hover {
            transform: scale(1.2);
        }

        .heart-icon.favorited {
            color: red;
        }

        /* 深藍色按鈕樣式 (新增) */
        .btn-dark-blue {
            background-color: #003366; /* 深藍色 */
            border-color: #003366;
            color: white;
        }
        .btn-dark-blue:hover {
            background-color: #002244; /* 滑鼠懸停時更深的藍色 */
            border-color: #002244;
            color: white;
        }

        /* 商品圖片 hover 效果 */
        /* Note: card-img-top might not be the right class if image is not at top of a vertical card */
        .card-img-top {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .card-img-top:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            opacity: 0.9;
        }

        .card-title a {
            color: #555555;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
        }

        .card-title a:hover {
            color:rgb(110, 151, 194);
            transform: scale(1.2);
            text-decoration: underline;
        }

        /* 確保 footer 是 flex 排列 */
        /* Note: This .card-footer class might conflict with the new card structure */
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 576px) {
            .card-footer {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
            .card-footer .btn-primary {
                width: 100%;
            }
            .card-footer .heart-icon {
                align-self: flex-start;
            }
        }

        #product-wrapper {
            position: relative;
        }

        @media (min-width: 769px) {
            #product-wrapper > form.mb-4.text-center {
                position: absolute;
                top: 0;
                right: 40px;
                width: 180px;
                background: #f8f9fa;
                padding: 8px 12px;
                border-radius: 5px;
                box-shadow: 0 0 8px rgba(0,0,0,0.1);
                text-align: right;
                font-size: 0.9rem;
                z-index: 10;
                background-image: url('images/filter.jpg');
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
            }
        }

        @media (max-width: 768px) {
            #product-wrapper > form.mb-4.text-center {
                position: relative !important;
                width: 100% !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
                text-align: center !important;
                margin-bottom: 1rem;
                z-index: auto;
            }

            #product-wrapper > form.mb-4.text-center label,
            #product-wrapper > form.mb-4.text-center select {
                font-size: 1rem;
            }

            #product-wrapper > form.mb-4.text-center select {
                width: 100%;
            }

    }

        #product-wrapper > form.mb-4.text-center select {
            background-color: white !important;
            border: 1px solid #ccc;
            padding-right: 30px;
            border-radius: 4px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            position: relative;
            cursor: pointer;
        }

        #product-wrapper > form.mb-4.text-center select::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 10px;
            width: 20px;
            height: 20px;
            background: #ddd;
            border: 1px solid #bbb;
            border-radius: 4px;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 2;
            box-sizing: border-box;
        }

        #product-wrapper > form.mb-4.text-center select {
            background-image:
            url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%23666' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 10px 6px;
        }

        #product-wrapper > form.mb-4.text-center select::after {
            content: none;
        }

        #search {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #search form#search-form {
            flex-grow: 1;
            display: flex;
        }

        #search-form input[type="text"] {
            flex-grow: 1;
            padding: 6px 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        #search-form button {
            padding: 6px 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-left: none;
            border-radius: 0 4px 4px 0;
            background-color: #A5C0DC;
            color: white;
            cursor: pointer;
        }

        .nav-right ul {
            display: flex;
            gap: 15px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-right ul li a {
            font-size: 1.5rem;
            color: #555;
        }

        @media (max-width: 768px) {
            #search {
                flex-direction: column;
                align-items: stretch;
                max-width: 100%;
                gap: 8px;
                padding: 0 10px;
            }

            #search-form {
                flex-direction: row;
            }

            #search-form input[type="text"] {
                font-size: 0.9rem;
                padding: 6px 8px;
            }

            #search-form button {
                padding: 6px 10px;
                font-size: 0.9rem;
            }

            .nav-right ul {
                justify-content: flex-end;
                gap: 10px;
            }

            .nav-right ul li a {
                font-size: 1.3rem;
            }
        }

        /* 收藏成功的 Toast 設定 */
        .favorite-toast {
            background-color:rgb(187, 209, 233);
            color:rgb(88, 88, 88);
        }
        .favorite-toast .toast-body {
            font-weight: bold;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #search-results {
            max-height: 300px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .toast-cart-custom {
            background-color: #bbd1e9;
            color: #333333;
        }

        #search-area {
            display: flex;
            align-items: center;
            gap: 10px;
            position: absolute;
            top: 1.3em;
            right: 2em;
        }

        .btn-outline-login {
            color: #6c8596;
            border-color: #6c8596;
        }

        .btn-outline-login:hover {
            background-color: #6c8596;
            color: white;
            border-color: #6c8596;
        }

        .heart-icon.active {
            color: red;
        }

        /* --- 針對購物車商品列表置中和間距的新增/修改樣式 --- */

        /* 讓每個商品卡片本身置中於其容器，並增加垂直間距 */
        .shopping-cart-item {
            display: flex;
            justify-content: center; /* 水平置中整個 item */
            margin-bottom: 30px; /* 增加卡片之間的垂直間距 */
            width: 100%; /* 確保它佔滿整個行以便置中 */
        }

        /* 調整卡片本身，讓其內容水平排列並垂直置中，並限制寬度 */
        .shopping-cart-item .card {
            width: 100%; /* 確保卡片可以伸縮 */
            max-width: 900px; /* 增加最大寬度，以便容納更多元素 */
            flex-direction: row; /* 保持所有元素在同一行 */
            align-items: center; /* 垂直置中卡片內的所有內容 */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* 增加一點陰影讓卡片更突出 */
            border-radius: 8px; /* 圓角 */
        }

        /* 圖片樣式調整 */
        .product-image {
            width: 120px; /* 固定圖片寬度 */
            height: 120px; /* 固定圖片高度 */
            object-fit: cover; /* 保持圖片比例並裁切 */
            border-radius: 6px; /* 圖片圓角 */
        }

        /* 商品資訊 (名稱, 價格) 區塊 - 佔用剩餘空間，並靠左對齊 */
        .product-info {
            flex-grow: 1; /* 讓名稱價格區塊佔用可用空間 */
            text-align: left; /* 名稱價格靠左對齊 */
        }

        /* 數量控制的輸入框樣式微調 */
        .quantity-control input {
            width: 50px; /* 確保輸入框有固定寬度 *//*50px*/ 
            text-align: center;
        }

        /* 新增的「選擇」按鈕樣式 */
        .btn-info {
            background-color: #5bc0de; /* Bootstrap 預設的 info 藍 */
            border-color: #5bc0de;
            color: white;
        }

        .btn-info:hover {
            background-color: #31b0d5;
            border-color: #31b0d5;
        }

        /* RWD 調整：小螢幕時讓商品內容堆疊 */
        @media (max-width: 768px) {
            .shopping-cart-item .card {
                flex-direction: column; /* 小螢幕時堆疊 */
                align-items: center; /* 堆疊後內容置中 */
                padding: 15px; /* 調整小螢幕的內邊距 */
            }
            .product-image {
                width: 500px;
                height: 500px;

                margin-bottom: 15px; /* 堆疊後圖片下方增加間距 */
                margin-right: 0 !important; /* 移除右邊距 */
            }
            .product-info {
                text-align: center; /* 堆疊後名稱價格置中 */
                margin-bottom: 10px; /* 增加下方間距 */
                margin-left: 0 !important; /* 移除左邊距 */
                flex-grow: 0 !important; /* 不再佔據所有可用空間 */
            }
            .quantity-control,
            .action-buttons,
            .heart-icon-container {
                margin-top: 10px !important;
                margin-bottom: 10px !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100%; /* 讓元素在小螢幕上可以佔滿寬度 */
                justify-content: center; /* 內部元素置中 */
            }
            /* 讓按鈕在小螢幕上可以並排顯示 */
            .action-buttons {
                display: flex;
                gap: 10px;
            }


        }

        /* 更小的手機螢幕調整 (例如 <576px) */
        @media (max-width: 575.98px) {
            .shopping-cart-item .card {
                padding: 10px;
            }
            .product-image {
                width: 300px;
                height: 300px;
            }
            .card-title {
                font-size: 1.1rem;
            }
            .card-text {
                font-size: 0.9rem;
            }
            .btn, .form-control-sm {
                font-size: 0.8rem;
                padding: 6px 10px;
            }
            .quantity-control input {
                width: 40px !important;
            }
        }

         @media (max-width: 375px) {
            .shopping-cart-item .card {
                padding: 10px;
            }
            .product-image {
                width: 100px;
                height: 100px;
            }
        }

        @media (max-width: 320px) {
            .shopping-cart-item .card {
                padding: 10px;
            }
            .product-image {
                width: 250px;
                height: 250px;
                margin: 15px;
            }
        }
    </style>
</head>
<body>
    <div id="page-wrapper">
        <!-- 導覽列 + 搜尋欄（替代 nav 和 picture_function） -->
        <section id="header">
            <div class="container">
                <div id="title">
                    <a href="index.php" id="logo-link">
                        <img src="images/title.png" alt="商標" id="logo">
                    </a>
                    <div id="search-area">
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
                                <li><a href="favorite.php"><i class="icon solid fa-heart"></i></a></li>

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
                        <div id="search-container" style="position: relative;">
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

    <div class="container py-5">
        <!-- 新增的總金額與送出訂單按鈕框框 -->
            <!--<div class="border rounded p-4 my-4 bg-light text-center mx-auto" style="max-width: 400px;">
                <h3 class="mb-3">總金額：$<span id="totalPriceDisplay">0.00 元</span></h3>
                <button class="btn btn-dark-blue btn-lg">送出訂單</button>
            </div>-->
        <div class="border rounded p-4 my-4 bg-light text-center mx-auto" style="max-width: 400px;">
            <div class="col-12 text-center">
                <h3 class="mb-3">總金額：<span id="totalPrice">0.00</span> 元</h4>
                <button class="btn btn-dark-blue btn-lg" onclick="submitOrder()">送出訂單</button>
            </div>
        </div>
        <!-- 結束新增區塊 -->

        <div class="justify-content-center" style="margin: 100px;"></div><!--空出距離讓版面好看-->

        <div class="row justify-content-center">
            <?php

                if (!empty($cart_items)) {
                        foreach ($cart_items as $item) {

                            $select_button_class = ($item['choose'] == 1) ? 'btn-success' : 'btn-info';
                            ?>
                    <!--改-->
                    <div class="col-12 shopping-cart-item" data-product-id="<?= htmlspecialchars($item['product_id']) ?>">
                        <div class="card d-flex align-items-center p-3"> <!-- p-3 提供了整體內邊距，使內容不緊貼邊緣 -->
                            <!-- 1. 照片 -->
                            <a href="product_detail.php?id=<?= htmlspecialchars($item['id']) ?>" class="me-3"> <!-- me-3: margin-right -->
                                <img src="<?= htmlspecialchars($item['img_path']) ?>" class="product-image" alt="<?= htmlspecialchars($item['name']) ?>">
                            </a>

                            <!-- 2. 名稱 & 3. 價格 -->
                            <div class="product-info flex-grow-1 me-3"> <!-- flex-grow-1: 佔用剩餘空間，me-3: margin-right -->
                                <h5 class="card-title mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text mb-0">價格：$<?= htmlspecialchars($item['price']) ?></p>
                            </div>

                            <!-- 4. 數量調整器 -->
                            <div class="quantity-control d-flex align-items-center me-3"> <!-- me-3: margin-right -->
                                <button class="btn btn-sm btn-secondary" onclick="updateQuantity(this, 'minus',<?= $item['product_id'] ?>)">-</button>
                                <input type="text" class="form-control form-control-sm text-center mx-1 quantity-input" type="number" value="<?= $item['amount'] ?>" min="1" readonly>
                                <button class="btn btn-sm btn-secondary" onclick="updateQuantity(this, 'add',<?= $item['product_id'] ?>)">+</button>
                            </div>

                            <!-- 5. "選擇" 按鈕 -->
                            <!--<button class="btn btn-info me-3" onclick="toggleSelect(this, <?= $item['product_id'] ?>, <?= $item['amount'] ?>)"
                            id="select-btn-<?= htmlspecialchars($item['product_id']) ?>"
                            >選取</button>-->
                            <button class="btn select-btn <?= $select_button_class ?> me-3"
                                    onclick="toggleSelect(this, <?= $item['product_id'] ?>)"
                                    data-product-id="<?= htmlspecialchars($item['product_id']) ?>">
                                <?= ($item['choose'] == 1) ? '已選取' : '選取' ?>
                            </button>

                            <!-- 6. "移除" 按鈕 -->
                            <?php if (isset($_SESSION["user"])): ?>
                                <button class="btn btn-primary" onclick="removeFromCart(this)"
                                data-product-id="<?= htmlspecialchars($item['product_id']) ?>"
                                data-table-name="<?= htmlspecialchars($item['table_name']) ?>"

                                >移除</button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="redirectToLogin()">移除</button>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php 
            }//foreach結束
            }else
            {echo "<p class='text-center'>目前購物車中沒有商品。</p>";}
                
            ?>
        </div>
    </div>

    <!-- 收藏成功視窗 -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div id="favoriteToast" class="toast align-items-center favorite-toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    收藏成功！
                </div>
                <button type="button" class="btn-close btn-close-dark me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <!-- 加入購物車提示 Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div id="cartToast" class="toast toast-cart-custom align-items-center border-0">
            <div class="d-flex">
                <div class="toast-body" id="cartToastBody">
                    商品已從購物車中移除!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- 選擇/取消選取提示 Toast (新增) -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1056">
      <div id="selectToast" class="toast align-items-center border-0 toast-cart-custom">
        <div class="d-flex">
          <div class="toast-body">
            <!-- 訊息將由 JavaScript 動態設定 -->
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <section id="footer">
        <div class="container">
            <header>
                <h2>Questions or comments? <strong>Get in touch:</strong></h2>
            </header>
            <div class="row">
                <div class="col-6 col-12-medium">
                    <section>
                        <form method="post" action="#">
                            <div class="row gtr-50">
                                <div class="col-6 col-12-small">
                                    <input name="name" placeholder="Name" type="text" />
                                </div>
                                <br>
                                <div class="col-6 col-12-small">
                                    <input name="email" placeholder="Email" type="text" />
                                </div>
                                <br>
                                <div class="col-12">
                                    <textarea name="message" placeholder="Message"></textarea>
                                </div>
                                <div class="col-12">
                                    <a href="#" class="form-button-submit button icon solid fa-envelope">Send Message</a>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
                <div class="col-6 col-12-medium">
                    <section>
                        <div class="row">
                            <div class="col-6 col-12-small">
                                <ul class="icons">
                                    <li class="icon solid fa-home">
                                        彰化縣彰化市彰化路彰化巷100號100樓<br />
                                    </li>
                                    <li class="icon solid fa-phone">
                                        (+886) 1234 5678
                                    </li>
                                    <li class="icon solid fa-envelope">
                                        <a href="#">S1250000@gm.ncue.edu.tw</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-6 col-12-small">
                                <ul class="icons">
                                    <li class="icon brands fa-instagram">
                                        <a href="">Anicolor</a>
                                    </li>
                                    <li class="icon brands fa-instagram">
                                        <a href="https://www.instagram.com/ashelypainting?igsh=MWFwY2R4bWx3NWHYcQ==">AshelyPainting</a>
                                    </li>
                                    <li class="icon brands fa-facebook-f">
                                        <a href="https://www.facebook.com/share/16EWUCyrp6/">Ashely Painting</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <div id="copyright" class="container">
            <ul class="links">
                <li>&copy; 版權所有翻印必究.</li>
            </ul>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 判斷是否登入（由 PHP 產生變數）
        const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
    </script>

    <script>
        const favoriteToastElement = document.getElementById('favoriteToast');
        const favoriteToast = new bootstrap.Toast(favoriteToastElement, {
            autohide: true,
            delay: 3000
        });

        function toggleFavorite(el) {
            if (!isLoggedIn) {
                window.location.href = 'SignIn.php';
                return;
            }

            const isFavorited = el.classList.toggle('favorited');
            const toastBody = document.querySelector('#favoriteToast .toast-body');

            if (isFavorited) {
                toastBody.textContent = '收藏成功！';
            } else {
                toastBody.textContent = '已取消收藏';
            }
            favoriteToast.show();
        }

        function handleAddToCart(button) {
            if (!isLoggedIn) {
                window.location.href = 'SignIn.php';
                return;
            }
            const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));
            cartToast.show();
        }

function removeFromCart(button) {
    // 檢查使用者是否已登入
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php'; // 如果未登入則導向登入頁
        return;
    }

    const productId = button.dataset.productId;
    const tableName = button.dataset.tableName;

    // 獲取 Toast 元素和其 body 元素 (已修正問題一)
    const cartToastElement = document.getElementById('cartToast');
    const cartToastBody = cartToastElement.querySelector('.toast-body'); // 確保正確選取 toast-body
    const cartToast = new bootstrap.Toast(cartToastElement);

    // 發送 AJAX 請求到 toggle_cart.php
    fetch('toggle_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}&table_name=${tableName}` // 傳遞必要參數
    })
    .then(response => {
        // 檢查 HTTP 響應狀態碼
        if (!response.ok) {
            // 如果 HTTP 狀態碼不是 2xx (成功)，則拋出錯誤
            return response.json().then(errorData => {
                throw new Error(errorData.message || `後端回傳非成功狀態碼: ${response.status}`);
            }).catch(() => {
                throw new Error(`HTTP 錯誤! 狀態碼: ${response.status}`);
            });
        }
        return response.json(); // 解析 JSON 響應
    })
    .then(data => {
        console.log('Remove response:', data); // 在瀏覽器控制台輸出後端回傳資料，方便除錯

        // 根據後端回傳的 status 和 action_performed 處理
        if (data.status === 'success' && data.action_performed === 'removed') {
            // 成功移除：從前端頁面移除該商品顯示 (已修正問題二)
            const itemToRemove = button.closest('.shopping-cart-item');
            if (itemToRemove) {
                itemToRemove.remove(); // 移除整個商品卡片
            }
            //recalcTotalPrice(); // 重新計算總金額
            fetchAllCartDataAndRecalc();

            // 更新 Toast 訊息為成功狀態
            cartToastBody.textContent = data.message;
            cartToastElement.classList.remove('bg-danger', 'bg-info'); // 移除可能的錯誤樣式
            cartToastElement.classList.add('bg-success', 'text-white'); // 設置成功樣式

        } else if (data.status === 'info') {
            // 提示訊息：商品不在購物車中
            cartToastBody.textContent = data.message;
            cartToastElement.classList.remove('bg-success', 'bg-danger');
            cartToastElement.classList.add('bg-info', 'text-white');

        } else {
            // 其他錯誤或異常情況
            cartToastBody.textContent = '移除失敗: ' + (data.message || '未知錯誤');
            cartToastElement.classList.remove('bg-success', 'bg-info');
            cartToastElement.classList.add('bg-danger', 'text-white'); // 設置錯誤樣式
        }
        cartToast.show(); // 顯示 Toast 提示
    })
    .catch(error => {
        // 捕獲網路錯誤或 JSON 解析錯誤
        console.error('Fetch Error:', error);
        cartToastBody.textContent = '移除時發生網路或處理錯誤: ' + error.message;
        cartToastElement.classList.remove('bg-success', 'bg-info');
        cartToastElement.classList.add('bg-danger', 'text-white'); // 設置錯誤樣式
        cartToast.show(); // 顯示錯誤 Toast
    });
}


        // --- 新增的數量調整函數 ---
        /*function updateQuantity(button, type,  productId) {
            const quantityControl = button.closest('.quantity-control');
            const quantityInput = quantityControl.querySelector('.quantity-input');
            let currentQuantity = parseInt(quantityInput.value);

            if (type === 'add') {
                currentQuantity++;
            } else if (type === 'minus' && currentQuantity > 1) {
                currentQuantity--;
            }
            quantityInput.value = currentQuantity;

            // Ajax 更新資料庫
            fetch('update_cart_amount.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({product_id: productId, amount: currentQuantity})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 如果該商品有被選取，更新總價
                    if (document.getElementById(`select-btn-${productId}`).classList.contains('btn-success')) {
                        recalcTotalPrice();
                    }
                } else {
                    alert('更新數量失敗');
                }
            });
            
        }*/

        function updateQuantity(button, type, productId) {
            if (!isLoggedIn) {
                window.location.href = 'SignIn.php';
                return;
            }

            const quantityControl = button.closest('.quantity-control'); // 使用 closest 找到父級容器
            const quantityInput = quantityControl.querySelector('.quantity-input'); // 在容器內找到 input
            let currentQuantity = parseInt(quantityInput.value);

            if (type === 'add') {
                currentQuantity++;
            } else if (type === 'minus' && currentQuantity > 1) {
                currentQuantity--;
            }
            quantityInput.value = currentQuantity; // 立即更新前端顯示

            fetch('update_cart_amount.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({product_id: productId, amount: currentQuantity})
            })
            .then(res => {
                if (!res.ok) {
                    // ... 錯誤處理 ...
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // 數量更新成功，重新獲取所有購物車數據並更新總價
                    fetchAllCartDataAndRecalc(); // <--- 關鍵點：調用新的數據獲取函數
                } else {
                    alert('更新數量失敗: ' + data.msg);
                }
            })
            .catch(error => {
                // ... 錯誤處理 ...
            });
        }

        // priceMap 將在每次從後端獲取數據時更新
        let priceMap = {}; // 確保在全局範圍內可訪問

        // 核心函數：從後端獲取所有購物車數據並重新計算總價
        /*function fetchAllCartDataAndRecalc() {
            if (!isLoggedIn) {
                document.getElementById('totalPrice').textContent = '$0.00';
                return;
            }
            fetch('get_all_cart_data.php') // 從後端獲取數據
                .then(response => {
                    if (!response.ok) {
                        // ... 錯誤處理 ...
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success' && data.cart_data) {
                        let totalPrice = 0;
                        priceMap = {}; // 清空並更新 priceMap
                        data.cart_data.forEach(item => {
                            priceMap[item.product_id] = parseFloat(item.price); // 確保是數字

                            // 同時更新前端顯示的數量和選取按鈕狀態
                            //const itemElement = document.querySelector(`.shopping-cart-item .card .quantity-control[data-product-id="${item.product_id}"]`);
                            const itemContainer = document.querySelector(`.shopping-cart-item[data-product-id="${item.product_id}"]`);
                            if (itemContainer) {
                                itemElement.querySelector('.quantity-input').value = item.amount;
                                const selectButton = itemContainer.closest('.card').querySelector('.select-btn');
                                if (selectButton) {
                                    if (item.choose === 1) { // 根據後端數據更新按鈕樣式和文字
                                        selectButton.classList.add('btn-success');
                                        selectButton.classList.remove('btn-info');
                                        selectButton.textContent = '已選取';
                                        totalPrice += parseFloat(item.price) * parseInt(item.amount); // 只有被選取的才計算
                                    } else {
                                        selectButton.classList.remove('btn-success');
                                        selectButton.classList.add('btn-info');
                                        selectButton.textContent = '選取';
                                    }
                                }
                            }
                        });
                        document.getElementById('totalPrice').textContent = `$${totalPrice.toFixed(2)}`;

                    } else {
                        console.error('Failed to get cart data:', data.message);
                        document.getElementById('totalPrice').textContent = '$5';
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart data:', error);
                    document.getElementById('totalPrice').textContent = '$6';
                });
        }*/
       // 核心函數：從後端獲取所有購物車數據並重新計算總價
function fetchAllCartDataAndRecalc() {
    if (!isLoggedIn) {
        document.getElementById('totalPrice').textContent = '$0.00';
        return;
    }
    fetch('get_all_cart_data.php') // 從後端獲取數據
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.cart_data) {
                let totalPrice = 0;
                priceMap = {}; // 清空並更新 priceMap

                data.cart_data.forEach(item => {
                    priceMap[item.product_id] = parseFloat(item.price); // 確保是數字

                    // 找到對應的商品容器
                    const itemContainer = document.querySelector(`.shopping-cart-item[data-product-id="${item.product_id}"]`);

                    if (itemContainer) { // 如果找到了這個商品容器
                        const quantityInput = itemContainer.querySelector('.quantity-input');
                        const selectButton = itemContainer.querySelector('.select-btn');

                        if (quantityInput) { // 確保數量輸入框存在
                            quantityInput.value = item.amount;
                        }

                        if (selectButton) { // 確保選取按鈕存在
                            if (item.choose === 1) { // 根據後端數據，更新按鈕樣式和文字
                                selectButton.classList.add('btn-success');
                                selectButton.classList.remove('btn-info');
                                selectButton.textContent = '已選取';
                                // 只有當商品被選取時才計算到總金額
                                totalPrice += parseFloat(item.price) * parseInt(item.amount);
                            } else {
                                selectButton.classList.remove('btn-success');
                                selectButton.classList.add('btn-info');
                                selectButton.textContent = '選取';
                            }
                        }
                    }
                });
                document.getElementById('totalPrice').textContent = `$${totalPrice.toFixed(2)}`;

            } else {
                console.error('Failed to get cart data:', data.message);
                document.getElementById('totalPrice').textContent = '$5'; // 數據獲取失敗
            }
        })
        .catch(error => {
            console.error('Error fetching cart data:', error);
            document.getElementById('totalPrice').textContent = '$6'; // Fetch 或 Promise 鏈中發生錯誤
        });
}

        /*function recalcTotalPrice() {
            totalPrice = 0;
            document.querySelectorAll('.select-btn.btn-success').forEach(btn => {
                const cartId = btn.dataset.cartId;
                const productId = btn.dataset.productId;
                const quantityInput = document.querySelector(`#quantity-input-${cartId}`);
                const amount = parseInt(quantityInput.value);
                const price = priceMap[productId] || 0;
                totalPrice += price * amount;
            });
            document.getElementById('totalPriceDisplay').textContent = totalPrice.toFixed(2);
        }*/



        // --- 結束新增數量調整函數 ---

        // --- 新增的選擇按鈕功能 ---


        /*function toggleSelect(button, productId, amount) {
            const isSelected = button.classList.toggle('btn-success');
            button.classList.toggle('btn-info', !isSelected);

            // 取得該商品單價 (假設你有一個商品價格物件 priceMap)
            const price = priceMap[productId] || 0;

            if (isSelected) {
                totalPrice += price * amount;
            } else {
                totalPrice -= price * amount;
            }

            // 更新畫面顯示總金額（假設有一個元素 #totalPriceDisplay）
            document.getElementById('totalPriceDisplay').textContent = totalPrice.toFixed(2);

            // 顯示toast提示
            showSelectToast(isSelected);
        }*/

////////////////////////////////////////////////////////////////////////////////////////
function toggleSelect(button, productId) { // 移除 currentAmount 參數，因為不再從前端讀取
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    const wasSelected = button.classList.contains('btn-success');
    console.log('Current button state (wasSelected):', wasSelected); // <-- 新增這行

    const newChooseStatus = wasSelected ? 0 : 1; // 點擊後要變成什麼狀態

    // 顯示 Toast 提示 - 處理中
    const selectToastElement = document.getElementById('selectToast');
    const selectToastBody = selectToastElement.querySelector('.toast-body');
    selectToastBody.textContent = '更新中...';
    selectToastElement.classList.remove('bg-success', 'bg-danger', 'text-white');
    selectToastElement.classList.add('toast-cart-custom', 'bg-secondary');
    const selectToast = new bootstrap.Toast(selectToastElement, { autohide: false });
    selectToast.show();

    /*fetch('update_cart_choose.php', { // 向新的 PHP API 發送請求
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=<span class="math-inline">\{productId\}&choose\=</span>{newChooseStatus}`
    })*/

    console.log(`Sending to update_cart_choose.php: product_id=${productId}, choose=${newChooseStatus}`);

   // 改
    fetch('update_cart_choose.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&choose=${newChooseStatus}` // <-- 修正後的程式碼
    })
    .then(response => {
        if (!response.ok) {
            // ... 錯誤處理 ...
        }
        return response.json();
    })
    .then(data => {
        console.log('Update choose response:', data);

        if (data.status === 'success') {
            // 更新按鈕樣式和文字
            button.classList.toggle('btn-success', newChooseStatus === 1);
            button.classList.toggle('btn-info', newChooseStatus === 0);
            button.textContent = newChooseStatus === 1 ? '已選取' : '選取';

            // 成功更新後，重新獲取所有購物車數據並計算總金額
            fetchAllCartDataAndRecalc(); // <--- 關鍵點：調用新的數據獲取函數

            // 更新 Toast 訊息為成功狀態
            selectToastBody.textContent = newChooseStatus === 1 ? '商品已選取' : '商品已取消選取';
            selectToastElement.classList.remove('bg-secondary');
            selectToastElement.classList.add('bg-success', 'text-white');
        } else {
            // ... 失敗處理 ...
        }
        selectToast.show();
        setTimeout(() => selectToast.hide(), 3000);
    })
    .catch(error => {
        // ... 錯誤處理 ...
    });
}

////////////////////////////////////////////////////////////////////////////////////////

        function showSelectToast(isSelected) {
            const selectToastElement = document.getElementById('selectToast');
            const selectToastBody = selectToastElement.querySelector('.toast-body');

            selectToastElement.classList.remove('bg-success', 'bg-danger', 'text-white');
            selectToastElement.classList.add('toast-cart-custom');

            selectToastBody.textContent = isSelected ? '已選取' : '已取消選取';

            const selectToast = new bootstrap.Toast(selectToastElement, {
                autohide: true,
                delay: 3000
            });
            selectToast.show();
        }
        // --- 結束新增選擇按鈕功能 ---
    </script>

    <script>
        // 這個函數現在變得多餘，因為它的功能已經合併到 handleAddToCart 中
        // 但為了兼容性，先保留，實際上可以考慮移除
        const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));
        function showAddToCartToast() {
            cartToast.show();
        }


    /*提交訂單的程式碼*/ 
    function submitOrder() {
        if (!isLoggedIn) {
            window.location.href = 'SignIn.php'; // If not logged in, redirect
            return;
        }

        // Show a "processing" toast/message
        const selectToastElement = document.getElementById('selectToast');
        const selectToastBody = selectToastElement.querySelector('.toast-body');
        selectToastBody.textContent = '訂單處理中...';
        selectToastElement.classList.remove('bg-success', 'bg-danger', 'text-white');
        selectToastElement.classList.add('toast-cart-custom', 'bg-secondary');
        const selectToast = new bootstrap.Toast(selectToastElement, { autohide: false });
        selectToast.show();

        fetch('submit_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // No body, so this header is less critical but good practice
            },
            // No body needed as member_id is fetched from session on server side
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }).catch(() => {
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                selectToastBody.textContent = data.message;
                selectToastElement.classList.remove('bg-secondary');
                selectToastElement.classList.add('bg-success', 'text-white');
                // After successful submission, clear the cart display and recalculate total
                alert("已送出訂單，請刷新頁面!");
                window.location.reload();
                //document.querySelector('.row.justify-content-center').innerHTML = "<p class='text-center'>已送出訂單，請刷新頁面。</p>";
                fetchAllCartDataAndRecalc(); // This will reset the total price to 0

                // Optionally redirect after a short delay
                // setTimeout(() => {
                //     window.location.href = 'order_success.php?order_id=' + data.order_id; // Redirect to an order success page
                // }, 1500);

            } else {
                selectToastBody.textContent = data.message || '訂單送出失敗。';
                selectToastElement.classList.remove('bg-secondary', 'bg-success');
                selectToastElement.classList.add('bg-danger', 'text-white');
            }
            selectToast.show();
            setTimeout(() => selectToast.hide(), 3000); // Hide toast after 3 seconds
        })
        .catch(error => {
            console.error('Error submitting order:', error);
            selectToastBody.textContent = '送出訂單時發生錯誤: ' + error.message;
            selectToastElement.classList.remove('bg-secondary', 'bg-success');
            selectToastElement.classList.add('bg-danger', 'text-white');
            selectToast.show();
            setTimeout(() => selectToast.hide(), 3000);
        });
    }

    </script>

    <!-- 搜尋功能-->
    <script>
        document.getElementById('search-input').addEventListener('input', function() {
            const query = this.value.trim();
            const resultBox = document.getElementById('search-results');

            if (query.length === 0) {
                resultBox.style.display = 'none';
                resultBox.innerHTML = '';
                return;
            }

            fetch(`search_api.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        resultBox.style.display = 'block';
                        resultBox.innerHTML = data.map(item =>
                            `<a href="product_detail.php?id=${item.id}"
                                class="d-flex align-items-center gap-2 p-2 text-decoration-none text-dark border-bottom"
                                onclick="document.getElementById('search-results').style.display='none'; document.getElementById('search-input').value = '';">
                                <img src="${item.img}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" />
                                <span>${item.name}</span>
                            </a>`
                        ).join('');
                    } else {
                        resultBox.style.display = 'block';
                        resultBox.innerHTML = '<div class="p-2 text-muted">找不到商品</div>';
                    }
                });
        });
    </script>
    <!-- 點擊空白處就關掉搜尋結果 -->
    <script>
        document.addEventListener('click', function (e) {
            const input = document.getElementById('search-input');
            const resultBox = document.getElementById('search-results');

            if (!input.contains(e.target) && !resultBox.contains(e.target)) {
                resultBox.style.display = 'none';
            }
        });

        document.getElementById('search-input').addEventListener('focus', function() {
            const event = new Event('input');
            this.dispatchEvent(event);
        });

        document.addEventListener('click', function (event) {
            const input = document.getElementById('search-input');
            const results = document.getElementById('search-results');
            if (!input.contains(event.target) && !results.contains(event.target)) {
                results.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            fetchAllCartDataAndRecalc(); // 頁面載入時呼叫一次
        });

    </script>
    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.dropotron.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>