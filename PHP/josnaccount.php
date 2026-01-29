<?php
		$host = "localhost";
		$username = "root";
		$password = "";
		$database = "user";

		// 連接到資料庫
		$conn = mysqli_connect($host, $username, $password, $database);     

        $phonePattern = "/^09\d{8}$/";
        $passwordPattern="/^[a-zA-Z0-9]{8,20}$/";

        if(isset($_POST["data"])){
            $phone = $_POST['data'];
    
            $query = "SELECT * FROM customer WHERE phone = '$phone'"; //會員電話
	        $result = mysqli_query($conn, $query); 
            
            if ($phone == '' || !preg_match($phonePattern, $phone)) {
                $return_data = false;
            }
            else{
                if(mysqli_num_rows($result) == 1){
                    $return_data=false;
                }
                else{
                    $return_data=true;
                }     
            }
        }

    if(isset($_POST['password']) || isset($_POST['passwordrepeat'])){
        $password=$_POST['password'];
        $phone = $_POST['phone'];
        $passwordrepeat = $_POST['passwordrepeat'];
    
        $query = "SELECT * FROM customer WHERE phone = '$phone'"; //會員電話
	    $result = mysqli_query($conn, $query); 
        
        if ($password !== '' && $password !== $phone && preg_match($passwordPattern, $password) &&        $password === $passwordrepeat && strlen($password) >= 8 && strlen($password) <= 20) {
            $return_data = true;
        }
        else{
            $return_data = false;  
        }   
    }
        
    /*if(isset($_POST['passwordRepeat']) && isset($_POST['password'])){
        $password=$_POST['password'];
        $passwordRepeat = $_POST['passwordRepeat'];
            
        if ($passwordRepeat == '' || $password!= $passwordRepeat) {
            $return_data = false;
        }
        else{
            $return_data = true;  
        }   
    }*/
echo json_encode($return_data);
?>