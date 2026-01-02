<?php
session_start();
require_once '../includes/db.php';

// Ensure the user is logged in and has a site_id from the previous step
if (!isset($_SESSION['user_id']) || !isset($_SESSION['site_id'])) {
    header("Location: ../new_quote_customer.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_SESSION['site_id'];
    $user_id = $_SESSION['user_id'];
    $installation_expenses = $_POST['installation_expenses'] ?? 0.00;

    // Begin Transaction
    $conn->begin_transaction();

    try {
        // 1. Create a new quote record
        $stmt_quote = $conn->prepare("INSERT INTO quotes (site_id, user_id, installation_expenses) VALUES (?, ?, ?)");
        $stmt_quote->bind_param("iid", $site_id, $user_id, $installation_expenses);
        $stmt_quote->execute();
        $quote_id = $conn->insert_id;
        $stmt_quote->close();

        // 2. Add items to the quote from the new inventory-based form
        $product_ids = $_POST['product_id'];
        $descriptions = $_POST['product_description'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];

        $stmt_item = $conn->prepare("INSERT INTO quote_items (quote_id, product_id, description, quantity, price) VALUES (?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && !empty($quantities[$i])) {
                $stmt_item->bind_param("iisid", $quote_id, $product_ids[$i], $descriptions[$i], $quantities[$i], $prices[$i]);
                $stmt_item->execute();
            }
        }
        $stmt_item->close();

        // Commit the transaction
        $conn->commit();

        // Store quote_id in a temporary session var for the redirect
        $_SESSION['temp_quote_id'] = $quote_id;

        // Clean up the quote creation session variables
        unset($_SESSION['customer_id']);
        unset($_SESSION['site_id']);

        header("Location: ../quotation.php?id=" . $quote_id);
        exit();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        // Handle error - ideally log this
        die("Error creating quote: " . $exception->getMessage());
    }

    $conn->close();
} else {
    header("Location: ../add_equipment.php");
    exit();
}
?>