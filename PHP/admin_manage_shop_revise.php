<?php
	session_start();
		$size_bytes=1*1024*1024;
if (!isset($_SESSION['form_submitted'])) {
	if (isset($_FILES["file1"])) {
		if ($_FILES["file1"]["error"] === UPLOAD_ERR_OK) {
			$count = 1;
			$allowedExtensions = array('jpg', 'png', 'gif'); // 允許的副檔名
			$fileName = ($_FILES["file1"]["name"]); // 取得檔名
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // 取得副檔名
			
			if (in_array($fileExtension, $allowedExtensions)) {
				if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $fileName)) { // 有中文的話
					echo '<script>alert("檔名不能包含中文！")
					</script>';
					exit;
				}
				
				$newfilename = strtolower(pathinfo($fileName, PATHINFO_FILENAME));
				while (file_exists("upload/" . $fileName)) { // 上傳後的不能再上傳
					$basefileName = $newfilename . "(" . $count . ")." . $fileExtension;
					$fileName = $basefileName;
					$count++;
				}
				
				if ($_FILES["file1"]["size"] > $size_bytes) { // 檔案大於1mb
					echo '<script>alert("檔案太大！")
					</script>';
					exit;
				} else {
					$path = "upload/" . $fileName;
					move_uploaded_file($_FILES["file1"]["tmp_name"], $path);
				}
			} else {
				echo '<script>alert("上傳的檔案只能是 png、jpg、gif ！")
				</script>';
			}
		}
	}
}

	$host = "localhost";
	$username = "root";
	$password = "";
	$database = "user";
	
	// 連接到資料庫
	$conn = mysqli_connect($host, $username, $password, $database);
	mysqli_set_charset($conn, "utf8");

	if(isset($_POST["action"])&&($_POST["action"]=="revise")){	 //修改鍵
		if(isset($fileName)){  //如果有改圖片的話
			$uploadPath = "upload/";  // 上傳路徑，請確保資料夾存在並具有適當權限
    		$fileName = $_FILES['file1']['name'];
    		$filePath = $uploadPath . $fileName;
			
			$sql_query = "UPDATE product SET product_name=?, quality=?, description=?, price=?, image=? WHERE product_id=?"; // 更改商品內容
			$stmt = $conn -> prepare($sql_query);
        	$stmt->bind_param("sssssi", $_POST["pname"], $_POST["pquality"], $_POST["pdescription"], $_POST["pprice"], $filePath, $_POST["revise"]);
			$stmt -> execute();
			$stmt -> close();
			$conn -> close();
			header("Location: admin_manage_shop.php");
		}
		else{ //沒有就不改圖
			$sql_query = "UPDATE product SET product_name=?, quality=?, description=?, price=? WHERE product_id=?";
			$stmt = $conn -> prepare($sql_query);
        	$stmt->bind_param("ssssi", $_POST["pname"], $_POST["pquality"], $_POST["pdescription"], $_POST["pprice"], $_POST["revise"]);
			$stmt -> execute();
			$stmt -> close();
			$conn -> close();
			header("Location: admin_manage_shop.php");
		}
	}
	$sql_select = "SELECT product_id, product_name, quality, description, price, status, image FROM product WHERE product_id = ?"; //抓商品資料
	$stmt = $conn -> prepare($sql_select);
	$stmt -> bind_param("i", $_GET["id"]); //前面a傳過來的id
	$stmt -> execute();
	$stmt -> bind_result($product_id, $product_name, $quality,$description, $price,$status, $image);
	$stmt -> fetch();
?>
<?php
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
	<script>
		function checkFiles() {
			var file1 = document.getElementsByName("file1")[0].value; //取得值	
			var product=document.getElementsByName("pname")[0];
			var description=document.getElementsByName("pdescription")[0];
			var price=document.getElementsByName("pprice")[0].value;
			var quality=document.getElementsByName("pquality")[0].value;
			
			var productcheck=product.value;
			var descriptionlen=description.value;
			
			if(productcheck.length>10){
			   	alert("商品名稱請小於10個字");
				product.value='';
				return false;
			}
				
			if(descriptionlen.length>30){
				alert("商品描述請勿超過30字");
				description.value='';
				return false; // 禁止表單送出
			}
			
			if (product === "" || description ==="" || price==="" || quality==="") {
				alert("請填寫商品完整資料");
				location.reload();
				return false; // 禁止表單送出
			}
			return true; // 允許表單送出
		}
	</script>
<html>
<head>
<meta charset="utf-8">
<title>修改商品</title>
	<link rel="stylesheet" href="css/admin_manage_shop_revise.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<style type="text/css">
		input{
			height: 30px;
			width: 300px;
			font-size: 20px;
		}
	</style>
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
			if(empty($product_id)) {
				echo'<script>alert("找不到產品");
				window.location.href="admin_manage_shop.php";</script>';
			}
			else{
		?>
		<form action="" method="post" enctype="multipart/form-data" onSubmit="return checkFiles()">
		<h2 style="font-size: 28px;">修改商品</h2>
			<table width="700" border="1" cellpadding="0" cellspacing="0" style="margin-top: 2%; font-size: 20px;">
			    <tbody>
					<tr>
						<th height="40"><h3>欄位</h3></th>
						<th><h3>資料</h3></th>
					</tr>
					<tr>
			        	<td height="40"><h3>商品名稱</h3></td>
						<td><input type="text" name="pname" value="<?php echo $product_name; ?>"></td>
			        </tr>
					<tr>
						<td height="200"><h3>商品圖片</h3></td>
						<td><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" width="200" height="auto">
							<br><input type='file' name='file1' accept='imag/*' style="font-size: 16px;">
						</td>
					</tr>
					<tr>
						<td height="40" ><h3>商品描述</h3></td>
						<td><input type="text" name="pdescription" value="<?php echo $description; ?>"></td></td>
					</tr>		
					<tr>
						<td height="40"><h3>商品價格</h3></td>
						<td><input type="text" name="pprice" value="<?php echo $price; ?>"></td>
					</tr>
					<tr>
						<td height="40"><h3>商品數量</h3></td>
						<td><input type="text" name="pquality" value="<?php echo $quality; ?>"></td>
					</tr>
						<tr>
							<td colspan="5" align="center" height="60px;">
			                        <input type="hidden" name="revise" value="<?php echo $product_id; ?>">
			                        <input type="hidden" name="action" value="revise">
			    					<input type="submit" name="submit_revise" class="nav-link" style="background: none; border:none;" value="確認修改這項商品">
							</td>
						</tr>
			    </tbody>
			</table>
		</form>
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