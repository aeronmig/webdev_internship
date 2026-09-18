<?php
session_start();
include("conn.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['email_address']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
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
</head>
<body>
  <header>
    <div class="logo-section">
      <img src="assets/logo.png" alt="Logo" class="logo" />
      <h1> CCS DEPARTMENT. </h1>
    </div>
    <nav>
      <a href="admin_home.php"> HOME </a>
      <a href="admin_about.php"> ABOUT </a>
      <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"> Logout </a>
    </nav>
  </header>
  <div class="center-wrapper">
    <div class="card">
      <main class="kll">
        <h2> Computer Studies Department </h2>
        <p> &nbsp; Computer Studies is a dynamic and ever-evolving field that goes beyond programming. It is the study of how technology can solve real-world problems through logic, innovation, and creativity. The roots of Computer Science can be traced back to the 1940s with the invention of the first digital computers. Over the decades, it has grown into one of the most in-demand disciplines worldwide, shaping industries, economies, and the way we live. <br> <br> 
            &nbsp; At Kolehiyo ng Lungsod ng Lipa (KLL), the College of Computer Studies (CCS) is committed to producing globally competitive, industry-ready professionals. The department offers a comprehensive curriculum focused on programming, data structures, web and software development, networking, cybersecurity, and the latest emerging technologies. Through hands-on learning and innovative teaching approaches, CCS ensures that students not only gain technical knowledge but also develop critical thinking, problem-solving skills, and ethical values. <br> <br>
            &nbsp; With a strong foundation in both theory and practical application, the CCS Department at KLL empowers students to become the next generation of tech leaders, innovators, and digital problem-solvers. 
        </p>
      </main>
    </div>
  </div>
  <footer>
    <p> © 2025 Ron Wesley Mendoza, Aeron Mig Garcia & Jad Andrei Agsamosam. All rights reserved. </p>
  </footer>
</body>
</html>
