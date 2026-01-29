<?php
	session_start();

	$host = "localhost";
	$username = "root";
	$password = "";
	$database = "user";
	
	// 連接到資料庫
	$conn = mysqli_connect($host, $username, $password, $database);
	mysqli_set_charset($conn, "utf8");

	if (isset($_POST['action']) && ($_POST['action'] == 'ban' || $_POST['action'] == 'unban')){ //上下架鍵
		$status = ($_POST['action'] == 'ban') ? '下架' : '上架'; //如果ban 就下架 不然就上架
		$sql_query = "UPDATE product SET status=? WHERE product_id=?"; //更改狀態
		$stmt = $conn -> prepare($sql_query);
		$stmt -> bind_param("si",$status, $_POST["delete"]);
		$stmt -> execute();
		
		header("Location: admin_manage_shop.php");
	}
	$sql_select = "SELECT product_id, product_name, quality, description, price, status, image FROM product WHERE product_id = ?"; //抓商品資料
	$stmt = $conn -> prepare($sql_select);
	$stmt -> bind_param("i", $_GET["id"]); //前面a傳過來的id
	$stmt -> execute();
	$stmt -> bind_result($product_id, $product_name, $quality,$description, $price,$status, $image);
	$stmt -> fetch();

	$name = "管理員";
	if (isset($_SESSION['user']) && $_SESSION['user'] !== $name) { //判斷是登入者是不是管理員
		echo'<script>alert("你的權限不能來這邊"); window.location.replace("home.php");</script>';
	}
	if (!isset($_SESSION['login']) || $_SESSION['user'] !== $name) { //未登入不能進到管理員頁面
		echo'<script>window.location.replace("home.php");alert("你的權限不能來這邊"); </script>';
		exit();
	}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>上下架商品</title>
	<link rel="stylesheet" href="css/admin_manage_shop_delete.css">
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
	<div class="container" >
		<?php
			if(empty($product_id)) { //改不存在的id跳
				echo'<script>alert("找不到產品");
				window.location.href="admin_manage_shop.php";</script>';
			}
			else{
		?>
		<h2 style="font-size: 28px;">上下架商品</h2>
			<table width="1125" border="1" cellpadding="0" cellspacing="0" style="margin-top: 2%; font-size: 20px;">
			    <tbody>
					<tr>
			        	<td width="150"><h3>商品名稱</h3></td>
			        	<td width="350"><h3>商品圖片</h3></td>
			        	<td width="250"><h3>商品描述</h3></td>
			        	<td width="125"><h3>商品價格</h3></td>
			        	<td width="125"><h3>商品數量</h3></td>
			        </tr>
			
			            <tr>
			                <td><h3><?php echo $product_name; ?></h3></td>
			                <td><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" width="200" height="auto"></td>
			                <td><p><?php echo $description; ?></p></td>
			                <td><p>價格：<?php echo $price; ?></p></td>
			                <td><p>數量：<?php echo $quality; ?></p></td>
			            </tr>
						<tr>
							<td colspan="5" align="center" height="60px;">
								<form action="" method="post">
			                        <input type="hidden" name="delete" value="<?php echo $product_id; ?>">				
									<?php if($status=="上架"){ ?>	
										<input type="hidden" name="action" value="ban">
										<input type="submit" value="確認下架這項商品" class="nav-link" style="background: none; border:none;">
										<?php } elseif($status=="下架"){ ?>	
										<input type="hidden" name="action" value="unban">
										<input type="submit" value="重新上架" class="nav-link" style="background: none; border:none;">
										<?php }?>
			                    </form>
							</td>
						</tr>
			    </tbody>
			</table>
		<?php }?>
  	</div>
</section>	
	<script src="js/app.js"></script>
</body>
</html>