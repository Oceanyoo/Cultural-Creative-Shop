<?php 
session_start();
include 'connection.php';

// 顯示使用者收藏清單（商品卡片）
$favorited_products = [];

if (isset($_SESSION['member_id'])) {
    $member_id = $_SESSION['member_id'];

    // 先抓 pr 表的收藏
    $sql1 = "SELECT pr.*, favorite.table_name FROM pr
             JOIN favorite ON pr.id = favorite.product_id
             WHERE favorite.member_id = ? AND favorite.table_name = 'pr'";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $member_id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $pr_products = $result1->fetch_all(MYSQLI_ASSOC);
    $stmt1->close();

    // 再抓 pr2 表的收藏
    $sql2 = "SELECT pr2.*, favorite.table_name FROM pr2
             JOIN favorite ON pr2.id = favorite.product_id
             WHERE favorite.member_id = ? AND favorite.table_name = 'pr2'";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $member_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $pr2_products = $result2->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    // 合併兩張表資料
    $favorited_products = array_merge($pr_products, $pr2_products);
}


////處理購物車/////////////////////
//if (isset($_GET['id'])) {

    // NEW: 檢查商品是否已在購物車
    $carted_ids = [];
    if (isset($_SESSION['member_id'])) {
        $member_id = $_SESSION['member_id'];
        // 移除了 AND table_name = 'pr2'，因為現在直接從 all_products 獲取資料
        $stmt = $conn->prepare("SELECT product_id FROM shopping_cart WHERE member_id = ?");
        if (!$stmt) {
            // 處理準備語句失敗的錯誤
            error_log("Prepare statement failed: " . $conn->error);
        } else {
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $carted_ids[] = $row['product_id'];
            }
            $stmt->close();
        }
    }
//}

?>

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

/* new */
 .product-container {
            margin-top: 50px;
        }
        .product-image {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
.btn-primary {
    background-color: #A5C0DC;
    border-color: #A5C0DC;
    color: white;
}

.btn-primary:hover {
    background-color: #6c8596;
    border-color: #6c8596;
    color: white;
}

.btn-buy {
    background-color: #A5C0DC;
    border: none;
    color: white;
}

.btn-buy:hover {
    background-color: #6c8596;
    color: white;
}

.product-title:hover {
    color: #6e97c2;
    transition: color 0.3s ease;
    cursor: pointer;
}

.heart-icon {
    font-size: 24px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.3s, transform 0.2s;
    padding-left: 6px;
    padding-right: 6px;
}

.heart-icon:hover {
    transform: scale(1.2);
}

.heart-icon.favorited {
    color: red;
}
.favorite-toast {
  background-color: #bbd1e9;
  color: #444;
}
.favorite-toast .toast-body {
  font-weight: bold;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
/*add*/
#search-results {
    max-height: 300px;           /* 限制高度 */
    overflow-y: auto;            /* 超出高度時可滾動 */
    scroll-behavior: smooth;     /* 平滑滾動效果（可選） */
}


/* 評價區塊外觀 */
.review {
    margin-top: 50px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f8f9fa;
}

/* 星星評分 */
.star-rating {
    font-size: 2rem;
    color: #ccc;
    cursor: pointer;
    margin-bottom: 10px;
}

.star-rating span:hover,
.star-rating span.hovered,
.star-rating span.selected {
    color: gold;
}

/* 留言框樣式 */
.review textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    margin-top: 10px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    resize: vertical;
}

