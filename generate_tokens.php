<?php
include("conn.php"); // Make sure this connects to your DB

function generateToken() {
    return bin2hex(random_bytes(16)); // 32-character secure token
}

$connection = connection(); // Or use your own DB connection logic

// Fetch users who don't have a token yet
$result = $connection->query("SELECT id FROM internship_users WHERE feedback_token IS NULL OR feedback_token = ''");

while ($row = $result->fetch_assoc()) {
    $token = generateToken();
    $id = $row['id'];

    // Update user with new token
    $stmt = $connection->prepare("UPDATE internship_users SET feedback_token = ? WHERE id = ?");
    $stmt->bind_param("si", $token, $id);
    $stmt->execute();
}

echo "Tokens generated for users without one.";
?>
