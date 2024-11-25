<!-- Description: Logout page, Author: Ramesh Singh, Copyright © 2024 PASA -->
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/config.php');

$_SESSION = []; // unset all session variables
session_destroy(); // destroy the session
/* session_unset(); // unset all session variables
session_destroy();  // destroy the session */

// Redirect to the login page
header("Location: login.php");
exit;
?>