<?php
session_start();

include("conn.php");
$connection = connection();
$userId = $_SESSION['id'];
// 1. Get hours_required from internship_users
$stmtUser = $connection->prepare("SELECT hours_required FROM internship_users WHERE id = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$hoursRequired = 0;

if ($rowUser = $resultUser->fetch_assoc()) {
    $hoursRequired = $rowUser['hours_required'];
}

// 2. Sum hours_accumulated from internship_logs
$stmtLog = $connection->prepare("SELECT SUM(hours_accumulated) AS total_hours FROM internship_log WHERE id = ?");
$stmtLog->bind_param("i", $userId);
$stmtLog->execute();
$resultLog = $stmtLog->get_result();
$hoursAccumulated = 0;

if ($rowLog = $resultLog->fetch_assoc()) {
    $hoursAccumulated = $rowLog['total_hours'] ?? 0;
}

// 3. Calculate remaining hours
$remainingHours = max($hoursRequired - $hoursAccumulated, 0);


if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'user') {
  header("Location: index.php");
  exit;
} 
$email_address = $_SESSION['email_address'];

$id = $_SESSION['id'];
$stmtLogs = $connection->prepare("SELECT user_log_id, date, time_in, time_out, hours_rendered, hours_accumulated FROM internship_log WHERE id = ? ORDER BY user_log_id DESC");
$stmtLogs->bind_param("i", $id);
$stmtLogs->execute();
$resultLogs = $stmtLogs->get_result();


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
  <title>OJT/Internship Tracker</title>
  <link rel="stylesheet" href="css/tracker-css.css">
</head>
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
    <div class="card">
      <main class="main-content">
        <h2>Remember, you only need <?= $remainingHours ?> hours of work. Keep grinding!</h2>
        <button onclick="window.print()" class="btn-signup">🖨️ Print</button>
        <div class="tracker-container">
          <h3> STUDENT INTERNSHIP TRACKER</h3>
          <form class="details">
            <div class="row">
              <label>Name of Trainee: <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname']) ?></label>
              <label>Company: <?= htmlspecialchars($user['company']) ?></label>
            </div>
            <div class="row">
              <label>School: <?= htmlspecialchars($user['school']) ?></label>
              <label>Department: <?= htmlspecialchars($user['department']) ?></label>
            </div>
            <div class="row">
              <label>Start Date: <?= date('m/d/Y', strtotime($user['start_date'])) ?></label>
              <label>Hours Required: <?= htmlspecialchars($user['hours_required']) ?></label>
            </div>
          </form>
          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th>Day</th>
                  <th>Date (mm/dd/yyyy)</th>
                  <th>In</th>
                  <th>Out</th>
                  <th>Hours Rendered</th>
                  <th>Hours Accumulated</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $resultLogs->fetch_assoc()) { ?>
                  <tr>
                    <td><?= $row['user_log_id'] ?></td>
                    <td><?= date("m/d/Y", strtotime($row['date'])) ?></td>
                    <td><?= date("g:iA", strtotime($row['time_in'])) ?></td>
                    <td><?= date("g:iA", strtotime($row['time_out'])) ?></td>
                    <td><?= $row['hours_rendered'] ?></td>
                    <td><?= $row['hours_accumulated'] ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
  <footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
</body>
</html>