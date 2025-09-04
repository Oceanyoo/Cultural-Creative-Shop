<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['member_id'])) {
    header("Location: SignIn.php");
    exit;
}

$member_id = $_SESSION['member_id'];

// 撈會員資料
$sql = "SELECT * FROM member WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

// 撈會員的訂單資料
function getProductName($conn, $product_id) {
    $name = '';

    // 查 pr 表
    $stmt = $conn->prepare("SELECT name FROM pr WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        $stmt->close();
        return $name;
    }
    $stmt->close();

    // 查不到再查 pr2 表
    $stmt = $conn->prepare("SELECT name FROM pr2 WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        $stmt->close();
        return $name;
    }
    $stmt->close();
}

function getProductImage($conn, $product_id) {
    $img = '';
    // 查 pr 表
    $stmt = $conn->prepare("SELECT img FROM pr WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($img);
    if ($stmt->fetch()) {
        $stmt->close();
        return $img;
    }
    $stmt->close();

    // 查不到再查 pr2 表
    $stmt = $conn->prepare("SELECT img FROM pr2 WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($img);
    if ($stmt->fetch()) {
        $stmt->close();
        return $img;
    }
    $stmt->close();

    return "images/default.png"; // 沒找到圖的預設圖片
}


// 撈會員訂單
$order_sql = "SELECT order_id, product_id, amount, total_money FROM order_records WHERE member_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $member_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();


// 加這段！把訂單依 order_id 分組
$grouped_orders = [];
$order_totals = []; // 新增：儲存每筆訂單的總價

foreach ($orders as $order) {
    $grouped_orders[$order['order_id']][] = $order;

    // 如果這筆訂單還沒累加過，初始化為 0
    if (!isset($order_totals[$order['order_id']])) {
        $order_totals[$order['order_id']] = 0;
    }

    // 把這筆商品的金額加進去
    $order_totals[$order['order_id']] += $order['total_money'];
}


// 修改會員資料（POST）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['birthday'])) {
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];

    $update_sql = "UPDATE member SET `E-mail` = ?, Birthday = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $email, $birthday, $member_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: profile.php");
    exit;
}


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

/* 卡片風格外框 NEWWWWWWWWWWWWWWWWWWWWWWWWWWWW */
.profile-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

.profile-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

/* 標題風格 */
h2 {
    font-weight: bold;
    color: #6c8596;
}

/* 表格樣式強化 */
.table td, .table th {
    vertical-align: middle;
    font-size: 1.1rem;
}

/* 表格 hover 效果 */
.table-hover tbody tr:hover {
    background-color: #f2f8ff;
}

/* 按鈕一致風格 */
.btn-primary {
    background-color: #A5C0DC;
    border-color: #A5C0DC;
}
.btn-primary:hover {
    background-color: #6c8596;
    border-color: #6c8596;
}

.btn-secondary {
    background-color: #ccc;
    border-color: #ccc;
}
.btn-secondary:hover {
    background-color: #999;
    border-color: #999;
}

img.order-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 0 4px rgba(0,0,0,0.1);
}



