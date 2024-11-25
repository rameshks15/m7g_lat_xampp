<!-- Description: Dashboard page, Author: Ramesh Singh, Copyright © 2024 PASA -->
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/config.php');
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../profile/login.php"); exit;
} //else { echo "SESSION[email]=".$_SESSION['email']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M7G - Dashboard</title>
    <link rel="stylesheet" href="m7g-styles.css">
    <script src="claimProcessor.js"></script>
    <script src="issueTag.js"></script>
</head>

<body>
    <header class="header">
        <div class="navbar">
            <a href="#" class="active"><b>Dashboard</b></a>
            <a href="claim.php">Analysis</a>
            <a href="../profile/index.php">Profile</a>
        </div>
    </header>
    <div class="container">
        <main class="content">
            <div id="issueTable"> </div>
            <div id="info"> </div>
        </main>
        <!--<aside class="sidebar"><h3>Sidebar</h3></aside>-->
    </div>
    <footer class="footer">
        <p>© 2024 Panasonic Automotive Systems</p>
    </footer>    
    <!-- Include the JavaScript file -->
    <script>
        window.addEventListener('DOMContentLoaded', fetchList);

    </script>
</body>
</html>
