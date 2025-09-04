<?php 
session_start();
include 'connection.php'; 
//NEW3333333333333333333333333333333333
$favorited_ids = [];
if (isset($_SESSION['member_id'])) {
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT product_id FROM favorite WHERE member_id = ? AND table_name = 'pr'");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $favorited_ids[] = $row['product_id'];
    }
    $stmt->close();
}

$carted_ids = [];
if (isset($_SESSION['member_id'])) {
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT product_id FROM shopping_cart WHERE member_id = ? AND table_name = 'pr1'");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $carted_ids[] = $row['product_id'];
    }
    $stmt->close();
}

?>
<!-- NEW3333333333333333333333333333333333 -->

<a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>"></a>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
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

/* 右箭頭需旋轉方向 */
.carousel-control-next-icon::after {
    transform: translate(-50%, -50%) rotate(-45deg);
}

/* 3. 去除左右控制按鈕的背景色（避免長方形底） */
.carousel-control-prev,
.carousel-control-next {
    background-color: transparent !important;
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

/* 如果你不要按鈕 hover 有透明黑色背景，這樣寫 */
.carousel-control-prev:hover,
.carousel-control-next:hover {
    background-color: transparent !important;
}

/* 商品標題改成灰色 */
.card-title {
    color: #555555; /* 深灰色，可調整 */
    font-weight: bold;
}

/* 按鈕底色改為鮮藍色 */
.btn-primary {
    background-color:  #A5C0DC; /* 鮮藍色 */
    border-color:  #A5C0DC;
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
    color: #ccc; /* 預設灰色 */
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

/* 商品圖片 hover 效果 */
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
    color:rgb(110, 151, 194);        /* 變色 */
    transform: scale(1.2); /* 放大 */
    text-decoration: underline; /* 底線 */
}


/* 確保 footer 是 flex 排列 */
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
        right: 40px; /* 大螢幕時往左40px */
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

/* 先把 select 背景改成白色 */
#product-wrapper > form.mb-4.text-center select {
    background-color: white !important;
    border: 1px solid #ccc;
    padding-right: 30px; /* 給右邊空間放箭頭框 */
    border-radius: 4px;
    appearance: none;       /* 取消預設箭頭 */
    -webkit-appearance: none;
    -moz-appearance: none;
    position: relative;
    cursor: pointer;
}

/* 自訂下拉箭頭方框 */
#product-wrapper > form.mb-4.text-center select::after {
    content: "";
    position: absolute;
    top: 50%;
    right: 10px;
    width: 20px;
    height: 20px;
    background: #ddd; /* 箭頭背景色 */
    border: 1px solid #bbb;
    border-radius: 4px;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
    box-sizing: border-box;
}

/* 自訂三角形箭頭，用 svg 背景 */
#product-wrapper > form.mb-4.text-center select {
    background-image:
      url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%23666' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 10px 6px;
}

/* 取消原本::after，改用 background-image */
#product-wrapper > form.mb-4.text-center select::after {
    content: none;
}

/* 先給整個搜尋區塊寬度最大限制，讓他不會太寬 */
#search {
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* 搜尋框本體 */
#search form#search-form {
    flex-grow: 1;
    display: flex;
}

/* 輸入框和按鈕 */
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

/* 圖示清單 */
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

/* RWD - 小螢幕調整 */
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
  background-color:rgb(187, 209, 233); /* 你想要的淺色背景 */
  color:rgb(88, 88, 88);            /* 深色字體 */
}
.favorite-toast .toast-body {
  font-weight: bold; /* 或 font-weight: 600; */
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

#search-results {
    max-height: 300px;           /* 限制高度 */
    overflow-y: auto;            /* 超出高度時可滾動 */
    scroll-behavior: smooth;     /* 平滑滾動效果（可選） */
}

