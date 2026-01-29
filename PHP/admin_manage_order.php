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
	<title>嘟嘟賀</title>
	<link rel="stylesheet" href="css/admin_manage_order.css">
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
			$sql_cart = "SELECT d.order_id, d.product_id, d.quality, p.product_name
             		FROM details d
             		JOIN product p ON d.product_id = p.product_id
             		JOIN `order` o ON d.order_id = o.order_id
             		ORDER BY d.order_id DESC";  //抓商品編號 訂單編號到明細
			$result_cart = mysqli_query($conn, $sql_cart);
			$previous_order_id = null; //判斷order_id用

			while ($row_result = mysqli_fetch_assoc($result_cart)) {
				$order_id = $row_result['order_id'];
				$quality = $row_result['quality'];
				$product_name = $row_result['product_name'];

				if ($order_id !== $previous_order_id) {  //不等於空的話
					$sql_order = "SELECT * FROM `order` WHERE order_id = '$order_id'"; //抓訂單資料
					$result_order = mysqli_query($conn, $sql_order);
					$row_result_order = mysqli_fetch_assoc($result_order);

					$id = $row_result_order['order_id'];
					$state = $row_result_order['state'];
					$address = $row_result_order['address'];
					$phone = $row_result_order['phone'];

					$sql_details = "SELECT * FROM `details` WHERE order_id ='$id';"; //抓明細資料
					$result_details = mysqli_query($conn, $sql_details);

					$previous_order_id = $order_id; //把id存到裡面
			?>

					<table width="700" border="0" cellpadding="0" cellspacing="0" style="position: relative; margin: auto;">
						<tbody>
							<tr>
								<td width="400" align="left">
									<h2 style="margin-left: 23px;">訂單編號：<span style="color: rgb(244, 140, 10)"><?php echo $id ?></span></h2>
								</td>
								<td width="100">
									<h2>狀態：</h2>
								</td>
								<td align="left">
									<form action="" method="post">
										<?php if ($state == "未出貨") { ?>
											<input type="hidden" name="order_id" value="<?php echo $row_result['order_id']; ?>">
											<input type="hidden" name="action" value="go">
											<input type="submit" value="出貨">
										<?php } elseif ($state == "已結單") { ?>
											<h2 style="color: rgb(244, 140, 10)">已結單</h2>
								<td width="85">&nbsp;</td>
							<?php } ?>
							</form>
							</td>
							<td align="left">
								<form action="" method="post">
									<?php
									if ($state == "未出貨") { ?>
										<input type="hidden" name="order_delete" value="<?php echo $row_result['order_id']; ?>">
										<input type="hidden" name="action_delete" value="cancel">
										<input type="submit" value="取消訂單" style="margin-left: 10px;">
									<?php } elseif ($state == "已取消") { ?>
										<h2 style="color: rgb(244, 140, 10)">已取消</h2>
							<td>&nbsp;</td>
						<?php } ?>
						</form>
						</td>
							</tr>
							<td align="left">
								<h2 style="margin-left: 23px;">買家帳號：<?php echo $phone ?></h2>
							</td>
							<td colspan="3" align="left">
								<h3>地址：<?php echo $address ?></h3>
							</td><br>
							<tr>
								<td colspan="4">
									<table width="650px" border="1" cellpadding="0" cellspacing="0" style="position: relative; margin: auto;">
										<tbody>
											<?php
											while ($row_result_details = mysqli_fetch_assoc($result_details)) {
												$proid = $row_result_details['product_id'];

												$sql_product = "SELECT * FROM `product` WHERE product_id ='$proid';"; //抓商品資料
												$result_product = mysqli_query($conn, $sql_product);
												$row_result_product = mysqli_fetch_assoc($result_product);
											?>
												<tr>
													<td width="300" height="35" align="left">
														<h3 style="margin-left: 50px;">商品名稱：<?php echo $row_result_product['product_name'] ?></h3>
													</td>
													<td width="150">
														<h3>商品數量：<?php echo $quality ?></h3>
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
			<?php
				}
			}

			if (isset($_POST["action"]) && $_POST["action"] == "go") { //出貨鍵
				$order_id = $_POST['order_id'];
				$status = "已結單";

				$sql_order = "SELECT * FROM `order` WHERE order_id = '$order_id'"; //抓訂單資料
				$result_order = mysqli_query($conn, $sql_order);
				$row_result_order = mysqli_fetch_assoc($result_order);
				$phone1 = $row_result_order['phone'];

				// 檢查是否已取消
				$sql_check_status = "SELECT state FROM `order` WHERE `order_id` = ?";
				$stmt_check_status = $conn->prepare($sql_check_status);
				$stmt_check_status->bind_param("i", $order_id);
				$stmt_check_status->execute();
				$stmt_check_status->bind_result($order_status);
				$stmt_check_status->fetch();
				$stmt_check_status->close();

				$sql_check_status = "SELECT status FROM `customer` WHERE `phone` = ?";
				$stmt_check_status = $conn->prepare($sql_check_status);
				$stmt_check_status->bind_param("i", $phone1);
				$stmt_check_status->execute();
				$stmt_check_status->bind_result($phone_state);
				$stmt_check_status->fetch();
				$stmt_check_status->close();

				if ($phone_state == "n") {
					echo '<script>alert("使用者已被禁用，無法出貨");</script>';
				} else {
					if ($order_status !== "已取消") { //如果沒取消  
						$sql_query = "UPDATE `order` SET `state` = ? WHERE `order_id` = ?";
						$stmt = $conn->prepare($sql_query);
						$stmt->bind_param("si", $status, $order_id);
						$stmt->execute();

						echo '<script>alert("出貨成功");
    				    window.location.href="admin_manage_order.php";</script>';
					} else {
						echo '<script>alert("使用者已取消訂單，無法出貨");</script>';
					}
				}
			}

			if (isset($_POST["action_delete"]) && $_POST["action_delete"] == "cancel") { //取消鍵
				$order_id = $_POST['order_delete'];
				$status = "已取消";

				// 檢查是否已取消
				$sql_check_status = "SELECT state FROM `order` WHERE `order_id` = ?";
				$stmt_check_status = $conn->prepare($sql_check_status);
				$stmt_check_status->bind_param("i", $order_id);
				$stmt_check_status->execute();
				$stmt_check_status->bind_result($order_status); //存放
				$stmt_check_status->fetch();
				$stmt_check_status->close();

				if ($order_status !== "已取消") { //如果沒取消
					//更新狀態
					$sql_query = "UPDATE `order` SET `state` = ? WHERE `order_id` = ?";
					$stmt = $conn->prepare($sql_query);
					$stmt->bind_param("si", $status, $order_id);
					$stmt->execute();

					// 抓庫存
					$sql_cart_quality = "SELECT d.product_id, d.quality, p.quality as product_quality
					FROM details d JOIN product p ON d.product_id = p.product_id WHERE d.order_id = ?";
					$stmt_cart_quality = $conn->prepare($sql_cart_quality);
					$stmt_cart_quality->bind_param("i", $order_id);
					$stmt_cart_quality->execute();
					$result_cart_quality = $stmt_cart_quality->get_result();

					//加回庫存
					while ($row_result_quality = mysqli_fetch_assoc($result_cart_quality)) {
						$product_id = $row_result_quality['product_id'];
						$quality_order = $row_result_quality['quality'];
						$product_quality = $row_result_quality['product_quality'];

						$new_quality = $product_quality + $quality_order;

						$sql_new_quality = "UPDATE `product` SET `quality`=? WHERE `product_id`=?";
						$stmt_update_product = $conn->prepare($sql_new_quality);
						$stmt_update_product->bind_param("ii", $new_quality, $product_id);
						$stmt_update_product->execute();
					}

					echo '<script>alert("訂單取消成功");
					window.location.href="admin_manage_order.php";</script>';
				} else {
					// 用户已取消订单，不执行更新操作
					echo '<script>alert("使用者已取消訂單，無法再次取消");</script>';
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