<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    
    header("Location: ../login_register.php");
    exit(); 
}else{
    header("Location: create_advertisement.php");
    exit();
}

?>