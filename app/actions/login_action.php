<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare a statement to select the user
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, start the session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect to the dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Invalid password
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } else {
        // Invalid username
        header("Location: ../login.php?error=invalid_credentials");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect if accessed directly without POST
    header("Location: ../login.php");
    exit();
}
?>