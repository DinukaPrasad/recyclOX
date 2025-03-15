<?php
    require_once('../config/db_connection.php');

    if(isset($_GET['id'])){
        $id = $_GET['id'];

        $sql = "SELECT * FROM advertisements WHERE ad_id = $id";
        $result = $con->query($sql);

        if($result->num_rows > 0){
            $ad = $result->fetch_assoc();

            $updateSql = "UPDATE advertisements SET status = 'rejected' WHERE ad_id = $id";
            
            if($con->query($updateSql)){
                header("Location: ../admin_dashboard.php");
                exit();
            } else {
                die("Error accepting ads" . $con->error);
            }
        } else {
            die("Advertisement not found");
        }
    } else {
        die("Invalid request.");
    }
?>