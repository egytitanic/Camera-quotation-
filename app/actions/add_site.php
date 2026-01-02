<?php
session_start();
require_once '../includes/db.php';

// Ensure the user has a customer_id from the previous step
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_SESSION['customer_id'];
    $name = $_POST['site_name'];
    $address = $_POST['site_address'];
    $location = $_POST['site_location'];

    $stmt = $conn->prepare("INSERT INTO sites (customer_id, name, address, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $customer_id, $name, $address, $location);

    if ($stmt->execute()) {
        $site_id = $conn->insert_id;
        $_SESSION['site_id'] = $site_id;
        // Redirect to the next step
        header("Location: ../add_equipment.php");
        exit();
    } else {
        // Handle error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect to the form page
    header("Location: ../add_site.php");
    exit();
}
?>