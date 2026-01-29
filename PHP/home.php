<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

$sql = "SELECT * FROM `customer`;";   //抓使用者給會員資料
$result_customer = mysqli_query($conn, $sql);
$member_data = mysqli_fetch_assoc($result_customer);

if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
} else {
	$userPhone = $_SESSION['user'];
	$sql_state = "SELECT status FROM `customer` WHERE phone='$userPhone'";  //使用者狀態

	$row = mysqli_query($conn, $sql_state);
	$row_state = mysqli_fetch_assoc($row);

	if ($row_state['status'] == "n") { //被禁用強制登出
		echo '<script>alert("你已被禁用");</script>';
		require_once 'function.php';
		clearSession();
		echo '<script>window.location.replace("account.php");</script>';
		exit;
	}
}
?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>嘟嘟賀</title>
	<link rel="stylesheet" type="text/css" href="css/index.css">
	<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
	<style type="text/css">
		.portfolio-header {
			display: grid;
			margin-bottom: 2rem;
			grid-template-columns: 80% 20%;
		}
	</style>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>

<body>
	<header>
		<nav class="container">
			<a href="home.php" class="logo"><img src="img/都.png" class="logopic" alt="" style="width: 249px; height: 80px;"></a>
			<div class="links">
				<ul>
					<li>
						<a href="home.php" class="nav-link">首頁</a>
					</li>
					<li>
						<a href="order.php" class="nav-link">我的訂單</a>
					</li>
					<li>
						<a href="cart.php" class="nav-link">購物車</a>
					</li>
					<li>
						<?php
						echo "<a href='profile.php?id=" . $member_data["phone"] . "' class='nav-link'>會員資料</a>"; //抓是誰登入進到他的會員資料
						?>
					</li>
					<li>
						<?php
						$name = "管理員";
						if (isset($_SESSION['user']) && $_SESSION['user'] == $name) {
							header("Location: admin.php");
						}
						if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
							// 已登入，顯示登出按鈕	
							echo '<form method="post">
              <div class="nav-link"><input type="submit" name="logout" value="登出" style="border:none; background: none; font-size: 18px; font-weight: bold; color: hsl(257,17%,63%);"></div>
              </form>';
						} else {
							// 未登入，顯示登入連結
							echo '<a href="account.php" class="nav-link">登入</a>';
						}
						?>
					</li>
					<li>
						<?php
						if (isset($_SESSION['login']) && $_SESSION['login'] == true) { // 有登入顯示使用者帳號
							echo '<div class="nav-link">' . $_SESSION['user'] . '</div>';
						}
						?>
					</li>
				</ul>
			</div>
		</nav>
	</header>
	<?php
	if (!isset($_SESSION['form_submitted'])) {   //登出
		if (isset($_POST['logout'])) {
			require_once 'function.php'; //呼叫
			clearSession(); //清空
			echo '<script>window.location.replace("home.php");</script>';
			exit;
		}
	}
	?>

	<main>
		<section class="showcase-area" id="Home">
			<div class="container">
				<div class="showcase-info" style="padding-left: 12%;">
					<h3 class="sub-hedding">Sincerely</h3>
					<h1 class="heading">Welcome to My Shop</h1>
					<div class="cta">
						<a href="#product" class="btn secondary-btn">開始購物</a>
						<img src="img/square1.png" class="shape" />
					</div>
				</div>
				<div class="showcase-image">
					<img src="img/CBG.png" style="position: absolute;width:135%;bottom: 83px;left: 16%;" alt="Markus" />
					<img src="img/circle1.png" class="circle" alt="" />
					<img src="img/dots.png" class="dots" alt="" />
				</div>
			</div>
		</section>

		<section class="portfolio section" id="product">
			<div class="container">
				<div class="portfolio-header">
					<div class="porfolio-title" style="margin-left: 7%;">
						<h3 class="sub-hedding">Introduction</h3>
						<h1 class="heading">Product</h1>
					</div>
					<div class="porfolio-search" style="margin-left: 7%;">
						<form action="" method="post">
							搜尋：　<input type="text" id="input_text" list="keylist">
							<datalist id="keylist" list="keylist"></datalist>
						</form>
					</div>
				</div>
				<div class="porfolio-gallery">
					<?php
					$sql = "SELECT * FROM `product`;";  //抓產品
					$result_product = mysqli_query($conn, $sql);

					if (mysqli_num_rows($result_product) !== 0) {
						$products_per_row = 3; // 每行顯示的產品數量
						$count = 0; // 計數器

						while ($row_result = mysqli_fetch_assoc($result_product)) {
							// 在每行的第一個產品前添加<tr>
							if ($row_result['status'] === '下架') { //如果產品下架跳過他
								continue;
							}
							if ($count % $products_per_row === 0) { //用餘數判斷一行三個
								echo '<tr>';
							}
					?>
							<td width="350">
								<div class="prt-card">
									<div class="prt-image">
										<img src="<?php echo $row_result['image']; ?>" alt="<?php echo $row_result['product_name']; ?>" style="max-width: 100%; max-height: 100%;">
										<div class="prt-overlay">
											<span class="prt-iocn" style="--i:0.15s">
												<?php echo "<a href='shop.php?id=" . $row_result["product_id"] . "'>" ?><i class="uil uil-link-h"></i></a>
											</span>
										</div>
									</div>
									<div class="prt-desc">
										<h3><?php echo $row_result['product_name']; ?></h3>
										<span class="btn secondary-btn sm"><?php echo "<a href='shop.php?id=" . $row_result["product_id"] . "'>Read more</a>"; ?> </span>
									</div>
								</div>
							</td>
					<?php
							// 在每行的最後一個產品後添加</tr>
							if (($count + 1) % $products_per_row === 0 || $count ===    mysqli_num_rows($result_product) - 1) { //判斷加一後是否能整除或者等於最後一個資料
								echo '</tr>';
							}
							$count++;
						}
					}
					?>
				</div>
			</div>
		</section>
	</main>
	<script>
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
	<script src="js/app.js"></script>
