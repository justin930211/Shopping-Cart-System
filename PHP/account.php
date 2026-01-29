<?php
session_start();

if (!isset($_SESSION['form_submitted'])) {
	$host = "localhost";
	$username = "root";
	$password = "";
	$database = "user";

	// 連接到資料庫
	$conn = mysqli_connect($host, $username, $password, $database);

	if (isset($_POST['register_button'])) {
		$phone = $_POST['phone1'];
		$password = $_POST['password1'];

		// 檢查使用者名稱是否已存在
		$query = "SELECT * FROM customer WHERE phone = '$phone'";
		$result = mysqli_query($conn, $query);

		if (mysqli_num_rows($result) == 0) {
			// 使用者名稱可用，將資料插入資料庫
			$query = "INSERT INTO customer (phone, password) VALUES ('$phone', '$password')";
			mysqli_query($conn, $query);

			echo '<script>alert("註冊成功！");
			</script>';
		} else {
			// 使用者名稱已存在，顯示錯誤訊息
			echo '<script>alert("使用者已存在！");
			</script>';
		}
	}

	if (isset($_POST['login'])) {
		if (isset($_SESSION['user'])) {
			$mess = '已有帳號登入 您無法再登入';
			echo '<script>alert("' . $mess . '");
				window.location.href="home.php";
				</script>';
		} else {
			$phone = $_POST['phone'];
			$password = $_POST['password'];

			// 檢查使用者名稱和密碼是否匹配
			$query = "SELECT * FROM customer WHERE phone = '$phone' AND password = '$password'";
			$result = mysqli_query($conn, $query);
			$board_query = "SELECT * FROM board WHERE phone = '$phone' AND password = '$password'";
			$board_result = mysqli_query($conn, $board_query);

			if (mysqli_num_rows($board_result) == 1) {
				$_SESSION['login'] = true;
				$name = "管理員";
				$_SESSION['user'] = $name;
				echo '<script>alert("管理員登入成功！");
						window.location.href="admin.php";
						</script>';
			} else {
				if (mysqli_num_rows($result) == 1) {
					// 登入成功，設定 session 變數並轉向到登入後的頁面
					$_SESSION['login'] = true;
					$_SESSION['user'] = $phone;
					echo '<script>alert("登入成功！");
						window.location.href="home.php";
						</script>';
				} else {
					// 登入失敗，顯示錯誤訊息
					echo '<script>alert("使用者名稱或密碼錯誤！");
						</script>';
				}
			}
		}
	}
}
?>

<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>嘟嘟賀</title>
	<link rel="stylesheet" href="css/account2.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
	<style type="text/css">
		.Yes {
			border: #3f0 5px solid;
			color: #3f0;
		}

		.No {
			border: #f00 5px solid;
			color: #f00;
		}
	</style>
</head>

