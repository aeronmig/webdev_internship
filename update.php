<?php
session_start();
require_once("conn.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;

}
$singleUser = null;
    if(isset($_GET['id'])){
        $id = $_GET['id'];
        $singleUser = getUser($id);
    }

    if(isset($_POST['update'])){
        $id = $_POST['update-id'];
        $email_address = $_POST['update-email_address'];
        $firstname = $_POST['update-firstname'];
        $middlename = $_POST['update-middlename'];
        $surname = $_POST['update-surname'];
        $company = $_POST['update-company'];
        $start_date= $_POST['update-start_date'];
        $hours_required = $_POST['update-hours_required'];
        updateUser($id, $email_address, $firstname, $middlename, $surname, $company, $start_date, $hours_required );
        header("Location: admin_home.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Record</title>
    <link rel="stylesheet" href="css/update.css">
    <link rel="icon" href="icon.jpg">
</head>
<body>
<header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1>Admin Panel - Internship Tracker</h1>
    </div>
    <nav>
      <a href="admin_home.php"> HOME </a>
      <a href="admin_about.php"> ABOUT </a>
      <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"> Logout </a>
    </nav>
</header>
<div class="center-wrapper">
  <div class="card">
    <main class="main-content">
      <div class="card-header">
        <h2 class="card-title mb-0">Update Record</h2>
      </div>
      <form method="post" action="update.php">
        <input type="hidden" name="update-id" value="<?php echo $singleUser['id']; ?>">
        <div class="input-field">
          <label>Email Address:
            <input type="email" name="update-email_address" value="<?php echo $singleUser['email_address']; ?>" required>
          </label>

          <label>First Name:
            <input type="text" name="update-firstname" value="<?php echo $singleUser['firstname']; ?>" required>
          </label>

          <label>Middle Name:
            <input type="text" name="update-middlename" value="<?php echo $singleUser['middlename']; ?>" required>
          </label>

          <label>Surname:
            <input type="text" name="update-surname" value="<?php echo $singleUser['surname']; ?>" required>
          </label>

          <label>Company:
            <input type="text" name="update-company" value="<?php echo $singleUser['company']; ?>" required>
          </label>

          <label>Start Date:
            <input type="date" name="update-start_date" value="<?php echo $singleUser['start_date']; ?>" required>
          </label>

          <label>Hours Required:
            <input type="number" name="update-hours_required" value="<?php echo $singleUser['hours_required']; ?>" required>
          </label>

          <button type="submit" class="btn-signup" name="update">Update Record</button>
        </div>
      </form>
    </main>
  </div>
</div>
<footer>
  <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
</footer>
</body>
</html>