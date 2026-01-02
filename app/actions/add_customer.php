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

        // Redirect to the next step (site selection) with the new customer ID
        header("Location: ../add_site.php?customer_id=" . $customer_id);
        exit();
    } else {
        // Handle error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect to the start of the flow
    header("Location: ../new_quote_customer.php");
    exit();
}
?>