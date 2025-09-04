<!--NEW!!!!-->
<?php
session_start();
?>
<!--NEW END!!!!-->
<!DOCTYPE HTML>
<html>
	<head>
		<title>Ashely painting & Anycolor</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.nav-right {
	display: flex;
	align-items: center;
	gap: 10px;
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
#search-results {
    max-height: 300px;           /* 限制高度 */
    overflow-y: auto;            /* 超出高度時可滾動 */
    scroll-behavior: smooth;     /* 平滑滾動效果（可選） */
}


#search-area {
    display: flex;
    align-items: center;
    gap: 10px;
    position: absolute;
    top: 1.3em;
    right: 2em;
}

/* 搜尋欄與按鈕樣式延用你原本的即可 */
#search-form-wrapper {
    display: flex;
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
		<div id="page-wrapper"> <!-- 2559 -->
				<section id="header">
					<div class="container"><!--208-->
						<!-- Search -->
						    <div id="title">
								<a href="index.php" id="logo-link">
									<img src="./images/title.png" alt="商標" id="logo"><!--1908-->
								</a>
							</div>
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
						<!-- Nav -->
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
											<!--<li><a href="#">吊飾 & 手機鍊</a></li>--><!--這個要記得補回來-->
										</ul>
									</li>
									<li><a href="02.php?filter=special:new"><span>新品上市 New!!</span></a></li>
									<li><a href="02.php?filter=special:hot"><span>熱銷商品 Hot!!</span></a></li>
								</ul>
							</nav>
						</section>
						<br>
						<section id="announcement">
							<div class="container">
								
								<a href="#" class="image featured"><img src="images/A.png" alt="" /></a>
							</div>
						</section>

						<div id="namespace">
							<h1><a href="02.php">Ashely painting </a></h1>
							<h1><a href="01.php">& Anycolor</a></h1>
							<p>More than just a product, it's a piece of art.</p>	
						</div>
					</div>
				</section>	
				
			<!--AshelyPainting & Anycolor-->
				<section id="page_change">
					<div id="Ashely">
						<a href="02.php" id="Ashely_page">
							<br>
							<img src="images/2.png" alt="雪莉" id="page1">
						</a>
					</div>
					<br>
					<div id="Annie">
						<a href="01.php" id="Annie_page">
							<img src="images/1.png" alt="安妮" id="page2">
						</a>
					</div>
					<br>
					<div id="main_product2"><!--畫畫類型-->
						<br>
						<h2>★ Ashely painting 本期主打 ★</h2>
					</div>
					<br>
					<div>
						<a href="02.php?filter=special:animal" id="sugar_page">
							<img src="images/sugarRush.png" alt="甜蜜衝刺" id="page4">
						</a>
					</div>
					<br>
					<div>
						<a href="#" id="commision_page">
							<img src="images/commision.png" alt="委託" id="page5">
						</a>
					</div>
					<br>
					<div>
						<a href="#" id="collection_page">
							<img src="images/commsion_collection.png" alt="圖集" id="page6">
						</a>
					</div>
					<div id="main_product1"><!--串珠類型-->
						<br>
						<h2>★ Anycolor 本期主打 ★</h2>
					</div>
					<div>
						<a href="01.php" id="sea_page">
							<img src="images/whisper of the sea.png" alt="海的低語" id="page3">
						</a>
					</div>
				</section>	
				
				<section id="title_for_feature">
					<div>
						<p>*.꙳𝜗𝜚꙳⋆ 近期委託 ⋆꙳𝜗𝜚꙳.*</p>
					</div>
				</section>
			<!-- Features -->
				<section id="features">
					<div class="container">
						<div class="row aln-center">
							
							<div class="col-4 col-6-medium col-12-small">
								<!-- Feature -->
									<section>
										<a href="#" class="image featured"><img src="images/commision_img/1 (2).jpg" alt="" /></a>
									</section>

							</div>
							<div class="col-4 col-6-medium col-12-small">

								<!-- Feature -->
									<section>
										<a href="#" class="image featured"><img src="images/commision_img/12 (1).jpg" alt="" /></a>
									</section>

							</div>
							<div class="col-4 col-6-medium col-12-small">

								<!-- Feature -->
									<section>
										<a href="#" class="image featured"><img src="images/commision_img/11 (1).jpg" alt="" /></a>
									</section>
							</div>
							<div class="col-12">
								<ul class="actions">
									<li><a href="#" class="button icon solid fa-file">Tell Me More</a></li>
								</ul>
							</div>
							
						</div>
					</div>
				</section>

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

		</div>

		<!-- Scripts -->
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
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>