.toast-cart-custom {
  background-color: #bbd1e9; /* 淡藍色 */
  color: #333333;
  /*font-weight: bold;*/
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
                                <li><a href="02.php?filter=type:1">明信片&其他</a></li>
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

<!-- 公告圖片區（替代 advertisement） -->
<section id="announcement">
  <div id="homeCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
    
    <!-- 圓點導覽 -->
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
    </div>

    <!-- 輪播圖片 -->
    <div class="carousel-inner text-center">
      <div class="carousel-item active">
        <a href="try.php">
            <img src="images/product_img/ashely_top.png" class="d-block mx-auto w-75" alt="圖片1">
        </a>
        </div>
      <div class="carousel-item">
        <a href="try.php?filter=special:spring">
            <img src="images/A_2.png" class="d-block mx-auto w-75" alt="圖片2">
        </a> 
      </div>
      <div class="carousel-item">
        <a href="try.php">
            <img src="images/A_3.png" class="d-block mx-auto w-75" alt="圖片3">
        </a>
      </div>
      <div class="carousel-item">
        <a href="try.php?filter=special:animal">
            <img src="images/A_4.png" class="d-block mx-auto w-75" alt="圖片4">
        </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    var myCarousel = document.querySelector('#homeCarousel');
    var carousel = new bootstrap.Carousel(myCarousel, {
        interval: 3000,
        wrap: true
    });
    });
    </script>

    <!-- 左右箭頭 -->
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon bg-success rounded-circle" aria-hidden="true"></span>
      <span class="visually-hidden">上一張</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon bg-success rounded-circle" aria-hidden="true"></span>
      <span class="visually-hidden">下一張</span>
    </button>
  </div>
</section>


<!-- 篩選器 -->
 <div id="product-wrapper" style="position: relative;">
    <form method="GET" action="" class="mb-4 text-center">
    <label for="filter">選擇商品類別：</label>
    <select name="filter" id="filter" onchange="this.form.submit()">
        <option value="">全部商品</option>
        <option value="type:1" <?= (isset($_GET['filter']) && $_GET['filter'] == 'type:1') ? 'selected' : '' ?>>明信片&其他</option>
        <option value="type:2" <?= (isset($_GET['filter']) && $_GET['filter'] == 'type:2') ? 'selected' : '' ?>>徽章</option>
        <option value="type:3" <?= (isset($_GET['filter']) && $_GET['filter'] == 'type:3') ? 'selected' : '' ?>>吊飾</option>
        <option value="special:animal" <?= (isset($_GET['filter']) && $_GET['filter'] == 'special:animal') ? 'selected' : '' ?>>小動物飲品系列</option>
        <option value="special:spring" <?= (isset($_GET['filter']) && $_GET['filter'] == 'special:spring') ? 'selected' : '' ?>>春日系列</option>
        <option value="special:new" <?= (isset($_GET['filter']) && $_GET['filter'] == 'special:new') ? 'selected' : '' ?>>5月新品</option>
        <option value="special:hot" <?= (isset($_GET['filter']) && $_GET['filter'] == 'special:hot') ? 'selected' : '' ?>>熱銷商品</option>
    </select>
</form>


    <div class="container py-5">
        <h1 class="mb-4 text-center">商品列表</h1>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            $filter = isset($_GET['filter']) ? $_GET['filter'] : '';

            if (!empty($filter)) {
                if (strpos($filter, ':') !== false) {
                    list($field, $value) = explode(':', $filter, 2);

                    if ($field === 'type') {
                        $type = intval($value);
                        $stmt = $conn->prepare("SELECT * FROM pr WHERE type = ?");
                        $stmt->bind_param("i", $type);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } elseif ($field === 'special') {
                        $special = $value;
                        $stmt = $conn->prepare("SELECT * FROM pr WHERE special = ?");
                        $stmt->bind_param("s", $special);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $result = $conn->query("SELECT * FROM pr");
                    }
                } else {
                    $result = $conn->query("SELECT * FROM pr");
                }
            } else {
                $result = $conn->query("SELECT * FROM pr");
            }


            if ($result->num_rows > 0):
                while ($product = $result->fetch_assoc()):
            ?>
                <div class="col">
                    <div class="card h-100">
                        <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                            <img src="<?= htmlspecialchars($product['img']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h5>
                            <p class="card-text">價格：$<?= htmlspecialchars($product['price']) ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="heart-icon  <?= in_array($product['id'], $favorited_ids) ? 'favorited' : '' ?>" onclick="toggleFavorite(this)" 
                            data-product-id="<?= htmlspecialchars($product['id'])?>">
                                <i class="fa fa-heart"></i>
                            </span>

                    <!--NEW!!!!!-->
                           <?php if (isset($_SESSION["user"])): ?>
                            <!--改-->
                            <button class="btn btn-primary <?= in_array($product['id'], $carted_ids) ? 'in-cart' : '' ?>" onclick="handleAddToCart(this)"
                            data-product-id="<?= htmlspecialchars($product['id']) ?>">
                                <?php
                                // NEW: 根據商品是否在購物車來決定按鈕文字
                                if (in_array($product['id'], $carted_ids)) {
                                    echo '已在購物車'; // 如果在購物車中，顯示「已在購物車」
                                } else {
                                    echo '加入購物車'; // 否則，顯示「加入購物車」
                                }
                                ?>
                            </button>

                            <?php else: ?>
                                <button class="btn btn-primary" onclick="redirectToLogin()">加入購物車</button>
                            <?php endif; ?>
                    <!--NEW END!!!!!-->

                        </div>
                    </div>
                </div>
            <?php
                endwhile;
            else:
                echo "<p class='text-center'>目前沒有商品。</p>";
            endif;
            ?>
        </div>
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
      <div class="toast-body">
         商品已加入購物車！
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
													<a href="https://www.instagram.com/ashelypainting?igsh=MWFwY2R4bWx3NWhycQ==">AshelyPainting</a>
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
<!--收藏相關Script-->

        <!--new 跟愛心收藏有關-->
        <script>
            // 判斷是否登入（由 PHP 產生變數）
            const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
        </script>


