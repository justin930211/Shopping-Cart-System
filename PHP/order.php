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
	$member_data = mysqli_fetch_assoc($result_customer);

	if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){ //判斷登入
		echo '<script>alert("請先登入");
		window.location.href="account.php";</script>';
	}
	else{
		$userPhone = $_SESSION['user']; 
		$sql_state="SELECT status FROM `customer` WHERE phone='$userPhone'"; //抓使用者狀態
		
		$row=mysqli_query($conn,$sql_state);
		$row_state=mysqli_fetch_assoc($row);
		if ($row_state['status'] == "n") { //禁用直接登出
			echo '<script>alert("你已被禁用");</script>';
			require_once 'function.php';
			clearSession();
			echo '<script>window.location.replace("account.php");</script>';
    	exit;		
		}
	}
	if (isset($_POST["action_delete"]) && $_POST["action_delete"] == "cancel") { //使用者取消訂單
			$order_id = $_POST['order_delete'];
			$status = "已取消";
		
			$userPhone = $_SESSION['user']; 
			$sql_state="SELECT status FROM `customer` WHERE phone='$userPhone'"; //抓使用者的狀態
			
			$row=mysqli_query($conn,$sql_state);
			$row_state=mysqli_fetch_assoc($row);
			
			if ($row_state['status'] == "n") {   //被禁用強制登出
				echo '<script>alert("你已被禁用");</script>';
				require_once 'function.php';
				clearSession();
				echo '<script>window.location.replace("account.php");</script>';
				exit;		
			}
		
		// 檢查是否已取消
		$sql_check_status = "SELECT state FROM `order` WHERE `order_id` = ?";
		$stmt_check_status = $conn->prepare($sql_check_status);
		$stmt_check_status->bind_param("i", $order_id);
		$stmt_check_status->execute();
		$stmt_check_status->bind_result($order_status);
		$stmt_check_status->fetch();
		$stmt_check_status->close();
		
		if ($order_status !== "已取消" && $order_status !== "已結單" ) {//如果沒取消
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
			window.location.href="order.php";</script>';
		} else {
			if($order_status == "已取消"){
				echo '<script>alert("管理者已取消訂單，無法再次取消");</script>';
			}
			elseif($order_status == "已結單"){
				echo '<script>alert("管理者已送出訂單，無法取消");</script>';	
			}
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
              <?php echo "<a href='profile.php?id=".$member_data["phone"]."' class='nav-link'>會員資料</a>"; ?> 
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
	if (!isset($_SESSION['form_submitted'])) {   //登出
		if(isset($_POST['logout'])){
			require_once 'function.php';
			clearSession();
			echo '<script>window.location.replace("home.php");</script>';
    exit;		
		}
	}
?>	
<body>
	<section class="showcase-area">
		<div class="container" >
			<a href="home.php"><img src="img/return.png" style="position: absolute;left: 5%;width: 50px;height: auto; z-index: 10;"></a>
			<?php
	  		$UserPhone = $_SESSION['user']; 		
			$sql_cart = "SELECT d.order_id ,d.product_id, d.quality, p.product_name, p.image, p.price
             FROM details d
             JOIN product p ON d.product_id = p.product_id
             JOIN `order` o ON d.order_id = o.order_id
             WHERE o.phone = '$UserPhone' ORDER BY d.order_id ASC"; //抓訂單明細 商品資料庫 降冪排列
			$result_cart = mysqli_query($conn, $sql_cart);
			
			$sql_order= "SELECT * FROM `order` WHERE phone ='$UserPhone' ORDER BY order_id DESC;";
			$result_order = mysqli_query($conn, $sql_order);
			
	  if(mysqli_num_rows($result_cart) === 0){ //沒有任何資料
        	echo '<p style="margin-top:20%; font-size:25px; font-weight: bold;">目前沒有任何訂單喔。</p><br><br><br><br>
					<a href="home.php#product" style="font-size:25px; font-weight: bold;">前往添加商品</a>';
	  }
	  else{
			while($row_result_order = mysqli_fetch_assoc($result_order)){
				$id=$row_result_order['order_id'];
				$state=$row_result_order['state'];
				
				$sql_details= "SELECT * FROM `details` WHERE order_id ='$id';"; //連接訂單資料
				$result_details = mysqli_query($conn, $sql_details);
		?>

		<table width="550" border="0" cellpadding="0" cellspacing="0" style="position: relative; margin: auto;">
            <tbody>
            <tr>
                <td width="231"><h2>訂單編號：<span style="color: rgb(244, 140, 10)"><?php echo $id ?></span></h2></td>
                <td width="220" align="right"><h2>狀態：<span style="color: rgb(244, 140, 10)"><?php echo $state ?></span></h2></td>
				<td width="99" align="left" >
					<form action="" method="post">
						<?php	
						if($state=="未出貨"){ ?>	
							<input type="hidden" name="order_delete" value="<?php echo $row_result_order['order_id']; ?>">
							<input type="hidden" name="action_delete" value="cancel">
							<input type="submit" value="取消訂單" style="margin-left: 10px;">
							<?php } ?>
					</form>
				</td>
            </tr>
            <tr>
                <td colspan="3">
                    <table width="100%" border="1" cellpadding="0" cellspacing="0">
                        <tbody>
                        	<?php
								$total_amount=0;
								$previous_order_id = null; //給id空直用來後面判斷
                    			while ($row_result_details = mysqli_fetch_assoc($result_details)) {
                    			        $proid = $row_result_details['product_id'];
										$min_total=$row_result_details['quality']*$row_result_details['price'];	//算小記
											
                    			        $sql_product = "SELECT * FROM `product` WHERE product_id ='$proid';"; //抓商品
                    			        $result_product = mysqli_query($conn, $sql_product);
                    			        $row_result_product = mysqli_fetch_assoc($result_product);
                    		?>
								<tr>
                    	            <td width="315" height="35" align="left"><h3>商品名稱：<?php echo $row_result_product['product_name'] ?></h3></td>
                    	            <td align="right"><h3>商品數量：<?php echo $row_result_details['quality'] ?></h3></td>
                    	            <td align="right"><h3>小計：<?php echo $min_total ?></h3></td>
                    	        </tr>
							<?php 
									if($id!==$previous_order_id){	//檢查是否跟上一個訂單不同 			  
										if($previous_order_id !== null){ //顯示金額				
											echo '<tr>';
											echo '<td colspan="3" align="right">總金額：' . $total_amount . '</td>';
											echo '</tr>';
										}
										$total_amount=0;
										$previous_order_id=$id;
									}
										$total_amount += $min_total; //總金額
		  						}
				
			    			if ($previous_order_id !== null) {//判斷最後一筆資料
								echo '<tr>';
								echo '<td colspan="3" align="right"><h3>總金額：' . $total_amount . '</h3></td>';
								echo '</tr>';
    						}
						} ?>
                        </tbody>
                    </table>
              </td>
            </tr>
            </tbody>
        </table>	
			<?php }	?>
	  </div>
	</section>
		<script src="js/app.js"></script>
</body>
</html>