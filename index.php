<?php
// If the user is not logged in, get current page URL & redirect
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../profile/login.php"); exit;
} else { header("Location: ./core/index.php"); }
?>
