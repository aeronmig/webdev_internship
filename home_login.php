<?php
session_start();

include("conn.php");
// After validating username and password
$connection = connection();
$email_address = $_SESSION['email_address'];

$sql = "SELECT firstname FROM internship_users WHERE email_address = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $email_address);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['firstname'] = $row['firstname'];
}
if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;
} 
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
$stmtLogs = $connection->prepare("SELECT SUM(hours_accumulated) AS total_hours FROM internship_log WHERE id = ?");
$stmtLogs->bind_param("i", $userId);
$stmtLogs->execute();
$resultLogs = $stmtLogs->get_result();
$hoursAccumulated = 0;

if ($rowLogs = $resultLogs->fetch_assoc()) {
    $hoursAccumulated = $rowLogs['total_hours'] ?? 0;
}

// 3. Calculate remaining hours
$remainingHours = max($hoursRequired - $hoursAccumulated, 0);



$stmtMess = $connection->prepare("SELECT feedback, submitted_at FROM feedback WHERE intern_id = ? ORDER BY submitted_at DESC");
$stmtMess->bind_param("i", $userId);
$stmtMess->execute();
$result = $stmtMess->get_result();

$feedbackList = [];
while ($row = $result->fetch_assoc()) {
    $feedbackList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> CCS Department </title>
  
  <link rel="stylesheet" href="css/main.css" />
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
  

  <main class="main-content">
    <h2> Hello, <?php echo $_SESSION['firstname']; ?>!</h2><br>
    <h2> Welcome to </h2>
    <h2> Kolehiyo ng Lungsod ng Lipa </h2><br>
    <h3> Bachelor of Science <br> in Computer Science </h3><br><br><br><br><br>
    <div class="card hour-summary">
  <h1>Internship Progress</h1>
  <p><strong>Good job! Your total hours accumulated:</strong> <?= $hoursAccumulated ?> hours</p>

  <p><strong>You're almost there! Your total remaining hours required:</strong> <?= $remainingHours ?> hours</p>
  <p><strong>Not bad! Keep up the good work :)</strong></p>
</div>
  </main>
  <section class="feedback-section">
    <h2>Take inspiration from your Employer's feedback</h2>
    <?php if (empty($feedbackList)): ?>
        <p>No feedback available yet.</p>
    <?php else: ?>
        <ul class="feedback-list">
            <?php foreach ($feedbackList as $feedback): ?>
                <li>
                    <strong>Date:</strong> <?= date("F j, Y, g:i A", strtotime($feedback['submitted_at'])) ?><br>
                    <strong>Message:</strong> <?= htmlspecialchars($feedback['feedback']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

  <footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
</body>
</html>
