<?php
		$host = "localhost";
		$username = "root";
		$password = "";
		$database = "user";

		// 連接到資料庫
		$conn = mysqli_connect($host, $username, $password, $database);     
        $passwordPattern="/^[a-zA-Z0-9]{8,20}$/";

    if(isset($_POST['password']) || isset($_POST['passwordrepeat'])){
        $password=$_POST['password'];
        $passwordrepeat = $_POST['passwordrepeat'];
        $phone = $_POST['phone'];

        if ($password !== '' && preg_match($passwordPattern, $password) && $password === $passwordrepeat && strlen($password) >= 8 && strlen($password) <= 20 && $password!==$phone) {
            $return_data = true;
        }
        else{
            $return_data = false;  
        }   
    }
echo json_encode($return_data);
?>