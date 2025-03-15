<?php
    session_start();

    $errors = [
        'login' => $_SESSION['login_error'] ?? '',
        'register' => $_SESSION['register_error'] ?? ''
    ];

    $activeForm = $_SESSION['active_form'] ?? 'login';

    session_unset();

    function showError($error){
        return !empty($error) ? "<p class='error-msg'> $error</p>" : '';
    }

    function isActiveForm($formName, $activeForm){
        return $formName === $activeForm ? 'active' : '';
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="./asset/css/login_page.css">
</head>
<body>
    <div class="container">
        <div class="form_box <?= isActiveForm('login', $activeForm); ?>" id="login_form">
            <form action="./controller/login_register_activity.php" method="post">
                <h2>LOGIN</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="btn_login">LOGIN</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register_form')">Register</a></p>
            </form>
        </div>

        <div class="form_box <?= isActiveForm('register', $activeForm); ?>" id="register_form">
            <form action="./controller/login_register_activity.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="firstName" placeholder="First Name" required>
                <input type="text" name="lastName" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="tel" name="phone" placeholder="Phone" required>
                <button type="submit" name="btn_singin">SIGN IN</button>
                <p>Already have an account? <a href="#" onclick="showForm('login_form')">Login</a></p>
            </form>
        </div>
    </div>
    <script src="./asset/js/login.js"></script>
</body>
</html>