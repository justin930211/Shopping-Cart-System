<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);

$product_name = $_POST['data'];
$keywords = [];

if (isset($_POST["data"])) {
    $query = "SELECT * FROM product WHERE product_name LIKE '%$product_name%'";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $keywords[] = $row['product_name'];
    }
    echo json_encode(['success' => true, 'keylist' => $keywords]);
}
