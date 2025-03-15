<?php
    require_once('../config/db_connection.php');

    if(isset($_GET['id'])){

        $id = $_GET['id'];

        $sql = "SELECT * FROM users WHERE user_id = $id";

        $result = $con->query($sql);

        if($result->num_rows > 0){
            $updateSql = "UPDATE users SET status = 'active' WHERE user_id = $id";
            
            if($con->query($updateSql)){
                header("Location: ../admin_dashboard.php");
                exit();
            } else {
                die("Error suspend user" . $con->error);
            }
        }else {
            die("user not found");
        }
    } else{
        die("Invalid request.");
    }
?>