/* 圓形圖片樣式 */
 .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        .profile-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        h2 {
            font-weight: bold;
            color: #6c8596;
        }
        .table td, .table th {
            vertical-align: middle;
            font-size: 1.1rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f2f8ff;
        }
        .rounded-circle {
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-custom {
            background-color: #6c8596;
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background-color: #4f6c7e;
        }
        .alert-fade {
            transition: opacity 1s ease-out;
        }

        .order-thumbnail {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.product-link:hover .order-thumbnail {
    transform: scale(1.2);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.product-name {
    font-size: 1.1rem;
    font-weight: 500;
    color: #333;
}

.product-link {
    text-decoration: none;
    color: inherit;
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
<div class="container mt-5">
    <div class="profile-card">
        <h2 class="mb-4">會員個人資料</h2>
        <?php if (isset($_GET['password_updated'])): ?>
            <div id="successAlert" class="alert alert-success alert-fade" role="alert">密碼更新成功！</div>
        <?php endif; ?>
        <?php if ($member): ?>
            <table class="table table-bordered table-hover">
                <tr>
                    <th>帳號</th>
                    <td><?= htmlspecialchars($member['Account']) ?></td>
                </tr>
                <tr>
                    <th>電子信箱</th>
                    <td><?= htmlspecialchars($member['E-mail']) ?></td>
                </tr>
                <tr>
                    <th>會員生日</th>
                    <td><?= htmlspecialchars($member['Birthday']) ?></td>
                </tr>
            </table>
            <div class="mt-3 d-flex flex-wrap gap-2">
                <a href="edit_profile.php" class="btn btn-custom">編輯資料</a>
                <a href="change_password.php" class="btn btn-custom">修改密碼</a>
                <a href="logout.php" class="btn btn-secondary">登出</a>
            </div>
        <?php else: ?>
            <p>找不到會員資料。</p>
        <?php endif; ?>
    </div>
</div>

<!-- 訂單紀錄 -->
<div class="container mt-5">
    <div class="profile-card">
        <h2 class="mb-4">訂單紀錄</h2>
        <?php if (!empty($grouped_orders)): ?>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>商品名稱</th>
                        <th>數量</th>
                        <th>總金額</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped_orders as $order_id => $items): ?>
                        <?php $first = $items[0]; ?>
                        <tr>
                            <td><?= htmlspecialchars($order_id) ?></td>
                            <td>
                                <a href="product_detail.php?id=<?= $first['product_id'] ?>" 
                                class="product-link d-flex align-items-center gap-3"
                                title="<?= htmlspecialchars(getProductName($conn, $first['product_id'])) ?>">
                                    <img src="<?= getProductImage($conn, $first['product_id']) ?>" 
                                        alt="商品圖片" class="order-thumbnail">
                                    <span class="product-name"><?= htmlspecialchars(getProductName($conn, $first['product_id'])) ?></span>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($first['amount']) ?></td>
                            <td>
                                $<?= htmlspecialchars($order_totals[$order_id]) ?>
                                <?php if (count($items) > 1): ?>
                                    <button class="btn btn-sm btn-primary float-end toggle-details" data-target="details-<?= $order_id ?>">▼</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (count($items) > 1): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <tr class="order-details details-<?= $order_id ?>" style="display: none;">
                                    <td></td>
                                    <td>
                                        <a href="product_detail.php?id=<?= $item['product_id'] ?>" 
                                        class="product-link d-flex align-items-center gap-3"
                                        title="<?= getProductName($conn, $item['product_id']) ?>">
                                            <img src="<?= getProductImage($conn, $item['product_id']) ?>" 
                                                alt="商品圖片" class="order-thumbnail">
                                            <span class="product-name"><?= getProductName($conn, $item['product_id']) ?></span>
                                        </a>
                                    </td>
                                    <td><?= $item['amount'] ?></td>
                                    <td>$<?= $item['total_money'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
            </table>
        <?php else: ?>
            <p>尚無任何訂單紀錄。</p>
        <?php endif; ?>
    </div>
</div>


<script>
// 密碼更新提示淡出
setTimeout(() => {
    const alert = document.getElementById('successAlert');
    if (alert) {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 1000);
    }
}, 2500);
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

document.querySelectorAll('.toggle-details').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.dataset.target;
        const detailRow = document.getElementById(targetId);
        if (detailRow.style.display === 'none') {
            detailRow.style.display = 'table-row';
        } else {
            detailRow.style.display = 'none';
        }
    });
});

document.querySelectorAll('.toggle-details').forEach(button => {
    button.addEventListener('click', () => {
        const targetClass = '.details-' + button.dataset.target.split('-')[1];
        document.querySelectorAll(targetClass).forEach(row => {
            row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
        });
    });
});


</script>
</body>
</html>