<script>
    const toastElement = document.getElementById('favoriteToast');
    const toastBody = toastElement.querySelector('.toast-body');  // 這行缺少了
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,     // 自動隱藏
        delay: 3000         // 3秒後隱藏
    });

    
// NEW333333333333    
function toggleFavorite(el) {
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    const tableName = window.location.pathname.includes('01.php') ? 'pr2' : 'pr';
    const card = el.closest('.card');
    //const productId = card.querySelector('a').href.split('id=')[1];
    const productId = el.dataset.productId;
    const isFavorited = el.classList.toggle('favorited');

    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&action=${isFavorited ? 'add' : 'remove'}&table_name=${tableName}`
    });

    const toastBody = document.querySelector('#favoriteToast .toast-body');
    toastBody.textContent = isFavorited ? '收藏成功！' : '已取消收藏';
    const toast = new bootstrap.Toast(document.getElementById('favoriteToast'));
    toast.show();
}

// NEW333333333333 

function handleAddToCart(button) {
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    // 修正：直接從按鈕的 dataset 屬性取得 product ID
    const productId = button.dataset.productId;//haaaaaaaaaaa  //記得愛心也要改
    const isInCart = button.classList.contains('in-cart');
    const action = isInCart ? 'remove' : 'add';

    // 這些 console.log 會幫助你確認取得的值是否正確
    /*console.log('--- 準備發送 Fetch 請求 ---');
    console.log('從按鈕獲取的 Product ID:', productId); // 檢查這裡
    console.log('獲取的 Action:', action);
    console.log('---------------------------');*/

    fetch('toggle_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&action=${action}`
    })
    /*.then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || `HTTP error! status: ${response.status}`);
            }).catch(() => {
                throw new Error(`HTTP error! status: ${response.status}. 後端可能存在 PHP 錯誤或未返回 JSON。`);
            });
        }
        return response.json();
    })*/

    .then(response => response.json())

    .then(data => {
        const toastBody = document.querySelector('#cartToast .toast-body');
        const toast = new bootstrap.Toast(document.getElementById('cartToast'));

        if (data.status === 'success') {
            if (data.action_performed === 'added') {
                button.classList.add('in-cart');
                button.textContent = '已在購物車';
                showAddToCartAnimation(button);
                toastBody.textContent = data.message;
            } else if (data.action_performed === 'removed') {
                button.classList.remove('in-cart');
                button.textContent = '加入購物車';
                toastBody.textContent = data.message;
            }
        } else if (data.status === 'info') {
            toastBody.textContent = data.message;
        } else {
            console.error('後端返回錯誤:', data.message);
            toastBody.textContent = '操作失敗：' + (data.message || '未知錯誤');
        }
        toast.show();
    })
    .catch(error => {
        console.error('Fetch 請求錯誤:', error);
        alert('購物車操作失敗，請檢查瀏覽器控制台的錯誤訊息。');
    });
}


