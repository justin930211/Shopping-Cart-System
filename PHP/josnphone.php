<?php     
    if(isset($_POST['password']) || isset($_POST['phone'])){
        $password=$_POST['password'];
        $phone = $_POST['phone'];
        
        if ($password!==$phone) {
            $return_data = true;
        }
        else{
            $return_data = false;  
        }   
    }
echo json_encode($return_data);
?>
