<!-- Description: Profile page, Author: Ramesh Singh, Copyright © 2024 PASA -->
<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/config.php');
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php"); exit;
} //else { echo "SESSION[email]=".$_SESSION['email']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../m7g/m7g-styles.css">
</head>
<body>
    <header class="header">
        <div class="navbar">
            <a href="../m7g/index.php">Dashboard</a>
            <a href="../m7g/claim.php">Analysis</a>
            <a href="#" class="active"><b>Profile</b></a>
        </div>
    </header>
    <div class="container">
        <main class="content">
            <h3>Profile</h3>
            <h3>User: [ <?php echo $_SESSION['username']; ?> ]</h3>
            <h3>Email: [ <?php echo $_SESSION['email']; ?> ]</h3>
            <h3><a href="logout.php">Logout</a></h3>
        </main>
        <!--<aside class="sidebar"></aside>-->
    </div>
    <footer class="footer">
        <p>© 2024 Panasonic Automotive Systems</p>
    </footer>
</body>
</html>
