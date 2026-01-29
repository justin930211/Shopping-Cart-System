<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);

if (isset($_POST["data"])) {
    $phone = $_POST['data'];
    $query = "SELECT * FROM customer WHERE phone = '$phone'";
    $result = mysqli_query($conn, $query);

    $board_query = "SELECT * FROM board WHERE phone = '$phone'";
    $board_result = mysqli_query($conn, $board_query);


    if (mysqli_num_rows($result) == 1 || mysqli_num_rows($board_result) == 1) {
        $return_data = true;
    } else {
        $return_data = false;
    }

    echo json_encode($return_data);
}
