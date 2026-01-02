<?php
// We must start the session on every page that uses this check.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}
?>