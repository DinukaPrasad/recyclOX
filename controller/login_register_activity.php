<?php
session_start();
require_once('../config/db_connection.php');

if (isset($_POST['btn_login'])) {
    // Login logic
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists in the Users table
    $result = $con->query("SELECT * FROM users WHERE email = '$email'");
    
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();

        //check user account status (active or suspended)
        if($user['status'] === 'active'){
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['first_name'];

                switch ($user['role']) {
                    case 'admin':
                        header("Location: ../admin_dashboard.php");
                        break;
                    case 'user':
                        header("Location: ../market.php");
                        break;
                    case 'staff':
                        header("Location: ../staff_dashboard.php");
                        break;
                    default:
                        header("Location: ../login_register.php");
                        break;
                }
                exit();
            } else {
                // Password is incorrect
                $_SESSION['login_error'] = 'Incorrect password!';
                $_SESSION['active_form'] = 'login';
            }
        }else{
            // Email not found
        $_SESSION['login_error'] = 'your account temporarily suspended!!';
        $_SESSION['active_form'] = 'login';
        }
    } else {
        // Email not found
        $_SESSION['login_error'] = 'Email not found!';
        $_SESSION['active_form'] = 'login';
    }

    // Redirect back to the login page if login fails
    header("Location: ../login_register.php");
    exit();
}

if (isset($_POST['btn_singin'])) {
    // Registration logic
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $role = 'user';

    // Check if the email is already registered
    $checkEmail = $con->query("SELECT email FROM Users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
    } else {
        // Insert the new user into the Users table
        $con->query("INSERT INTO Users (first_name, last_name, email, password, address, phone_number, role) 
                     VALUES ('$firstName', '$lastName', '$email', '$password', '$address', '$phone', '$role')");
        $_SESSION['register_success'] = 'Registration successful! Please login.';
        $_SESSION['active_form'] = 'login';
    }

    // Redirect back to the login page
    header("Location: ../login_register.php");
    exit();
}
?>