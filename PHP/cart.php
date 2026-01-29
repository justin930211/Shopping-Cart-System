<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

$sql = "SELECT * FROM `customer`;";
$result_customer = mysqli_query($conn, $sql);
$member_data = mysqli_fetch_assoc($result_customer); //給會員資料

$insufficientInventoryProducts = []; // 用於儲存庫存不足的商品
$can = true;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) { //判斷登入
	echo '<script>alert("請先登入");
		window.location.href="account.php";</script>';
} else {
	$userPhone = $_SESSION['user'];
	$sql_state = "SELECT status FROM `customer` WHERE phone='$userPhone'"; //抓使用者狀態

	$row = mysqli_query($conn, $sql_state);
	$row_state = mysqli_fetch_assoc($row);


	if ($row_state["status"] == "n") { //禁用登出
		echo '<script>alert("你已被禁用");</script>';
		require_once 'function.php';
		clearSession();
		echo '<script>window.location.replace("account.php");</script>';
		exit;
	}

	if (!isset($_SESSION['form_submitted'])) {
		if (isset($_POST["action"]) && ($_POST["action"] == "delete")) {	//刪除鍵
			$delete_product_id = $_POST["delete"]; //刪除的id

			$sql_query = "DELETE FROM customer_cart WHERE product_id=?";  //刪除購物車的東西
			$stmt = $conn->prepare($sql_query);
			$stmt->bind_param("i", $_POST["delete"]);
			$stmt->execute();
			echo '<script>alert("刪除成功");
		window.location.href="cart.php";</script>';
		}

		if (isset($_POST["action"]) && ($_POST["action"] == "shop")) { //修改鍵
			$quantity = $_POST["number"]; //修改後的數量
			$product_id = $_POST["product_id"];

			$sql = "SELECT product_name,status FROM `product` WHERE product_id='$product_id'"; //抓商品的名字狀態
			$row = mysqli_query($conn, $sql);
			$row_state = mysqli_fetch_assoc($row);

			if ($row_state['status'] === '下架') { //下架的話跳出
				echo '<script>alert("' . $row_state['product_name'] . '已被下架");window.location.replace("cart.php");</script>';
				exit();
			}

			$sql_check_cart = "SELECT quality FROM customer_cart WHERE phone=? AND product_id=?"; //抓購物車的數量 看使用者是誰 商品編號
			$stmt_check_cart = $conn->prepare($sql_check_cart);
			$stmt_check_cart->bind_param("si", $userPhone, $product_id);
			$stmt_check_cart->execute();
			$stmt_check_cart->bind_result($existing_quantity); //訂購數
			$stmt_check_cart->fetch();
			$stmt_check_cart->close();

			$sql_check_product = "SELECT quality FROM product WHERE product_id=?"; //抓商品的庫存
			$stmt_check_product = $conn->prepare($sql_check_product);
			$stmt_check_product->bind_param("i", $product_id);
			$stmt_check_product->execute();
			$stmt_check_product->bind_result($available_quality); //庫存數
			$stmt_check_product->fetch();
			$stmt_check_product->close();

			if ($available_quality >= $quantity) {
				if ($existing_quantity != null) {  //購物車有的話 加數量  
					$sql_update_cart = "UPDATE customer_cart SET quality=? WHERE phone=? AND product_id=?";
					$stmt_update_cart = $conn->prepare($sql_update_cart);
					$stmt_update_cart->bind_param("isi", $quantity, $userPhone, $product_id);
					$stmt_update_cart->execute();
					$stmt_update_cart->close();
					echo '<script>alert("' . $row_state['product_name'] . '修改成功");</script>';
				}
			} else {
				echo '<script>alert("庫存不足,剩餘庫存' . $available_quality . '");</script>';
			}
		}

		if (isset($_POST["action"]) && ($_POST["action"] == "submit_order")) { //訂單送出鍵
			if ($row_state['status'] == "n") {
				echo '<script>alert("你已被禁用");</script>';
				require_once 'function.php';
				clearSession();
				echo '<script>window.location.replace("account.php");</script>';
				exit;
			} else {
				$cartQuery = "SELECT c.product_id, p.product_name, p.price, c.quality, p.status, p.quality as available_quantity,c.price as older_price
            FROM customer_cart c
            JOIN product p ON c.product_id = p.product_id
            WHERE c.phone = '$userPhone'";
				$cartResult = mysqli_query($conn, $cartQuery);

				while ($row_cart = mysqli_fetch_assoc($cartResult)) {
					$productStatus = $row_cart['status'];
					$availableQuantity = $row_cart['available_quantity'];
					$cartQuantity = $row_cart['quality'];
					$productName = $row_cart['product_name'];

					if ($productStatus === '下架') {
						$upload_mess[] = [
							'product_name' => $productName
						];
						$can = false; // 商品已经下架，不允许結帳
					}

					// 检查商品庫存是否足够
					if ($cartQuantity > $availableQuantity) {
						$quality_mess[] = [
							'available_quality' => $availableQuantity,
							'product_name' => $productName
						];
						$can = false; // 不允许結帳
					}

					if ($row_cart['older_price'] !== $row_cart['price']) {
						$price_mess[] = [
							'product_name' => $productName,
							'available_price' => $row_cart['price']
						];
						$sql_update_product = "UPDATE customer_cart SET price = {$row_cart['price']} WHERE product_id = {$row_cart['product_id']}";
						mysqli_query($conn, $sql_update_product);
						$can = false;
					}
				}

				if ($can) {
					// 创建订单
					$address = $_POST['address'];
					$insertOrderQuery = "INSERT INTO `order` (phone, address) VALUES ('$userPhone', '$address')";
					$result = mysqli_query($conn, $insertOrderQuery);

					if ($result) {
						$orderID = mysqli_insert_id($conn);
						$cartQuery = "SELECT * FROM customer_cart WHERE phone= '$userPhone'";
						$cartResult = mysqli_query($conn, $cartQuery);

						while ($cartRow = mysqli_fetch_assoc($cartResult)) {
							$productID = $cartRow['product_id'];
							$quantity = $cartRow['quality']; //購物車數量

							// 獲取商品資料
							$productNameQuery = "SELECT product_name FROM product WHERE product_id = $productID";
							$productNameResult = mysqli_query($conn, $productNameQuery);
							$productName = mysqli_fetch_assoc($productNameResult)['product_name'];

							$productPriceQuery = "SELECT price FROM product WHERE product_id = $productID";
							$productPriceResult = mysqli_query($conn, $productPriceQuery);
							$productPrice = mysqli_fetch_assoc($productPriceResult)['price'];

							$productQualityQuery = "SELECT quality FROM product WHERE product_id = $productID";
							$productQualityResult = mysqli_query($conn, $productQualityQuery);
							$productQuality = mysqli_fetch_assoc($productQualityResult)['quality'];

							$new_product_quality = $productQuality - $quantity;
							$sql_update_product = "UPDATE product SET quality = {$new_product_quality} WHERE product_id = $productID";
							mysqli_query($conn, $sql_update_product);

							// 執行結帳操作
							$insertOrderItemQuery = "INSERT INTO details (order_id, product_id, quality, price) VALUES ($orderID, $productID, $quantity, $productPrice)";
							mysqli_query($conn, $insertOrderItemQuery);
						}

						// 如果結帳成功就清空購物車
						$clearCart = "DELETE FROM customer_cart WHERE phone = '$userPhone'";
						mysqli_query($conn, $clearCart);

						echo '<script>alert("訂單送出成功");</script>';
					}
				} else {
					if (!empty($upload_mess)) {  //下架提示
						foreach ($upload_mess as $product) {
							echo '<script>alert("訂單送出失敗,商品 ' . $product['product_name'] . ' 已下架");</script>';
						}
						echo '<script>window.location.replace("cart.php");</script>';
					} elseif (!empty($price_mess)) { //價格變動
						foreach ($price_mess as $product) {
							echo '<script>alert("訂單送出失敗,商品 ' . $product['product_name'] . ' 的價格有變動，價格為：' . $product['available_price'] . '");</script>';
						}
						echo '<script>window.location.replace("cart.php");</script>';
					} elseif (!empty($quality_mess)) { //庫存不足
						foreach ($quality_mess as $product) {
							echo '<script>alert("訂單送出失敗,商品 ' . $product['product_name'] . ' 的庫存不足，剩餘庫存：' . $product['available_quality'] . '");</script>';
						}
						echo '<script>window.location.replace("cart.php");</script>';
					}
				}
			}
		}
	}
	$sql_cart = "SELECT c.product_id, c.quality, p.price, p.status ,p.product_name
			FROM customer_cart c JOIN product p ON c.product_id = p.product_id WHERE c.phone = '$userPhone'";  //抓是誰連接購物車跟商品資料庫
	$result_cart = mysqli_query($conn, $sql_cart);
	$upload_messange = array();
	while ($row_result = mysqli_fetch_assoc($result_cart)) {
		if ($row_result['status'] == "下架") {
			$proname = $row_result['product_name'];
			$upload_messange[] = array(
				'product_name' => $proname
			);

			$product_id = $row_result['product_id'];
			$sql_remove_product = "DELETE FROM `customer_cart` WHERE product_id=?";
			$stmt_remove_product = $conn->prepare($sql_remove_product);
			$stmt_remove_product->bind_param("i", $product_id);
			$stmt_remove_product->execute();
			$stmt_remove_product->close();
			echo '<script>window.location.replace("cart.php");</script>';
			return;
		}
	}
	if (!empty($upload_messange)) {  //下架提示
		foreach ($upload_messange as $product) {
			echo '<script>alert("商品 ' . $product['product_name'] . ' 已下架");</script>';
		}
		echo '<script>window.location.replace("cart.php");</script>';
	}
}

