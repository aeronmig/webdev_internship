<?php
include('conn.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_address = trim($_POST['email_address']);
    $password = trim($_POST['password']);

    // Check in the admin table first
    $sql = "SELECT * FROM admin WHERE email_address=?";
    $connection = connection();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email_address);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password for admin
        if ($password == $row['password']) {
            $_SESSION['email_address'] = $email_address;
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin_home.php");
                exit;
            }
        } else {
            $message = "Invalid Password for Admin";
            echo "<script type='text/javascript'>alert('$message');</script>";
        }
    } else {
        // If not found in admin, check in internship_users
        $sql = "SELECT * FROM internship_users WHERE email_address=?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $email_address);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verify the password for internship_users
            if (password_verify($password, $row['password'])) {
                $_SESSION['email_address'] = $email_address;
                $_SESSION['id'] = $row['id'];
                $_SESSION['firstname'] = $row['firstname'];
                $_SESSION['role'] = 'user'; // Assuming the role for internship_users is 'user'
                header("Location: home_login.php");
                exit;
            } else {
                $message = "Invalid Password for User";
                echo "<script type='text/javascript'>alert('$message');</script>";
            }
        } else {
            $message = "Invalid Email Address";
            echo "<script type='text/javascript'>alert('$message');</script>";
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/homepage.css">
    <title> CCS Internship Tracker Login </title>
</head>
<body>
<header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1> CCS Student Internship Tracker Login. </h1>
    </div>
    <nav>
      <a href="index.php"> HOME </a>
      <a href="about.php"> ABOUT </a>
      <a href="login.php"> LOGIN </a>
      <a href="registration.php"> SIGNUP </a>
    </nav>
  </header>
  <div class="center-wrapper">
    <div class="login-form card">
      <form method="post" action="login.php">
        <div class="content">
          <div class="input-field">
            <label for="email_address"> Email Address: </label>
            <input type="email" name="email_address" placeholder="Enter Email Address" required>
          </div>
          <div class="input-field">
            <label for="password"> Password: </label>
            <input type="password" name="password" id="password" placeholder="Enter Password" required>
          </div>
          <div class="checkbox-field">
            <input type="checkbox" onclick="myFunction()"> Show Password
          </div>
          <div class="action">
            <button class="btn-signup" onclick="trying();">Register</button>
            <button class="btn-signin" type="submit">Sign in</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
<script>
    function trying() {
        window.location.href = "registration.php"
    }
    function myFunction() {
        var x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>
</body>
</html>
