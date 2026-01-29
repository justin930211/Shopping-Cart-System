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
	<title>會員管理</title>
	<link rel="stylesheet" href="css/admin_manage_customer1.css">
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
			$sql = "SELECT * FROM `customer`;"; //連接顧客資料庫
			$result_manage = mysqli_query($conn, $sql);
			$total_records = $result_manage->num_rows; //有幾筆資料

			if (mysqli_num_rows($result_manage) === 0) {
				echo '<p style="font-size:25px; font-weight: bold;">目前商城中沒有任何會員。</p>';
			} else {
			?>
				<h2 style="font-size: 28px;">會員管理</h2><br>
				<h3>共<?php echo $total_records ?>位會員</h3>
				<table width="1125" border="1" cellpadding="0" cellspacing="0" style="margin-top: 2%; font-size: 20px;">
					<tbody>
						<tr>
							<td width="150" height="50">
								<h3>手機號碼</h3>
							</td>
							<td width="250">
								<h3>密碼</h3>
							</td>
							<td width="125">
								<h3>姓名</h3>
							</td>
							<td width="350">
								<h3>Gmail</h3>
							</td>
							<td width="125">
								<h3>性別</h3>
							</td>
							<td width="125">
								<h3>功能</h3>
							</td>
						</tr>
						<?php
						while ($row_result = mysqli_fetch_assoc($result_manage)) {
						?>
							<tr>
								<td height="35">
									<p><?php echo $row_result['phone']; ?></p>
								</td>
								<td>
									<p><?php echo $row_result['password']; ?></p>
								</td>
								<td>
									<p><?php echo empty($row_result['name']) ? '未填寫' : $row_result['name']; ?></p>
								</td>
								<td>
									<p><?php echo empty($row_result['gmail']) ? '未填寫' : $row_result['gmail']; ?></p>
								</td>
								<td>
									<p><?php
										if (empty($row_result['gender'])) {
											echo "未填寫";
										} else if ($row_result['gender'] == "F") {
											echo "女";
										} else {
											echo "男";
										}
										?></p>
								</td>
								<td>
									<form action="" method="post">
										<input type="hidden" name="phone" value="<?php echo $row_result['phone']; ?>">
										<?php if ($row_result['status'] == "y") { ?>
											<input type="hidden" name="action" value="ban">
											<input type="submit" value="封鎖">
										<?php } elseif ($row_result['status'] == "n") { ?>
											<input type="hidden" name="action" value="unban">
											<input type="submit" value="解封">
										<?php } ?>
									</form>
								</td>
							</tr>
					<?php }
						if (isset($_POST['action']) && ($_POST['action'] == 'ban' || $_POST['action'] == 'unban')) {
							$status = ($_POST['action'] == 'ban') ? 'n' : 'y'; // 根據 action 確定要設定的狀態值
							$sql_query = "UPDATE customer SET status=? WHERE phone=?";
							$stmt = $conn->prepare($sql_query);
							$stmt->bind_param("si", $status, $_POST["phone"]);
							$stmt->execute();
							echo '<script>
					alert("更改成功");
					window.location.href="admin_manage_customer.php";
					</script>';
						}
					}
					?>
					</tbody>
				</table>
		</div>
	</section>
	<script src="js/app.js"></script>
	<script>
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
</body>

</html>