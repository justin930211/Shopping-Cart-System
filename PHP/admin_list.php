<?php
session_start();

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
	<title>上傳商品</title>
	<link rel="stylesheet" href="css/admin_list2.css">
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
	<script>
		function checkFiles() {
			var file1 = document.getElementsByName("file1")[0].value; //取得值	
			var product = document.getElementsByName("product")[0];
			var description = document.getElementsByName("description")[0];
			var price = document.getElementsByName("price")[0];
			var quality = document.getElementsByName("quality")[0];

			var productcheck = product.value;
			var descriptioncheck = description.value;

			if (productcheck.length > 10) {
				alert("商品名稱請小於10個字");
				product.value = '';
				return false;
			}
			if (descriptioncheck.length > 30) {
				alert("商品描述請小於30個字");
				description.value = '';
				return false;
			}

			if (file1 === "") {
				alert("請選擇圖片");
				return false; // 禁止表單送出
			}
			if (product === "" || description === "" || price === "" || quality === "") {
				alert("請填寫商品完整資料");
				location.reload();
				return false; // 禁止表單送出
			}
			return true; // 允許表單送出
		}
	</script>
	<div class="container">
		<div class="s1">
			<form action="" id="form1" name="form1" method="post" enctype="multipart/form-data" onsubmit="return checkFiles()"><!--編碼方式-->
				<div class="file1">
					上傳圖片: <input type='file' name='file1' accept='imag/*' style="font-size:18px; border: none">
					<br><br>
					商品名稱: <input type="text" class="input" name="product"><br><br>
					商品描述: <input name="description" type="text" class="input"><br><br>
					價　　格: <input name="price" type="number" required="required" class="input" max="10000" min="1" step="1"><br><br>
					商品數量: <input name="quality" type="number" required="required" class="input" max="1000" min="1" step="1" value="1"><br><br>
				</div>
				<input type="submit" class="nav-link" value="送出" name="go" style="background: none; border: none; margin-left: 48%; font-size:23px;">
			</form>
		</div>
	</div>

	<?php
	$size_bytes = 1 * 1024 * 1024; //限制大小1mb
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
					exit;
				}
			}
		}

		if (isset($_POST['go'])) { //送出鍵
			$host = "localhost";
			$username = "root";
			$password = "";
			$database = "user";

			// 連接到資料庫
			$conn = mysqli_connect($host, $username, $password, $database);
			mysqli_set_charset($conn, "utf8");

			$product = $_POST['product'];
			$description = $_POST['description'];
			$price = $_POST['price'];
			$quality = $_POST['quality'];

			$query = "INSERT INTO product (product_name, quality,description,price,image) VALUES ('$product', '$quality','$description','$price','$path')";  //寫入資料
			mysqli_query($conn, $query);
			$mess = $_POST['product'] . "上傳成功";
			echo '<script>alert("' . $mess . '");
		</script>';
		}
	}
	?>
	<script type="text/javascript">
		// 使用 JavaScript 防止惡意刷新
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
</body>

</html>