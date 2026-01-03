<?php
// --- DATABASE CONNECTION ---
// IMPORTANT: Replace the placeholder values below with your actual database credentials.
// It is strongly recommended to use environment variables for production environments
// to avoid committing sensitive information to version control.

$servername = "127.0.0.1"; // Or your database server IP/hostname
$username = "your_username";     // Your database username
$password = "your_password";     // Your database password
$dbname = "camera_quote_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set character set to UTF-8
if ($conn->connect_error) {
    // In a real application, you would log this error and show a generic message.
    // For this project, we'll show the error for easier debugging.
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

?>
