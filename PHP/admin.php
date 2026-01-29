<?php
	session_start();

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
<title>嘟嘟賀管理頁面</title>
	  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="css/admin.css"/>
    <script src="https://unpkg.com/scrollreveal"></script>
	    <link
      rel="stylesheet"
      href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
    />
</head>
<header>
      <nav class="container">
        <a href="admin.php" class="logo"><img src="img/都.png" class="logopic" alt="" style="width: 249px; height: 80px;"></a>
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
				<?php
        			if (isset($_SESSION['login']) && $_SESSION['login'] == true) { //判斷到登入
        			echo '<form method="post">
                			<div class="nav-link"><input type="submit" name="logout" value="登出" style="border:none; background: none; font-size: 18px; font-weight: bold; color: hsl(257,17%,63%);"></div>
              			  </form>';		
        			} else {
        			    // 未登入，顯示登入和註冊連結
        			    echo '<a href="account.php" class="nav-link">登入</a>';
        			    echo '<a href="account.php" class="nav-link">註冊</a>';
        			}
        		?>
			</li> 
			<li>
			  <?php
				if (isset($_SESSION['login']) && $_SESSION['login'] == true) { //顯示登入者
					echo '<div class="nav-link">' . $_SESSION['user'] . '</div>';
				}
				?>
			 </li>
          </ul>
        </div>
      </nav>
    </header>
<body>
	<main>
	<section>
	    <div class="container">
          <div class="porfolio-gallery" style="margin-top: 9%;">
            <div class="prt-card">
              <div class="prt-image">
                <img src="img/upload.png" alt="">
                <div class="prt-overlay">
                  <a href="admin_list.php" class="prt-iocn" style="--i:0.15s">
                    <i class="uil uil-link-h"></i>
                  </a>
                </div>
              </div>
              <div class="prt-desc">
                <h3>上傳商品</h3>
              </div>
            </div>
            <div class="prt-card">
              <div class="prt-image">
                <img src="img/store.png" alt="">
                <div class="prt-overlay">
                  <a href="admin_manage_shop.php" class="prt-iocn" style="--i:0.15s">
                    <i class="uil uil-link-h"></i>
                  </a>
                </div>
              </div>
              <div class="prt-desc">
                <h3>商品管理</h3>
              </div>
            </div>
            <div class="prt-card">
              <div class="prt-image">
                <img src="img/skills.png" alt="">
                <div class="prt-overlay">
                  <a href="admin_manage_customer.php" class="prt-iocn" style="--i:0.15s">
                    <i class="uil uil-link-h"></i>
                  </a>
                </div>
              </div>
              <div class="prt-desc">
                <h3>會員管理</h3>
              </div>
            </div>
            <div class="prt-card">
              <div class="prt-image">
                <img src="img/order-fulfillment.png" alt="">
                <div class="prt-overlay">
                  <a href="admin_manage_order.php" class="prt-iocn" style="--i:0.15s">
                    <i class="uil uil-link-h"></i>
                  </a>
                </div>
              </div>
              <div class="prt-desc">
                <h3>訂單管理</h3>
              </div>
            </div>
          </div>
        </div>
      </section>
	</main>
<script src="js/app.js"></script>
      <script> 	  
	 if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
  </script>
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
</body>
</html>