?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>嘟嘟賀</title>
	<link rel="stylesheet" href="css/shop.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
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
					<?php echo "<a href='profile.php?id=" . $member_data["phone"] . "' class='nav-link'>會員資料</a>"; ?>
				</li>
				<li>
					<?php
					if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
						// 已登入，顯示登出按鈕	
						echo '<form method="post">
                			<div class="nav-link"><input type="submit" name="logout" value="登出" style="border:none; background: none; font-size: 18px; font-weight: bold; color: hsl(257,17%,63%);"></div>
              			  </form>';
					}
					?>
				</li>
				<li>
					<?php
					if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
						echo '<div class="nav-link">' . $_SESSION['user'] . '</div>';
					}
					?>
				</li>
			</ul>
		</div>
	</nav>
</header>
<?php
function clearSession()
{
	session_unset();
	session_destroy();
}

if (!isset($_SESSION['form_submitted'])) {   //登出
	if (isset($_POST['logout'])) {
		clearSession();
		echo '<script>window.location.replace("home.php");</script>';
		exit;
	}
}
?>
<script>
	function check() {
		var addressinput = document.getElementsByName("address")[0];
		var address = addressinput.value;
		var pattern = /([\u4e00-\u9fa5]*)市([\u4e00-\u9fa5]*)區([\u4e00-\u9fa5]*)路([0-9]*)號([1-9]*)樓/; //地址格式

		if (address == "") {
			alert("要輸入地址喔");

			return false;
		}
		if (!pattern.test(address)) {
			alert("地址格式不正確");

			return false;
		}
		return true;
	}
