<!--NEW!!!!-->
<?php
session_start();
include 'connection.php';
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    /////////////////////////////////
    $isFavorited = false;
    if (isset($_SESSION['member_id'])) {
        $member_id = $_SESSION['member_id'];
        $check_fav_sql = "SELECT 1 FROM favorite WHERE member_id = $member_id AND product_id = $product_id";
        $fav_result = mysqli_query($conn, $check_fav_sql);
        if (mysqli_num_rows($fav_result) > 0) {
            $isFavorited = true;
        }
    }
    ///////////////////////////////
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

    // 撈商品主資料
    $sql = "SELECT * FROM all_products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);

    // 撈圖片資料
    $img_sql = "SELECT * FROM all_products_img WHERE product_id = $product_id";
    $img_result = mysqli_query($conn, $img_sql);
    $images = [];
    while ($row = mysqli_fetch_assoc($img_result)) {
        $images[] = $row;
    }

    // 新增評價（必須登入且有輸入評價）
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SESSION['member_id']) &&
    !empty($_POST['review_text']) &&
    isset($_POST['rating'])) {

    $member_id = $_SESSION['member_id'];
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    $rating = intval($_POST['rating']);

    $sql_insert = "INSERT INTO reviews (product_id, member_id, review_text, rating)
                   VALUES ('$product_id', '$member_id', '$review_text', '$rating')";

    $res = mysqli_query($conn, $sql_insert);
    if (!$res) {
        die("評論新增失敗: " . mysqli_error($conn) . "<br>SQL: " . $sql_insert);
    } else {
        header("Location: product_detail.php?id=$product_id");
        exit;
    }
    }
    
    // 撈取評論資料，放在 POST 條件外，保證一定有值
    $sql_reviews = "SELECT r.*, m.Account AS username 
                    FROM reviews r 
                    JOIN member m ON r.member_id = m.id 
                    WHERE r.product_id = $product_id
                    ORDER BY r.created_at DESC";  // 新增排序

    $result_reviews = mysqli_query($conn, $sql_reviews);
    if ($result_reviews) {
        $reviews = mysqli_fetch_all($result_reviews, MYSQLI_ASSOC);
    } else {
        $reviews = [];
    }

} else {
    echo "<p>找不到商品。</p>";
    exit;
}
?>
<!-- NEWEND!!!! -->

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
                                <li>
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <a href="test_cart_3.php"><i class="icon solid fa-shopping-cart"></i></a>
                                    <?php else: ?>
                                        <a href="SignIn.php" onclick="return confirm('請先登入才能查看購物車');">
                                            <i class="icon solid fa-shopping-cart"></i>
                                        </a>
                                    <?php endif; ?>
                                </li>
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
<!--new-->
<div class="container product-container">
<?php if (!empty($product)) { ?>
        <div class="row">
            <!-- 左邊圖片 -->
            <div class="col-md-6">
                <div id="mainImage">
                    <img src="<?php echo $images[0]['img_path'] ?? ''; ?>" class="img-fluid" alt="主圖">
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <?php foreach ($images as $img) { ?>
                        <img src="<?php echo $img['img_path']; ?>" class="img-thumbnail" style="width: 100px; cursor: pointer;"
                             onclick="document.getElementById('mainImage').children[0].src=this.src;">
                    <?php } ?>
                </div>
            </div>

            <!-- 右邊商品資訊 -->
            <div class="col-md-6">
                <h2 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                <p><strong>價格：</strong> $<?php echo $product['price']; ?></p>
                <p><strong>商品介紹：</strong></p>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
               <div class="d-flex flex-column flex-md-row align-items-center gap-3 mt-3" style="padding-bottom: 40px;">
                    <span class="heart-icon <?= $isFavorited ? 'favorited' : '' ?>" onclick="toggleFavorite(this, <?= $product_id ?>)">
                        <i class="fa fa-heart"></i>
                    </span>

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

                    <button class="btn btn-buy flex-grow-1">直接購買</button>
                </div>
            </div>
        </div>
    <?php } ?>
</div>



<!--NEW!!!!!-->
<!-- 評價區塊 -->
<div class="review">
    <h4>商品評價</h4>

    <?php if (isset($_SESSION['user'])): ?>
    <form method="POST">
        <div class="star-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span data-value="<?= $i ?>">&#9733;</span>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating" required>
        <textarea name="review_text" placeholder="請輸入您的留言..." required></textarea>
        <button type="submit">送出評價</button>
    </form>
    <?php else: ?>
        <p>請先 <a href="SignIn.php">登入</a> 才能留下評價。</p>
    <?php endif; ?>

    <!-- 評論列表 -->
    <?php foreach ($reviews as $review): ?>
    <div class="review-item" style="margin-top: 1em; padding: 1em; border: 1px solid #ccc; border-radius: 10px;">
        <strong><?= htmlspecialchars($review['username']) ?></strong>：
        <div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span style="color: <?= $i <= $review['rating'] ? 'gold' : '#ccc' ?>;">★</span>
            <?php endfor; ?>
        </div>
        <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
        <small><?= $review['created_at'] ?></small>
    </div>
    <?php endforeach; ?>
</div>

<!-- 這裡放你的 footer 和腳本 -->
<script>
// 你的星星評分 JS (跟之前的差不多)
document.querySelectorAll('.star-rating span').forEach(function(star) {
    star.addEventListener('mouseover', function() {
        const value = parseInt(this.dataset.value);
        document.querySelectorAll('.star-rating span').forEach(function(s) {
            s.classList.remove('hovered');
            if (parseInt(s.dataset.value) <= value) {
                s.classList.add('hovered');
            }
        });
    });

    star.addEventListener('mouseout', function() {
        document.querySelectorAll('.star-rating span').forEach(function(s) {
            s.classList.remove('hovered');
        });
    });

    star.addEventListener('click', function() {
        const value = this.dataset.value;
        document.getElementById('rating').value = value;
        document.querySelectorAll('.star-rating span').forEach(function(s) {
            s.classList.remove('selected');
            if (parseInt(s.dataset.value) <= value) {
                s.classList.add('selected');
            }
        });
    });
});
</script>
<!--NEW END!!!!!-->

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
<!-- 收藏成功提示框 -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
  <div id="favoriteToast" class="toast favorite-toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
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
  <div id="cartToast" class="toast favorite-toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
         商品已加入購物車！
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>




<!-- Scripts -->

        <!--new 跟愛心收藏有關-->
        <script>
            // 判斷是否登入（由 PHP 產生變數）
            const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
        </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 收藏功能 -->


<script>

/////////////////////////////////////////////
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
///////////////////////////////////////
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

<!-- 星星評分功能 -->


<script>
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


//改
function showAddToCartAnimation(button) {
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
}



</script>
<!--加入購物車相關Script-->


<!--<script>
  const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));

  function showAddToCartToast() {
    cartToast.show();
  }
</script>-->


</script>


<!--NEW!!!!-->
<script>

function redirectToLogin() {
    // 直接導向登入頁面
    window.location.href = 'SignIn.php';
}
</script>
<!--NEW END!!!!-->

<!--NEW END!!!-->

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.dropotron.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
