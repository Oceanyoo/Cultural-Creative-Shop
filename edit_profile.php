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

// 修改會員資料（POST）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                                <li><a href="#"><i class="icon solid fa-shopping-cart"></i></a></li>
                                <li>
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <a href="favorite.php"><i class="icon solid fa-heart"></i></a>
                                    <?php else: ?>
                                        <a href="SignIn.php" onclick="return confirm('請先登入才能查看收藏清單');">
                                            <i class="icon solid fa-heart"></i>
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
<div class="container mt-5">
    <h2 class="mb-4">會員個人資料</h2>
    <form method="POST">
        <table class="table table-bordered bg-white">
            <tr>
                <th>帳號</th>
                <td><?php echo htmlspecialchars($member['Account']); ?></td>
            </tr>
            <tr>
                <th>電子信箱</th>
                <td>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($member['E-mail']); ?>" class="form-control">
                </td>
            </tr>
            <tr>
                <th>生日</th>
                <td>
                    <input type="date" name="birthday" value="<?php echo htmlspecialchars($member['Birthday']); ?>" class="form-control">
                </td>
            </tr>
        </table>
        <button type="submit" class="btn btn-primary">儲存修改</button>
        <a href="logout.php" class="btn btn-secondary">登出</a>
    </form>
</div>
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
</body>