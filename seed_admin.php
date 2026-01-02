<?php
require_once 'app/includes/db.php';

// --- Configuration ---
$username = 'admin';
$password = 'admin'; // You should change this after your first login
$role = 'manager';

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if the user already exists
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "Admin user already exists.\n";
} else {
    // Insert the new user
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt_insert->execute()) {
        echo "Admin user created successfully!\n";
        echo "Username: " . $username . "\n";
        echo "Password: " . $password . "\n";
    } else {
        echo "Error creating admin user: " . $stmt_insert->error . "\n";
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

echo "Script finished.\n";
?>