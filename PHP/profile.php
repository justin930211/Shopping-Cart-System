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

	if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){ //沒有登入
		echo '<script>alert("請先登入");
		window.location.href="account.php";</script>';
		exit;
	}
	else{
		$userPhone = $_SESSION['user']; 
		$sql_state="SELECT status FROM `customer` WHERE phone='$userPhone'"; //抓使用者狀態
		
		$row=mysqli_query($conn,$sql_state);
		$row_state=mysqli_fetch_assoc($row);
		if ($row_state['status'] == "n") {  //使用者狀太禁用的話登出
			echo '<script>alert("你已被禁用");</script>'; 
			require_once 'function.php';
			clearSession();
			echo '<script>window.location.replace("account.php");</script>';
    	exit;		
		}
	}

	if (!isset($_SESSION['form_submitted'])) {
		if(isset($_POST["action"]) && $_POST["action"] == "revise") { //儲存按鈕
			if($_POST['newpassword']==""){   //如果沒有改密碼的話
				$sql_query = "UPDATE customer SET name=?, gender=?, gmail=? WHERE phone=?";
				$stmt = $conn->prepare($sql_query);
				$stmt->bind_param("sssi", $_POST["name"], $_POST["cSex"], $_POST["gmail"], $_GET["id"]);
				$stmt->execute();
				echo '<script>alert("儲存成功");</script>';
			}
			else{  //有輸入密碼驗過就改
				$sql_query = "UPDATE customer SET password=?, name=?, gender=?, gmail=? WHERE phone=?";
				$stmt = $conn->prepare($sql_query);
				$stmt->bind_param("ssssi", $_POST["newpassword"], $_POST["name"], $_POST["cSex"], $_POST["gmail"], $_GET["id"]);
				$stmt->execute();
				echo '<script>alert("儲存成功");</script>';
			}
		}
	}
	$sql_select = "SELECT phone, password, name, gender, gmail FROM customer WHERE phone = ?"; //抓是哪一個使用者
	$stmt = $conn->prepare($sql_select);
	$stmt->bind_param("i", $_GET["id"]);
	$stmt->execute();
	$stmt->bind_result($phone, $password, $name1, $gender, $gmail);	//綁定
	$stmt->fetch();//提取
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>嘟嘟賀</title>
		<link rel="stylesheet" href="css/profile1.css">
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
				<?php 
   					echo "<a href='profile.php?id=$userPhone' class='nav-link'>會員資料</a>";
 				?>
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
				if (isset($_SESSION['login']) && $_SESSION['login'] == true && $_SESSION['user'] === $_GET['id']) {//判斷登入者是誰
					echo '<div class="nav-link">' . $_SESSION['user'] . '</div>';
				}
				else{ //$_SESSION['user'] !== $_GET['id']跳轉到他自己的葉面
					echo '<script>
        			window.location.href="profile.php?id='.$_SESSION['user'].'";</script>';
				}
				?>
			</li>
          </ul>
        </div>
      </nav>
    </header>	
	<script>
		function checkFiles() {
			var gmailInput=document.getElementsByName("gmail")[0];	//抓gmail值
			var gmail= gmailInput.value;
			var emailPattern = /@gmail\.com$/; //限制gmail格式
			
        	var passwordInput = document.getElementsByName("newpassword")[0]; //抓newpassword值
        	var checkInput = document.getElementsByName("checkpassword")[0];  //抓checkpassword值
	
        	var password = passwordInput.value;
        	var check = checkInput.value;
			var chinesePattern = /[\u4E00-\u9FA5]/;
			var passwordPattern=/^[a-zA-Z0-9]+$/;

			if(gmail!==""){ 	//不為空的化	
				if (!emailPattern.test(gmail)) {
            		alert("必須使用 Gmail 格式");
            		gmailInput.value = ''; // 清空輸入框值
            		return false;
        		}
			}
			
			if(password!==""){
				if(chinesePattern.test(password)){
					alert("新密碼不能打中文喔");
					passwordInput.value='';
					checkInput.value='';
        			return false; // 禁止表單送出 
				}
				if(!passwordPattern.test(password)){
					alert("新密碼只能是英文跟數字");
					passwordInput.value='';
					checkInput.value='';
        			return false; // 禁止表單送出 
				}
				
				if (password.length < 8 || password.length > 20) {
        			alert("新密碼長度必須在8到20個字之間");
					passwordInput.value='';
					checkInput.value='';
        			return false; // 禁止表單送出
    			}		
				
				if (password!==check) {
					alert("新密碼跟確認密碼不相符");
					passwordInput.value='';
					checkInput.value='';

					return false; // 禁止表單送出
				}
			}
			return true;
		}
	</script>
<?php
	if (!isset($_SESSION['form_submitted'])) {  //登出
		if(isset($_POST['logout'])){
			require_once 'function.php';
			clearSession();
			echo '<script>window.location.replace("home.php");</script>';
    		exit;		
		}
	}
?>
	<body>
		<a href="home.php"><img src="img/return.png" style="position: absolute;left: 15%;top: 20%; width: 50px;height: auto; z-index: 10;"></a>
			<div class="card signup" id="signup">
			<h2 ><i class="fas fa-user"></i> 我的檔案</h2>
				<form action="" method="post" onsubmit="return checkFiles()" >
				  <div class="form-group" style="margin-right: 105px;">
					<label for="phone">Phone：</label>
					  <label for="" style="height:30px; margin-top: 10px;"><?php echo $phone; ?></label>
					</div>
				  <div class="form-group" style="margin-right: 105px;">
					<label for="password">Password：</label>
					  <label for="" style="height:30px; margin-top: 10px;"><?php echo $password; ?></label>
					</div>				  
					<div class="form-group">
					<label for="password">NEW Password：</label>
						<input type="password" name="newpassword"  placeholder="請輸入新密碼"  style="height:30px; margin-top: 10px; padding:10px;">	
						<input type="password" name="checkpassword" placeholder="確認新密碼"  style="height:30px; margin-top: 10px; padding:10px;">	
					</div>
					<div class="form-group">
						<label for="name">Name：</label>
						<input type="text" name="name" value="<?php echo $name1; ?>" style="height:30px; margin-top: 10px; padding:10px;">	
					</div>
					<div class="form-group">
						<label for="name">Gender：</label>
						<table width="180" border="0" cellpadding="0">
						  <tbody>
						    <tr>
						      <td><input type="radio" name="cSex" id="radioM" value="M" <?php if($gender=="M") echo "checked";?> style="margin-top: 10px; padding:10px; "> 男</td>
							  <td><input type="radio" name="cSex" id="radioF" value="F" <?php if($gender=="F") echo "checked";?> style="margin-top: 10px; padding:10px; "> 女</td>
						    </tr>
						  </tbody>
						</table>				
					</div>
					<div class="form-group">
						<label for="gamil">Gamil：</label>
						<input type="text" name="gmail" value="<?php echo $gmail; ?>" style="height:30px; margin-top: 10px; padding:10px;">
					</div>
					<input type="hidden" name="revise" value="<?php echo $phone; ?>">
			        <input type="hidden" name="action" value="revise">
			    	<input type="submit" name="submit_revise" class="nav-link" style="background: none; border:none;" value="儲存">
				</form>
			</div>
	</body>
</html>
	<script type="text/javascript">
    // 使用 JavaScript 防止惡意刷新
    	if (window.history.replaceState) {
    	    window.history.replaceState(null, null, window.location.href);
    	}
	</script>