<body>
	<header>
		<nav class="container">
			<a href="home.php" class="logo"><img src="img/都.png" class="logopic" alt="" style="width: 249px; height: 80px;"></a>
			<div class="links">
				<ul>
					<li>
						<a href="home.php" class="nav-link">回到首頁</a>
					</li>
				</ul>
			</div>
		</nav>
	</header>
	<script>
		function checkFiles() {
			var phoneInput = document.getElementsByName("phone1")[0]; // 取得輸入框元素
			var passwordInput = document.getElementsByName("password1")[0];
			var checkInput = document.getElementsByName("password-repeat")[0];

			var phone = phoneInput.value; // 取得值
			var password = passwordInput.value;
			var check = checkInput.value;

			var chinesePattern = /[\u4E00-\u9FA5]/; //中文
			var phonePattern = /^09\d{8}$/; //電話 09加8碼
			var english = /[\u0041-\u007a]/; //英文
			var passwordPattern = /^[a-zA-Z0-9]+$/;

			if (chinesePattern.test(phone) || chinesePattern.test(password) || chinesePattern.test(check)) {
				alert("不可輸入中文喔！");
				phoneInput.value = '';
				passwordInput.value = '';
				checkInput.value = '';
				return false; // 禁止表單送出
			}

			if (english.test(phone)) {
				alert("電話不可輸入英文喔！");
				phoneInput.value = '';
				return false; // 禁止表單送出
			}

			if (phone == "" || password == "" || check == "") {
				alert("所有格子都要輸入喔");
				phoneInput.value = '';
				passwordInput.value = '';
				checkInput.value = '';
				return false; // 禁止表單送出
			}

			if (phone.length != 10) {
				alert("電話要10個數字喔");
				phoneInput.value = '';
				return false; // 禁止表單送出
			}

			if (!phonePattern.test(phone)) {
				alert("電話號碼格式錯誤，前兩個數字必須是09喔");
				phoneInput.value = '';
				return false;
			}

			if (password.length < 8 || password.length > 20) {
				alert("密碼長度必須在8到20個字之間");
				passwordInput.value = '';
				checkInput.value = '';
				return false; // 禁止表單送出
			}

			if (phone == password) {
				alert("電話跟密碼不得相符");
				phoneInput.value = '';
				passwordInput.value = '';
				checkInput.value = '';
				return false; // 禁止表單送出
			}

			if (!passwordPattern.test(password)) {
				alert("密碼只能有英文跟數字");
				passwordInput.value = '';
				checkInput.value = '';
				return false; // 禁止表單送出
			}

			if (password !== check) {
				alert("確認密碼跟密碼不相符");
				checkInput.value = '';
				return false; // 禁止表單送出
			}
			return true; // 允許表單送出
		}

		function checkFileslogin() {
			var phoneInput = document.getElementsByName("phone")[0]; //取得值
			var passwordInput = document.getElementsByName("password")[0];

			var phone = phoneInput.value;
			var password = passwordInput.value;

			var chinesePattern = /[\u4E00-\u9FA5]/; //鎖定中文

			if (chinesePattern.test(phone) || chinesePattern.test(password)) {
				alert("不可輸入中文喔！");
				phoneInput.value = '';
				passwordInput.value = '';
				return false; // 禁止表單送出
			}

			if (phone == "" || password == "") {
				alert("所有格子都要輸入喔");
				phoneInput.value = '';
				passwordInput.value = '';
				return false; // 禁止表單送出
			}
			return true; // 允許表單送出
		}
	</script>

	<div class="card signup" id="signup">
		<h2 style="transform: rotate(-4deg);"><i class="fas fa-user"></i> 立即註冊</h2>
		<form action="" method="post" onsubmit="return checkFiles()" style="transform: rotate(-4deg);">
			<div class="form-group">
				<label for="phone">Phone：</label>
				<input type="text" name="phone1" id="input_phone">
			</div>
			<div class="form-group">
				<label for="password">Password：</label>
				<input name="password1" type="password" required="required" id="input_password">
			</div>
			<div class="form-group">
				<label for="password-repeat">Password repeat：</label>
				<input name="password-repeat" type="password" id="input_password-repeat">
			</div>
			<button type="submit" name="register_button">Create</button>
		</form>
		<p class="link-signup" style="transform: rotate(-4deg);">
			已經擁有購物帳號嗎？
			<span class="switchText" onClick="switchToLogin()">Sign in!</span>
		</p>
	</div>

	<div class="card login" id="login">
		<h2 style="transform: rotate(4deg);"><i class="fas fa-key"></i> 歡迎登入</h2>
		<form action="" method="post" onsubmit="return checkFileslogin()" style="transform: rotate(4deg);">
			<div class="form-group">
				<label for="phone">Phone：</label>
				<input type="text" name="phone" id="input_text">
			</div>
			<div class="form-group">
				<label for="password">Password：</label>
				<input type="password" name="password">
			</div>
			<button type="submit" name="login">Sign In</button>
		</form>
		<p class="link-login" style="transform: rotate(4deg);">
			沒有購物帳號？
			<span class="switchText" onClick="switchToSignUp()">Sign up!</span>
		</p>
	</div>
	<script src="js/login.js"></script>
	<script type="text/javascript">
		// 使用 JavaScript 防止惡意刷新
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
</body>

</html>

<script>
	$('#input_text').on("keyup", function() {
		var target_data = $(this).val(); //input裡的東西

		$.ajax({
			url: 'JudgeProgram.php', //哪裡
			type: 'POST', //方式
			dataType: 'json', //用什麼方式
			data: {
				'data': target_data
			}
		}).done(function(data) {
			if (data) {
				$('#input_text').removeClass("No").addClass("Yes");
			} else {
				$('#input_text').removeClass("Yes").addClass("No");
			}
		})
	})

	$('#input_phone').on("keyup", function() {
		var phone = $(this).val(); //input裡的東西

		$.ajax({
			url: 'josnaccount.php', //哪裡
			type: 'POST', //方式
			dataType: 'json', //用什麼方式
			data: {
				'data': phone
			}
		}).done(function(data) {
			if (data) {
				$('#input_phone').removeClass("No").addClass("Yes");
			} else {
				$('#input_phone').removeClass("Yes").addClass("No");
			}
		})
	})

	$('#input_password,#input_password-repeat').on("keyup", function() {
		var password = $('#input_password').val(); //input裡的東西
		var passwordrepeat = $('#input_password-repeat').val();
		var phone = $('#input_phone').val();

		if (password !== "" || passwordrepeat !== "") {
			$.ajax({
				url: 'josnpassword.php', //哪裡
				type: 'POST', //方式
				dataType: 'json', //用什麼方式
				data: {
					'password': password,
					'passwordrepeat': passwordrepeat,
					'phone': phone
				}
			}).done(function(data) {
				if (data) {
					$('#input_password,#input_password-repeat').removeClass("No").addClass("Yes");
				} else {
					$('#input_password,#input_password-repeat').removeClass("Yes").addClass("No");
				}
			})
		}
	})

	$('#input_password,#input_phone').on("keyup", function() {
		var password = $('#input_password').val(); //input裡的東西
		var phone = $('#input_phone').val();

		if (password !== "" && phone !== "") {
			$.ajax({
				url: 'josnphone.php', //哪裡
				type: 'POST', //方式
				dataType: 'json', //用什麼方式
				data: {
					'password': password,
					'phone': phone
				}
			}).done(function(data) {
				if (data) {
					$('#input_password').removeClass("No").addClass("Yes");
				} else {
					$('#input_password').removeClass("Yes").addClass("No");
				}
			})
		}
	})
</script>