<?php
session_start();
require_once("conn.php");
$connection = connection();
// Check if the user is logged in and has admin role
if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;

}
$email_address = $_SESSION['email_address'];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['form_type'] === 'update_profile') {
      $company = $_POST['company'];
      $start_date = $_POST['start_date'];
      $hours_required = $_POST['hours_required'];

      $update_sql = "UPDATE internship_users SET company = ?, start_date = ?, hours_required = ? WHERE email_address = ?";
      $stmt = $connection->prepare($update_sql);
      $stmt->bind_param("ssis", $company, $start_date, $hours_required, $email_address);

      if ($stmt->execute()) {
          echo "<script>alert('Profile updated successfully.');
          window.location.href = 'profile.php';
          </script>";
      } else {
          echo "<script>alert('Error updating profile.');</script>";
      }

  } elseif ($_POST['form_type'] === 'change_password') {
      $current_password = $_POST['current_password'];
      $new_password = $_POST['new_password'];
      $confirm_password = $_POST['confirm_password'];

      if ($new_password !== $confirm_password) {
          echo "<script>alert('New passwords do not match.');</script>";
      } else {
          $check_sql = "SELECT password FROM internship_users WHERE email_address = ?";
          $check_stmt = $connection->prepare($check_sql);
          $check_stmt->bind_param("s", $email_address);
          $check_stmt->execute();
          $result = $check_stmt->get_result();
          $row = $result->fetch_assoc();

          if ($row && password_verify($current_password, $row['password'])) {
              $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

              $update_pass_sql = "UPDATE internship_users SET password = ? WHERE email_address = ?";
              $update_pass_stmt = $connection->prepare($update_pass_sql);
              $update_pass_stmt->bind_param("ss", $hashed_new_password, $email_address);

              if ($update_pass_stmt->execute()) {
                  echo "<script>alert('Password updated successfully.');
                  window.location.href = 'profile.php';</script>";
              } else {
                  echo "<script>alert('Error updating password.');</script>";
              }
          } else {
              echo "<script>alert('Current password is incorrect.');</script>";
          }
      }
  }
}


$sql = "SELECT * FROM internship_users WHERE email_address = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $email_address);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $surname = $user['surname'];
    $firstname = $user['firstname'];
    $middlename = $user['middlename'];
    $school = $user['school'];
    $start_date = $user['start_date'];
    $company = $user['company'];
    $department = $user['department'];
    $hours_required = $user['hours_required'];
    $password = $user['password']; // only if you want to display it
} else {
    echo "User profile not found.";
    exit;
}


   
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> CCS Department </title>
  <link rel="stylesheet" href="css/profile.css" />
  <link rel="icon" href="icon.jpg">
<body>
  <header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1> CCS Student Internship Tracker. </h1>
    </div>
    <nav>
    <a href="home_login.php"> HOME </a>
      <a href="about_login.php"> ABOUT </a>
      <a href="tool.php"> LOG </a>
      <a href="tracker.php"> TRACKER </a>
      <a href="profile.php"> PROFILE </a>
      <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"> LOGOUT </a>
      
    </nav>
  </header>
  
<div class="center-wrapper">
  <div class="login-form card">
    
  <form method="post" action="profile.php">
  <input type="hidden" name="form_type" value="update_profile">

  
    <div class="input-field">
    <label for="email_address"> Email Address: </label>
      <input type="email" name="update-email_address" value="<?php echo htmlspecialchars($email_address); ?>" disabled>
    </div>
    

    <div class="input-field">
           <label for="surname"> Surname: </label>
                <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($surname); ?>" disabled>
            </div> 
            <div class="input-field">
            <label for="firstname"> Firstname: </label>
                <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($firstname); ?>" disabled>
            </div>
            <div class="input-field">
            <label for="middlename"> Middlename: </label>
                <input type="text" name="middlename" id="middlename" value="<?php echo htmlspecialchars($middlename); ?>" disabled>
            </div>
            
            <div class="input-field">
            <label for="school"> School: </label>
            <input type="text" name="school"  value="<?php echo htmlspecialchars($school); ?>" disabled>
            </div>
            <div class="input-field">
            <label for="department"> Department: </label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>" disabled>
            </div>
            <div class="input-field">
            <label for="company"> Update Company: </label>
                <input type="text" name="company" id="company" value="<?php echo htmlspecialchars($company); ?>">
            </div>
            <div class="input-field">
                <label for="start_date"> Update Start Date: </label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
            </div>
            <div class="input-field">
            <label for="hours_required">Update Total Hours Required: </label>
                <input type="number" name="hours_required" value="<?php echo htmlspecialchars($hours_required); ?>">
            </div>
    <div class="action">
      <button class="btn-signup" type="submit">Update Profile</button>
    </div>
  </form>
  <div class="action">
  <button class="btn-signup" type="button" onclick="openPasswordModal()">Change Password</button>
</div>
  

 <!-- Password Modal -->
<div id="passwordModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closePasswordModal()">&times;</span>
    <h2>Change Password</h2>
    <form method="post" action="profile.php">
  <input type="hidden" name="form_type" value="change_password">

      <div class="input-field">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required>
      </div>
      <div class="checkbox-field">
            <input type="checkbox" id="showPassword" onclick="myFunction()"> Show Password
            </div>
      <div class="input-field">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" required>
      </div>
      <div class="input-field">
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
      </div>
      <div class="action">
        <button class="btn-signup" type="submit">Update Password</button>
      </div>
    </form>
  </div>
</div>
</div>
</div>
<script>
function openPasswordModal() {
  document.getElementById("passwordModal").style.display = "block";
}

function closePasswordModal() {
  document.getElementById("passwordModal").style.display = "none";
}
function myFunction() {
        var x = document.getElementById("current_password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
// Optional: Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById("passwordModal");
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>

</body>
<footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
</html>
