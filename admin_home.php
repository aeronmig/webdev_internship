<?php
session_start();
include("conn.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}
$connection = connection();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new record
        $email_address = $_POST['email_address'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $firstname = $_POST['firstname'];
        $middlename = $_POST['middlename'];
        $surname = $_POST['surname'];
        $company = $_POST['company'];
        $school = $_POST['school'];
        $department = $_POST['department'];
        $start_date = $_POST['start_date'];
        $hours_required = $_POST['hours_required'];

        $check = $connection->prepare("SELECT id FROM internship_users WHERE email_address = ?");
        $check->bind_param("s", $email_address);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>
                alert('Sorry, the email is already registered.');
                window.location.href = 'admin_home.php';
            </script>";
            exit;
        }

        $query = "INSERT INTO internship_users (email_address, password, firstname, middlename, surname, company, school, department, start_date, hours_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ssssssssss", $email_address, $password, $firstname, $middlename, $surname, $company, $school, $department, $start_date, $hours_required);
        if ($stmt->execute()) {
            echo "<script>alert('Account added successfully!');
            window.location.href = 'admin_home.php';
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif (isset($_POST['delete'])) {
        // Delete record
        $id = $_POST['id'];
        $query = "DELETE FROM internships WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Fetch all internship records
$query = "SELECT * FROM internship_users";
$result = $connection->query($query);

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Internship Tracker</title>
    <link rel="stylesheet" href="css/admin.css" />

</head>
<body>
    <header>
        <div class="logo-section">
            <img src="assets/logo.png" alt="Logo" class="logo" />
            <h1> CCS Student Internship Tracker. </h1>
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
                <h2>Manage Internship Records</h2>
                <h2>Existing Records</h2>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Company</th>
                                <th>School</th>
                                <th>Department</th>
                                <th>Start Date</th>
                                <th>Hours Required</th>
                                <th>Remaining Hours Required</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $userId = $row['id'];

                // Fetch accumulated hours from internship_log
                $stmtLogs = $connection->prepare("SELECT SUM(hours_accumulated) AS total_hours FROM internship_log WHERE id = ?");
                $stmtLogs->bind_param("i", $userId);
                $stmtLogs->execute();
                $resultLogs = $stmtLogs->get_result();
                $accumulatedHours = 0;
                if ($rowLogs = $resultLogs->fetch_assoc()) {
                    $accumulatedHours = $rowLogs['total_hours'] ?? 0;
                }

                // Calculate remaining hours
                $remainingHours = max($row['hours_required'] - $accumulatedHours, 0);
            ?>
            <tr>
                <td><?php echo $row['firstname'] . " " . $row['middlename'] . " " . $row['surname']; ?></td>
                <td><?php echo $row['company']; ?></td>
                <td><?php echo $row['school']; ?></td>
                <td><?php echo $row['department']; ?></td>
                <td><?php echo $row['start_date']; ?></td>
                <td><?php echo $row['hours_required']; ?></td>
                <td><?php echo $remainingHours; ?></td>
                <td>
                    <a href="update.php?id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>
                    
                
            </div>
            <div class="center-wrapper">
                <div class="login-form">
                    <form method="post" class="add-record-form">
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
                        <button class="btn-signup" name="add">Add Record</button>
                    </div>
                    </form>
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