// 確保 showAddToCartAnimation 函數中移除了重複的 toast.show()
function showAddToCartAnimation(button) {
    const card = button.closest('.card');
    const img = card.querySelector('.card-img-top');
    if (!img) return;

    const cartIcon = document.querySelector('a[href="test_cart_3.php"] .fa-shopping-cart');
    if (!cartIcon) return;

    const flyingImg = img.cloneNode(true);
    const rect = img.getBoundingClientRect();
    flyingImg.style.position = "fixed";
    flyingImg.style.left = rect.left + "px";
    flyingImg.style.top = rect.top + "px";
    flyingImg.style.width = rect.width + "px";
    flyingImg.style.height = rect.height + "px";
    flyingImg.style.transition = "all 0.6s ease-in-out";
    flyingImg.style.zIndex = 9999;
    document.body.appendChild(flyingImg);

    setTimeout(() => {
        const cartRect = cartIcon.getBoundingClientRect();
        flyingImg.style.left = cartRect.left + "px";
        flyingImg.style.top = cartRect.top + "px";
        flyingImg.style.width = rect.width * 0.1 + "px";
        flyingImg.style.height = rect.height * 0.1 + "px";
        flyingImg.style.opacity = 0.5;
    }, 500);

    setTimeout(() => {
        document.body.removeChild(flyingImg);
        // 這裡不再需要 toast.show()，因為 handleAddToCart 已經處理了
        // const toast = new bootstrap.Toast(document.getElementById('cartToast'));
        // toast.show();
    }, 1300);
}


    //new 
    /*function handleAddToCart(button) {
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    // 加入購物車動畫與處理
    showAddToCartAnimation(button);
}*///多餘重複的



</script>
<!--加入購物車相關Script-->


<!--<script>
  const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));

  function showAddToCartToast() {
    cartToast.show();
  }
</script>-->

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

    // 假設你用 Ajax call PHP
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
<!-- 點擊空白處就關掉搜尋結果  add  --> 
<script>
document.addEventListener('click', function (e) {
    const input = document.getElementById('search-input');
    const resultBox = document.getElementById('search-results');

    // 如果點擊的不是搜尋框或結果區塊
    if (!input.contains(e.target) && !resultBox.contains(e.target)) {
        resultBox.style.display = 'none';
    }
});

//使用者即使沒改變文字，只要點進搜尋框，也會重新顯示結果
document.getElementById('search-input').addEventListener('focus', function() {
    const event = new Event('input');
    this.dispatchEvent(event); // 重新觸發 input 事件
});

//點到搜尋框跟結果框以外的地方，就會自動把搜尋結果藏起來
document.addEventListener('click', function (event) {
    const input = document.getElementById('search-input');
    const results = document.getElementById('search-results');
    if (!input.contains(event.target) && !results.contains(event.target)) {
        results.style.display = 'none';
    }
});

</script>


<!--NEW!!!!-->
<script>
// 飛進去購物車的動畫
function showAddToCartAnimation(button) {
    const card = button.closest(".card");
    const img = card.querySelector("img");
    const cartIcon = document.querySelector(".fa-shopping-cart");

    // 先平滑滾動到購物車圖示位置（讓使用者看到飛入效果目標）
    cartIcon.scrollIntoView({ behavior: "smooth", block: "center" });

    // 建立一個複製的圖片，模擬飛入動畫
    const flyingImg = img.cloneNode(true);
    const rect = img.getBoundingClientRect();
    flyingImg.style.position = "fixed";
    flyingImg.style.left = rect.left + "px";
    flyingImg.style.top = rect.top + "px";
    flyingImg.style.width = rect.width + "px";
    flyingImg.style.height = rect.height + "px";
    flyingImg.style.transition = "all 0.6s ease-in-out";
    flyingImg.style.zIndex = 9999;
    document.body.appendChild(flyingImg);

    // 取得購物車位置（等頁面滾動完後）
    setTimeout(() => {
        const cartRect = cartIcon.getBoundingClientRect();
        flyingImg.style.left = cartRect.left + "px";
        flyingImg.style.top = cartRect.top + "px";
        flyingImg.style.width = rect.width * 0.1 + "px";  // 10%
        flyingImg.style.height = rect.height * 0.1 + "px";
        flyingImg.style.opacity = 0.5;
    }, 500);


    // 移除動畫圖片並顯示加入購物車提示
    setTimeout(() => {
        document.body.removeChild(flyingImg);
        //const toast = new bootstrap.Toast(document.getElementById('cartToast'));
        //toast.show();
    }, 1300);
}

function redirectToLogin() {
    // 直接導向登入頁面
    window.location.href = 'SignIn.php';
}
</script>
<!--NEW END!!!!-->

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.dropotron.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
