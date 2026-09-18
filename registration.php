<?php
include('conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_address = $_POST['email_address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $surname = $_POST['surname'];
    $school = $_POST['school'];
    $start_date = $_POST['start_date'];
    $company = $_POST['company'];
    $department = $_POST['department'];
    $hours_required = $_POST['hours_required'];


    $connection = connection();

    $check = $connection->prepare("SELECT id FROM internship_users WHERE firstname = ? or middlename = ? or surname = ?");
    $check->bind_param("sss", $firstname, $middlename, $surname);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>
            alert('Sorry, this person is already registered.');
            window.location.href = 'registration.php';
        </script>";
        exit;
    }
    function generateToken() {
      return bin2hex(random_bytes(16));
  }
  $token = generateToken();
    $stmt = $connection->prepare("INSERT INTO internship_users (email_address, password, role, firstname, middlename, surname, school, start_date, company, department, hours_required, feedback_token) VALUES (?, ?, 'user', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sssssssssss", $email_address, $password, $firstname, $middlename, $surname, $school, $start_date, $company, $department, $hours_required, $token);
        
        
        if ($stmt->execute()) {
           
            echo "<script>alert('Account created successfully! Redirecting to the login page...'); window.location.href='login.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

       
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $connection->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/registration.css">
    <title>CCS Internship Tracker Sign Up</title>
</head>
<body>

<header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1> CCS Student Internship Tracker Signup. </h1>
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
      <form method="post" action="registration.php">
       
            <div class="input-field">
            <label for="email_address"> Email Address: </label>
                <input type="email" name="email_address" id="email_address" placeholder="Enter Email Address" required>
            </div>
            <div class="input-field">
            <label for="password"> Password: </label>
                <input type="password" name="password" id="password" placeholder="Enter Password" required>
            </div>  
            <div class="checkbox-field">
            <input type="checkbox" id="showPassword" onclick="togglePassword()"> Show Password
            </div>
           <div class="input-field">
           <label for="surname"> Surname: </label>
                <input type="text" name="surname" id="surname" placeholder="Enter Surname" required>
            </div> 
            <div class="input-field">
            <label for="firstname"> Firstname: </label>
                <input type="text" name="firstname" id="firstname" placeholder="Enter Firstname" required>
            </div>
            <div class="input-field">
            <label for="middlename"> Middlename: </label>
                <input type="text" name="middlename" id="middlename" placeholder="Enter Middlename" required>
            </div>
            
            <div class="input-field">
            <label for="school"> School: </label>
            <select name="school" id="school" required>
                <option value="" disabled selected>Select School</option>
                <option value="Kolehiyo ng Lungsod ng Lipa">Kolehiyo ng Lungsod ng Lipa</option>
            </select>
            </div>
            <div class="input-field">
                <label for="start_date"> Start Date: </label>
                <input type="date" name="start_date" id="start_date" placeholder="Enter Start Date" required>
            </div>
            <div class="input-field">
            <label for="company"> Company: </label>
                <input type="text" name="company" id="company" placeholder="Enter Company" required>
            </div>
            <div class="input-field">
            <label for="department"> Department: </label>
            <select name="department" id="department" required>
                <option value="" disabled selected>Select Department</option>
                <option value="College of Computer Studies">College of Computer Studies</option>
            </select>
            </div>
            <div class="input-field">
            <label for="hours_required"> Total Hours Required: </label>
                <input type="number" name="hours_required" placeholder="Enter Total Hours Required" required>
            </div>
            
        
        <div class="action">
            <button class="btn-signup" type="submit">Register</button>
    </div>
    </form>
    </div>
</div>
<footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
<script>
    function togglePassword() {
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