</body>

</html>

<script>
	var originalProductList = $('.porfolio-gallery').html();

	$('#input_text').on("keyup", function() {
		var target_data = $(this).val(); //input裡的東西

		if (target_data === '') {
			$('.porfolio-gallery').html(originalProductList);
		} else {
			$.ajax({
				url: 'josnproduct.php', //哪裡
				type: 'POST', //方式
				dataType: 'json', //用什麼方式
				data: {
					'data': target_data
				}
			}).done(function(data) {
				if (data) {
					// 假設您有一個用於顯示產品的元素
					$('.porfolio-gallery').html(data.html); // 將返回的 HTML 內容插入
				} else {
					// 清空或顯示沒有找到產品的信息
					$('.porfolio-gallery').html('<p>No products found.</p>');
				}
			}).fail(function(error) {
				console.log("Error:", error.responseText);
				console.log("Status:", error.status);
				console.log("ErrorThrown:", error.statusText);
			})
		}
	});

	$('#input_text').on("keyup", function() {
		var target_data = $(this).val(); //input裡的東西
		var datalist;
		var option;
		if (target_data !== '') {
			$.ajax({
				url: 'josnsearch.php', //哪裡
				type: 'POST', //方式
				dataType: 'json', //用什麼方式
				data: {
					'data': target_data
				}
			}).done(function(data) {
				if (data) {
					// 假設您有一個用於顯示產品的元素
					datalist = $('#keylist');
					datalist.empty();

					data.keylist.forEach(function(keyword) {
						option = $('<option>').val(keyword);
						datalist.append(option);
					})
				}
			}).fail(function(error) {
				console.log("Error:", error.responseText);
				console.log("Status:", error.status);
				console.log("ErrorThrown:", error.statusText);
			})
		} else {
			datalist.empty();
		}
	});
</script>