/* 評價提交按鈕 */
.review button[type="submit"] {
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #A5C0DC;
    border: none;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.review button[type="submit"]:hover {
    background-color: #6c8596;
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
/*NEW END!!! */
.card .card-img-top {
    width: 100%;
    height: auto;
    object-fit: contain;
    display: block;
    margin: 0 auto;
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
<!--NEW END!!!-->
                        <div class="nav-right"><!--購物車、搜詢欄、會員登入-->
                            <ul>
                                <li></li>
                                <li>
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <a href="favorite.php"><i class="icon solid fa-heart"></i></a>
                                    <?php else: ?>
                                        <a href="SignIn.php" onclick="return confirm('請先登入才能查看收藏清單');">
                                            <i class="icon solid fa-heart"></i>
                                        </a>
                                    <?php endif; ?>
                                </li>
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

<div class="container my-5">
    <h4 class="mb-4">收藏清單 ❤️</h4>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php if (!empty($favorited_products)): ?>
        <?php foreach ($favorited_products as $fav): ?>
            <div class="col">
                <div class="card h-100" id="fav-card-<?= $fav['id'] ?>">
                    <a href="product_detail.php?id=<?= htmlspecialchars($fav['id']) ?>">
                        <img src="<?= htmlspecialchars($fav['img']) ?>" class="card-img-top" alt="<?= htmlspecialchars($fav['name']) ?>">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="product_detail.php?id=<?= htmlspecialchars($fav['id']) ?>">
                                <?= htmlspecialchars($fav['name']) ?>
                            </a>
                        </h5>
                        <p class="card-text">價格：$<?= htmlspecialchars($fav['price']) ?></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button class="btn btn-danger btn-sm" onclick="removeFavorite(<?= $fav['id'] ?>, this)" data-table="<?= $fav['table_name'] ?>">取消收藏</button>
                        <button class="btn btn-primary <?= in_array($fav['id'], $carted_ids) ? 'in-cart' : '' ?>" onclick="handleAddToCart(this)"
                        data-product-id="<?= htmlspecialchars($fav['id']) ?>">
                        <?php
                        // NEW: 根據商品是否在購物車來決定按鈕文字
                        if (in_array($fav['id'], $carted_ids)) {
                            echo '已在購物車'; // 如果在購物車中，顯示「已在購物車」
                        } else {
                            echo '加入購物車'; // 否則，顯示「加入購物車」
                        }
                        ?>
                    </button>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">您尚未收藏任何商品。</p>
    <?php endif; ?>
    </div>
</div>

<!-- Toast 元素 -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
  <div id="removeToast" class="toast favorite-toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">已取消收藏</div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<!-- 加入購物車提示 Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
  <div id="cartToast" class="toast favorite-toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
         商品已加入購物車！
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>


<script>
function removeFavorite(productId, buttonElement) {
    if (!confirm("確定要取消收藏嗎？")) return;

    const tableName = buttonElement.getAttribute('data-table');

    fetch('remove_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + encodeURIComponent(productId) + '&table_name=' + encodeURIComponent(tableName)
    })
    .then(response => response.text())
    .then(data => {
        const card = buttonElement.closest('.col');
        if (card) {
            card.remove();
        }

        // ✅ Toast id 應為 removeToast，而不是 favoriteToast
        const toastBody = document.querySelector('#removeToast .toast-body');
        toastBody.textContent = '已取消收藏';
        const toast = new bootstrap.Toast(document.getElementById('removeToast'));
        toast.show();
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


<!--haaaaaaaaaaaaaaaaaaaaaaaaa-->
<script>
            // 判斷是否登入（由 PHP 產生變數）
            const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
        </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

function toggleFavorite(el, productId) {
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    const isFavorited = el.classList.contains('favorited');
    const action = isFavorited ? 'remove' : 'add';

    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&action=${action}`
    })
    .then(res => res.text())
    .then(data => {
        if (action === 'add') {
            el.classList.add('favorited');
            showToast('已加入收藏！');
        } else {
            el.classList.remove('favorited');
            showToast('已取消收藏！');
        }
    });
}

function showToast(msg) {
    const toastBody = document.querySelector('#favoriteToast .toast-body');
    toastBody.textContent = msg;
    const toast = new bootstrap.Toast(document.getElementById('favoriteToast'));
    toast.show();
}

function handleAddToCart(button) {
    if (!isLoggedIn) {
        window.location.href = 'SignIn.php';
        return;
    }

    // 修正：直接從按鈕的 dataset 屬性取得 product ID
    const productId = button.dataset.productId;//haaaaaaaaaaa  //記得愛心也要改
    const isInCart = button.classList.contains('in-cart');
    const action = isInCart ? 'remove' : 'add';


    fetch('toggle_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&action=${action}`
    })


    ///////////////////////////////////////////////////
    .then(response => response.json())
    ///////////////////////////////////////////////////
    .then(data => {
        const toastBody = document.querySelector('#cartToast .toast-body');
        const toast = new bootstrap.Toast(document.getElementById('cartToast'));

        if (data.status === 'success') {
            if (data.action_performed === 'added') {
                button.classList.add('in-cart');
                button.textContent = '已在購物車';
                //showAddToCartAnimation(button);
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


//改
/*function showAddToCartAnimation(button) {
    // 修正：直接獲取主要商品圖片，因為按鈕的父元素沒有 .card 類別
    const mainImageContainer = document.getElementById('mainImage');
    const img = mainImageContainer ? mainImageContainer.querySelector('img') : null; // 檢查 mainImageContainer 是否存在

    if (!img) {
        console.warn('動畫：未找到主要商品圖片（#mainImage 內的 img）。');
        return; // 如果圖片找不到，就中止動畫
    }

    // 修正：更精確地獲取購物車圖示
    // 購物車圖示在導覽列中
    const cartIconLink = document.querySelector('a[href="test_cart_3.php"] .fa-shopping-cart');

    if (!cartIconLink) {
        console.warn('動畫：未找到購物車圖示（a[href="test_cart_3.php"] .fa-shopping-cart）。');
        return; // 如果購物車圖示找不到，就中止動畫
    }

    // 平滑滾動到購物車圖示位置（讓使用者看到飛入效果目標）
    // 確保 cartIconLink 是一個元素並且有 scrollIntoView 方法
    if (cartIconLink && typeof cartIconLink.scrollIntoView === 'function') {
        cartIconLink.scrollIntoView({ behavior: "smooth", block: "center" });
    }

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
        const cartRect = cartIconLink.getBoundingClientRect(); // 使用 cartIconLink 的位置
        flyingImg.style.left = cartRect.left + "px";
        flyingImg.style.top = cartRect.top + "px";
        flyingImg.style.width = rect.width * 0.1 + "px";  // 縮小到原圖 10%
        flyingImg.style.height = rect.height * 0.1 + "px";
        flyingImg.style.opacity = 0.5;
    }, 500);

    // 移除動畫圖片
    setTimeout(() => {
        document.body.removeChild(flyingImg);
    }, 1300);
}*/

function redirectToLogin() {
    // 直接導向登入頁面
    window.location.href = 'SignIn.php';
}
</script>


</body>