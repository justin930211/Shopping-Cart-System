<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);
mysqli_set_charset($conn, "utf8");
?>
<?php
$name = "管理員";
if (isset($_SESSION['user']) && $_SESSION['user'] !== $name) { //判斷是登入者是不是管理員
	echo '<script>alert("你的權限不能來這邊"); window.location.replace("home.php");</script>';
}
if (!isset($_SESSION['login']) || $_SESSION['user'] !== $name) { //未登入不能進到管理員頁面
	echo '<script>window.location.replace("home.php");alert("你的權限不能來這邊"); </script>';
	exit();
}
?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>管理商品</title>
	<link rel="stylesheet" href="css/admin_manage_shop.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<header>
	<nav class="container">
		<a href="home.php" class="logo"><img src="img/都.png" class="logopic" alt="" style="width: 249px; height: 80px;"></a>
		<div class="links">
			<ul>
				<li>
					<a href="admin_list.php" class="nav-link">上傳商品</a>
				</li>
				<li>
					<a href="admin_manage_shop.php" class="nav-link">管理商品</a>
				</li>
				<li>
					<a href="admin_manage_customer.php" class="nav-link">會員管理</a>
				</li>
				<li>
					<a href="admin_manage_order.php" class="nav-link">訂單管理</a>
				</li>
				<li>
					<a href="admin.php" class="nav-link">回到首頁</a>
				</li>
			</ul>
		</div>
	</nav>
</header>

<body>
	<section class="showcase-area">
		<div class="container">
			<?php
			$sql = "SELECT * FROM `product`;"; //抓商品
			$result_product = mysqli_query($conn, $sql);
			$total_records = $result_product->num_rows; //商品數量

			if (mysqli_num_rows($result_product) === 0) {
				echo '<p style="font-size:25px; font-weight: bold; bold;">目前商城中沒有任何產品。</p><br><br><br><br><br><br><br><br><br><br>	
		<a href="admin_list.php" style="font-size:25px; font-weight: bold;">前往添加商品</a>';
			} else {
			?>
				<h2 style="font-size: 28px;">管理商品</h2>
				<br>
				<h3>共<?php echo $total_records ?>個產品</h3>
				<table width="1125" border="1" cellpadding="0" cellspacing="0" style="margin-top: 2%; font-size: 20px;">
					<tbody>
						<tr>
							<td width="150">
								<h3>商品名稱</h3>
							</td>
							<td width="350">
								<h3>商品圖片</h3>
							</td>
							<td width="250">
								<h3>商品描述</h3>
							</td>
							<td width="125">
								<h3>商品價格</h3>
							</td>
							<td width="125">
								<h3>商品數量</h3>
							</td>
							<td width="125">
								<h3>功能</h3>
							</td>
						</tr>
						<?php
						while ($row_result = mysqli_fetch_assoc($result_product)) {
							$status = $row_result['status'];
						?>
							<tr>
								<td>
									<h3><?php echo $row_result['product_name']; ?></h3>
								</td>
								<td><img src="<?php echo $row_result['image']; ?>" alt="<?php echo $row_result['product_name']; ?>" width="200" height="auto"></td>
								<td>
									<p><?php echo $row_result['description']; ?></p>
								</td>
								<td>
									<p>價格：<?php echo $row_result['price']; ?></p>
								</td>
								<td>
									<p>數量：<?php echo $row_result['quality']; ?></p>
								</td>
								<td><?php echo "<a href='admin_manage_shop_revise.php?id=" . $row_result["product_id"] . "'>修改</a>"; ?> | <?php
																																		if ($status == "上架") {
																																			echo "<a href='admin_manage_shop_delete.php?id=" . $row_result["product_id"] . "'>下架</a>"; ?></td>
							<?php } elseif ($status == "下架") {
																																			echo "<a href='admin_manage_shop_delete.php?id=" . $row_result["product_id"] . "'>上架</a>"; ?></td>
							<?php } ?>
							</tr>
					<?php
						}
					} ?>
					</tbody>
				</table>
		</div>
		<script>
			if (window.history.replaceState) {
				window.history.replaceState(null, null, window.location.href);
			}
		</script>
	</section>
	<script src="js/app.js"></script>
</body>

</html>