</script>

<body>
	<section class="showcase-area">
		<div class="container">
			<a href="home.php"><img src="img/return.png" style="position: absolute;left: 5%;width: 50px;height: auto; z-index: 10;"></a>
			<?php
			$UserPhone = $_SESSION['user'];
			$sql_cart = "SELECT c.product_id, c.quality, p.product_name, p.image , p.price, p.status
             FROM customer_cart c JOIN product p ON c.product_id = p.product_id WHERE c.phone = '$UserPhone'"; //抓誰的購物車有哪些東西
			$result_cart = mysqli_query($conn, $sql_cart);

			if (mysqli_num_rows($result_cart) ===	0) {
				echo '<p style="margin-top:20%; font-size:25px; font-weight: bold;">目前沒有任何商品喔。</p><br><br><br><br>
					<a href="home.php#product" style="font-size:25px; font-weight: bold;">前往添加商品</a>';
			} else {
			?>
				<table width="1000" border="1" cellpadding="0" cellspacing="1" style="font-size:20px;">
					<tbody>
						<tr>
							<td width="150" align="center">
								<h3>商品名稱</h3>
							</td>
							<td width="350" align="center">
								<h3>商品圖片</h3>
							</td>
							<td width="125" align="center">
								<h3>商品數量</h3>
							</td>
							<td width="125" align="center">
								<h3>金額</h3>
							</td>
							<td width="125" align="center">
								<h3>功能</h3>
							</td>
						</tr>
						<?php
						$alltotal = 0;
						while ($row_result = mysqli_fetch_assoc($result_cart)) {
							if ($row_result['status'] === "下架") {
								continue;
							}
							$total = $toatl = $row_result['quality'] * $row_result['price']; //小記
							$alltotal += $total; //總金額
						?>
							<tr>
								<td align="center">
									<h3><?php echo $row_result['product_name']; ?></h3>
								</td>
								<td align="center"><img src="<?php echo $row_result['image']; ?>" alt="<?php echo $row_result['product_name']; ?>" width="200" height="auto"></td>
								<td align="center">
									<form method="post">
										<input type="number" min="1" required="required" name="number" style="width:70px; font-size: 18px; font-weight: bold;" value="<?php echo $row_result['quality']; ?>">
										<input type="hidden" name="action" value="shop">
										<input type="hidden" name="product_id" value="<?php echo $row_result['product_id']; ?>"><br><br>
										<input type="submit" name="go" class="nav-link" style="background: none; border:none;" value="修改">
									</form>
								</td>
								<td align="center">
									<p>金額：<?php echo $total; ?></p>
								</td>
								<td align="center">
									<form action="" method="post">
										<input type="hidden" name="delete" value="<?php echo $row_result['product_id']; ?>">
										<input type="hidden" name="action" value="delete">
										<input type="submit" name="submit_revise" class="nav-link" style="background: none; border:none;" value="刪除">
									</form>
								</td>
							</tr>
						<?php
						}
						?>
						<tr>
							<td height="80" colspan="5" align="center">
								<?php
								echo '總金額：' . $alltotal;
								?>
								<form action="" method="post" onSubmit="return check()">
									住址：<input type="text" name="address">
									<input type="hidden" name="action" value="submit_order">
									<input type="submit" name="submit_order" value="提交訂單">
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			<?php }
			?>
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