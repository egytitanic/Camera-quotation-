<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['customer_name'];
    $phone = $_POST['customer_phone'];
    $address = $_POST['customer_address'];

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $address);

    if ($stmt->execute()) {
        $customer_id = $conn->insert_id;
        $_SESSION['customer_id'] = $customer_id;
        // Redirect to the next step
        header("Location: ../add_site.php");
        exit();
    } else {
        // Handle error - for simplicity, we'll just echo it
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect to the form page
    header("Location: ../index.php");
    exit();
}
?>