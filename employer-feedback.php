<?php
include("conn.php");
$connection = connection();

if (!isset($_GET['token'])) {
    die("Invalid token.");
}

$token = $_GET['token'];

// Check if token is valid
$stmt = $connection->prepare("SELECT id, firstname, surname FROM internship_users WHERE feedback_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$intern = $result->fetch_assoc();

if (!$intern) {
    die("Invalid or expired token.");
}

$intern_id = $intern['id']; // Defined early to fix feedback query

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = $_POST['feedback'];

    // Save feedback
    $stmt = $connection->prepare("INSERT INTO feedback (intern_id, feedback) VALUES (?, ?)");
    $stmt->bind_param("is", $intern_id, $feedback);
    $stmt->execute();

    

    echo "<script>alert('Thank you for your feedback!'); window.location.href='?token=$token';</script>";
    exit;
}

// Load feedback history
$stmtMess = $connection->prepare("SELECT feedback, submitted_at FROM feedback WHERE intern_id = ? ORDER BY submitted_at DESC");
$stmtMess->bind_param("i", $intern_id);
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
  <title>Employer Feedback</title>
  <link rel="stylesheet" href="css/main.css" />
  <link rel="icon" href="icon.jpg">
</head>
<body>
<header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1> CCS Student Internship Tracker. </h1>
    </div>
    <nav>
      <a href="index.php"> HOME </a>
      <a href="about.php"> ABOUT </a>
      <a href="login.php"> LOGIN </a>
      <a href="registration.php"> SIGNUP </a>
    </nav>
</header>
<div class="center-wrapper">
  <div class="card">
    <main class="main-content">
      <div class="horizontal-container" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; padding: 20px; flex-wrap: wrap;">

        <!-- Feedback Form -->
        <div class="feedback-form" style="flex: 1; min-width: 300px;">
          <h2>Submit Feedback for <?= htmlspecialchars($intern['firstname'] . " " . $intern['surname']) ?></h2>
          <form method="POST">
            <div class="input-field">
              <label>Message:</label>
              <textarea name="feedback" rows="5" style="width: 100%; font-size: 16px; padding: 10px; border: 2px solid #d81f26; border-radius: 7px;" required></textarea>
            </div>
            <button type="submit" class="btn-signup" style="margin-top: 10px;">Submit Feedback</button>
          </form>
        </div>

        <!-- Feedback History -->
        <section class="feedback-history" style="flex: 1; min-width: 300px;">
          <h2>Your Feedback History for <?= htmlspecialchars($intern['firstname'] . " " . $intern['surname']) ?></h2>
          <?php if (empty($feedbackList)): ?>
              <p>No feedback available yet.</p>
          <?php else: ?>
              <ul class="feedback-list">
                  <?php foreach ($feedbackList as $feedback): ?>
                      <li style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                          <strong>Date:</strong> <?= date("F j, Y, g:i A", strtotime($feedback['submitted_at'])) ?><br>
                          <strong>Message:</strong> <?= nl2br(htmlspecialchars($feedback['feedback'])) ?>
                      </li>
                  <?php endforeach; ?>
              </ul>
          <?php endif; ?>
        </section>
        <?php
        // Load other interns excluding current one and only those with a valid token
        $stmtOthers = $connection->prepare("SELECT firstname, surname, feedback_token FROM internship_users WHERE id != ? AND feedback_token IS NOT NULL");
        $stmtOthers->bind_param("i", $intern_id);
        $stmtOthers->execute();
        $resultOthers = $stmtOthers->get_result();

        $otherInterns = [];
        while ($row = $resultOthers->fetch_assoc()) {
            $otherInterns[] = $row;
        }
        ?>
        <!-- Other Interns List -->
        <section class="other-interns" style="flex: 1; min-width: 300px;">
          <h2>Other Interns You Can Give Feedback To</h2>
          <?php if (empty($otherInterns)): ?>
              <p>No other interns with available feedback links.</p>
          <?php else: ?>
              <ul class="feedback-list">
                  <?php foreach ($otherInterns as $other): ?>
                      <li style="margin-bottom: 10px;">
                        <a href="employer-feedback.php?token=<?= urlencode($other['feedback_token']) ?>" style="color: #d81f26; text-decoration: none;">
                          <?= htmlspecialchars($other['firstname'] . ' ' . $other['surname']) ?>
                        </a>
                      </li>
                  <?php endforeach; ?>
              </ul>
          <?php endif; ?>
        </section>

      </div>
    </main>
  </div>
</div>
<footer>
  <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
</footer>
</body>

</html>
