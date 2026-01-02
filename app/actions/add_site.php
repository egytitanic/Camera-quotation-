<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

// This file is kept for consistency but the logic is now primarily in add_site.php itself.
// It handles a direct POST from a form that ONLY adds a site.

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $name = $_POST['site_name'];
    $address = $_POST['site_address'];
    $location = $_POST['site_location'];

    $stmt = $conn->prepare("INSERT INTO sites (customer_id, name, address, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $customer_id, $name, $address, $location);

    if ($stmt->execute()) {
        $site_id = $conn->insert_id;
        $_SESSION['site_id'] = $site_id;
        header("Location: ../add_equipment.php");
        exit();
    } else {
        header("Location: ../add_site.php?customer_id=$customer_id&error=1");
        exit();
    }
} else {
    // If not a POST request or customer_id is missing, redirect
    header("Location: ../new_quote_customer.php");
    exit();
}
?>