<?php
// We must start the session on every page to check for login status.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is logged in, redirect to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    // If user is not logged in, redirect to the login page.
    header('Location: login.php');
    exit;
}
?>