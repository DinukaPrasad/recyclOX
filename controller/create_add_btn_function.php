<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    
    header("Location: ../login_register.php");
    exit(); 
}else{
    header("Location: ../user_dashboard.php#my-listed-items");
    exit();
}

?>