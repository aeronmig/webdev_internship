<?php
date_default_timezone_set('Asia/Manila');
session_start();
include("conn.php");
$connection = connection();

if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$today = date("Y-m-d");
$now = date("H:i:s"); // full time with seconds
$nowForInput = date("H:i"); // for HTML time input (without seconds)
$existingLog = null;

// Check if today's log exists
$stmt = $connection->prepare("SELECT * FROM internship_log WHERE id = ? AND date = ?");
$stmt->bind_param("is", $id, $today);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $existingLog = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'time_in') {
            if ($existingLog) {
                echo "<script>alert('You have already logged Time In today.'); window.location.href='tool.php';</script>";
                exit;
            }

            // Only accept exact current time for time_in (with some 30s leeway)
            $submittedTimeIn = date("H:i:s");
            $currentTimestamp = time();
            $submittedTimestamp = strtotime("$today $submittedTimeIn");
            if (abs($currentTimestamp - $submittedTimestamp) > 30) {
                echo "<script>alert('Time In must be the current time.'); window.location.href='tool.php';</script>";
                exit;
            }

            // Get last accumulated hours
            $stmt = $connection->prepare("SELECT hours_accumulated FROM internship_log WHERE id = ? ORDER BY user_log_id DESC LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $last = $res->fetch_assoc();
            $acc = $last ? $last['hours_accumulated'] : 0;

            // Get next log ID
            $stmt = $connection->prepare("SELECT MAX(user_log_id) as max_id FROM internship_log WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $nextUserLogId = $row['max_id'] ? $row['max_id'] + 1 : 1;

            // Insert time in only, time_out = '00:00:00' as placeholder
            $defaultTimeOut = "00:00:00";
            $insert = $connection->prepare("INSERT INTO internship_log (id, user_log_id, date, time_in, time_out, hours_accumulated) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("iisssd", $id, $nextUserLogId, $today, $submittedTimeIn, $defaultTimeOut, $acc);
            $insert->execute();

            echo "<script>alert('Time In recorded at current time. You may log Time Out later.'); window.location.href='tool.php';</script>";
            exit;

        } elseif ($_POST['action'] === 'time_out') {
            if (!$existingLog || $existingLog['time_out'] != '00:00:00') {
                echo "<script>alert('No pending Time Out to log or already logged out.'); window.location.href='tool.php';</script>";
                exit;
            }

            // Only accept exact current time for time_out (with some 30s leeway)
            $submittedTimeOut = date("H:i:s");
            $currentTimestamp = time();
            $submittedTimestamp = strtotime("$today $submittedTimeOut");
            if (abs($currentTimestamp - $submittedTimestamp) > 30) {
                echo "<script>alert('Time Out must be the current time.'); window.location.href='tool.php';</script>";
                exit;
            }

            $timeIn = $existingLog['time_in'];

            if ($submittedTimeOut <= $timeIn) {
                echo "<script>alert('Time Out must be after Time In'); window.location.href='tool.php';</script>";
                exit;
            }

            $in = new DateTime("$today $timeIn");
            $out = new DateTime("$today $submittedTimeOut");
            $interval = $in->diff($out);
            $hoursRendered = $interval->h + ($interval->i / 60);
            $newAccumulated = $existingLog['hours_accumulated'] + $hoursRendered;

            // Update time out and rendered hours
            $update = $connection->prepare("UPDATE internship_log SET time_out = ?, hours_rendered = ?, hours_accumulated = ? WHERE user_log_id = ? AND id = ?");
            $update->bind_param("sddii", $submittedTimeOut, $hoursRendered, $newAccumulated, $existingLog['user_log_id'], $id);
            $update->execute();

            echo "<script>alert('Time Out recorded successfully at current time.'); window.location.href='tracker.php';</script>";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CCS Department.</title>
  <link rel="stylesheet" href="css/main.css" />
  <link rel="icon" href="icon.jpg" />
</head>
<body>
<header>
  <div class="logo-section">
    <img src="assets/logo.png" alt="Logo" class="logo" />
    <h1>CCS Student Internship Tracker.</h1>
  </div>
  <nav>
    <a href="home_login.php">HOME</a>
    <a href="about_login.php">ABOUT</a>
    <a href="tool.php">LOG</a>
    <a href="tracker.php">TRACKER</a>
    <a href="profile.php">PROFILE</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">LOGOUT</a>
  </nav>
</header>
<div class="center-wrapper">
  <div class="card">
    <main class="main-content">
      <h2>Hello, future computer scientist. You may log here.</h2>

      <?php if (!$existingLog): ?>
        <!-- Time In Form (auto-filled, disabled, only submit current time) -->
        <form method="POST" action="tool.php">
          <div class="login-form">
            <div class="content">
              <div class="input-field">
                <label>Time In:
                  <input type="time" name="time_in" value="<?= $nowForInput ?>" disabled>
                </label>
                <br>
                <input type="hidden" name="action" value="time_in" />
                <button class="btn-signup" type="submit">Log Time In</button>
              </div>
            </div>
          </div>
        </form>

      <?php elseif ($existingLog && $existingLog['time_out'] === '00:00:00'): ?>
        <!-- Time Out Form (auto-filled, disabled, only submit current time) -->
        <form method="POST" action="tool.php">
          <div class="login-form">
            <div class="content">
              <div class="input-field">
              <?php 
$timeInObj = DateTime::createFromFormat('H:i:s', $existingLog['time_in']);
$timeInFormatted = $timeInObj ? $timeInObj->format('g:ia') : htmlspecialchars($existingLog['time_in']);
?>
<label>Time In: <?= $timeInFormatted ?></label><br><br>

                <label>Time Out:
                  <input type="time" name="time_out" value="<?= $nowForInput ?>" disabled>
                </label>
                <br>
                <input type="hidden" name="action" value="time_out" />
                <button class="btn-signup" type="submit">Log Time Out</button>
              </div>
            </div>
          </div>
        </form>

      <?php else: ?>
        <p>You have already logged both Time In and Time Out today. Try again tomorrow.</p>
      <?php endif; ?>
    </main>
  </div>
</div>
<footer>
  <p>© 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved.</p>
</footer>
</body>
</html>
