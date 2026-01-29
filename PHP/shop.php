<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
} else {
    $userPhone = $_SESSION['user'];
    $sql_state = "SELECT status FROM `customer` WHERE phone='$userPhone'"; //抓使用者的狀態

    $row = mysqli_query($conn, $sql_state);
    $row_state = mysqli_fetch_assoc($row);

    if ($row_state['status'] == "n") {   //被禁用強制登出
        echo '<script>alert("你已被禁用");</script>';
        require_once 'function.php';
        clearSession();
        echo '<script>window.location.replace("account.php");</script>';
        exit;
    }
}

if (!isset($_SESSION['form_submitted'])) {
    if (isset($_POST["action"]) && ($_POST["action"] == "shop")) { //加入購物車
        if ($row_state['status'] == "n") { //使用者狀態是禁用
            echo '<script>alert("你已被禁用");</script>';
            require_once 'function.php';
            clearSession();
            echo '<script>window.location.replace("account.php");</script>';
            exit;
        } else {
            $userPhone = $_SESSION['user'];
            $product_id = $_POST["product_id"];
            $quantity = $_POST["number"];

            $sql = "SELECT status FROM `product` WHERE product_id='$product_id'"; //抓商品狀態
            $row = mysqli_query($conn, $sql);
            $row_state = mysqli_fetch_assoc($row);

            if ($row_state['status'] === '下架') { //下架就結束
                echo '<script>alert("已被下架");window.location.replace("home.php#product");</script>';
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

            $sql_check_product = "SELECT price FROM product WHERE product_id=?"; //抓商品的庫存
            $stmt_check_product = $conn->prepare($sql_check_product);
            $stmt_check_product->bind_param("i", $product_id);
            $stmt_check_product->execute();
            $stmt_check_product->bind_result($available_price); //庫存數
            $stmt_check_product->fetch();
            $stmt_check_product->close();

            if ($available_quality >= $quantity) { //判斷庫存是否足夠				
                if ($existing_quantity != null) {   //購物車有的話 加數量
                    $new_quantity = $existing_quantity + $quantity;
                    $sql_update_cart = "UPDATE customer_cart SET quality=? WHERE phone=? AND product_id=?";
                    $stmt_update_cart = $conn->prepare($sql_update_cart);
                    $stmt_update_cart->bind_param("isi", $new_quantity, $userPhone, $product_id);
                    $stmt_update_cart->execute();
                    $stmt_update_cart->close();
                    echo '<script>alert("添加成功");</script>';
                } else {  //沒有的話寫入
                    $sql_insert_cart = "INSERT INTO customer_cart(phone,product_id,quality,price) VALUE (?,?,?,?)";
                    $stmt_insert_cart = $conn->prepare($sql_insert_cart);
                    $stmt_insert_cart->bind_param("siii", $userPhone, $product_id, $quantity, $available_price);
                    $stmt_insert_cart->execute();
                    $stmt_insert_cart->close();
                    echo '<script>alert("添加成功");</script>';
                }
            } else {
                echo '<script>alert("庫存不足");</script>';
            }
        }
    }
}
$sql_select = "SELECT product_id, product_name, quality, description, price, image,status FROM product WHERE product_id = ?"; //抓商品的資料	
$stmt = $conn->prepare($sql_select);
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$stmt->bind_result($product_id, $product_name, $quality, $description, $price,  $image, $status);
$stmt->fetch();
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
                    <a href="profile.php" class="nav-link">會員資料</a>
                </li>
                <li>
                    <?php
                    if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
                        // 已登入，顯示登出按鈕	
                        echo '<form method="post">
                            <div class="nav-link"><input type="submit" name="logout" value="登出" style="border:none; background: none; font-size: 18px; font-weight: bold; color: hsl(257,17%,63%);"></div>
                            </form>';
                    } else {
                        // 未登入，顯示登入連結
                        echo '<a href="account.php" class="nav-link">登入</a>';
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
    if (isset($_POST['logout'])) {
        require_once 'function.php';
        clearSession();
        echo '<script>window.location.replace("home.php");</script>';
        exit;
    }
}
?>

<body>
    <?php
    if ($status == '下架') {
        echo '<script>alert("找不到產品或產品已被下架");
                window.location.href="home.php#product";</script>';
    } else {
        if (empty($product_id)) { //改不存在的id話跳
            echo '<script>alert("找不到產品");
                window.location.href="home.php#product";</script>';
        } else {
    ?>
            <a href="home.php#product"><img src="img/return.png" style="position: absolute;left: 15%;top: 24%;width: 50px;height: auto; z-index: 10;"></a>
            <section class="showcase-area">
                <div class="container">
                    <table width="1000" border="0" cellpadding="0" cellspacing="0" style="margin-left: 10%; margin-top: 12%; font-size: 20px;  ">
                        <tbody>
                            <tr>
                                <td width="538" rowspan="5"><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" width="400" height="266"></td>
                                <td align="left">
                                    <h3><?php echo $product_name; ?></h3>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <p>描述：<?php echo $description; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <p>價格：<?php echo $price; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <p>剩餘數量：<?php echo $quality; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <?php
                                    if (isset($_SESSION['login']) && $_SESSION['login'] == true) { //如果登入財顯示加入購物車
                                        if ($quality == 0) { //商品不夠時顯示
                                            echo "商品補貨中";
                                        } else {
                                            echo '<form method="post">
                                        數量：<input type="number" min="1" value="1" required="required" name="number" max="' . $quality . '" style="width:70px; font-size: 18px; font-weight: bold;">
                                        <input type="hidden" name="action" value="shop">
                                        <input type="hidden" name="product_id" value="' . $product_id . '">
                                        <input type="submit" name="go" style="margin-left: 18.5%; padding: 5px; background: none; font-size: 18px; font-weight: bold; color: hsl(257,17%,63%);"value="加入購物車">
                                        </form>';
                                        }
                                    } else { //不然空
                                        echo '&nbsp;';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
    <?php }
    } ?>
    <script src="js/app.js"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>