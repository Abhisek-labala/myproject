<?php
session_start();
session_unset();
session_destroy();
// $_SESSION['username'] = $username;

// Redirect to login page if session username is not set
if (!isset($_SESSION['username'])) {
    header("Location: /myproject/login.php");
    exit();
}

echo 'Logging you out. Please wait...';
?>
