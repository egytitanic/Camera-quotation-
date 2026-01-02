<?php
session_start();
require_once '../includes/db.php';

// Ensure the user has a site_id from the previous step
if (!isset($_SESSION['site_id'])) {
    header("Location: ../add_site.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_SESSION['site_id'];

    // Begin Transaction
    $conn->begin_transaction();

    try {
        // 1. Create a new quote
        $stmt_quote = $conn->prepare("INSERT INTO quotes (site_id) VALUES (?)");
        $stmt_quote->bind_param("i", $site_id);
        $stmt_quote->execute();
        $quote_id = $conn->insert_id;
        $stmt_quote->close();

        // 2. Add items to the quote
        $descriptions = $_POST['product_description'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];

        $stmt_item = $conn->prepare("INSERT INTO quote_items (quote_id, description, quantity, price) VALUES (?, ?, ?, ?)");

        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
                $stmt_item->bind_param("isid", $quote_id, $descriptions[$i], $quantities[$i], $prices[$i]);
                $stmt_item->execute();
            }
        }
        $stmt_item->close();

        // Commit the transaction
        $conn->commit();

        // Store quote_id in session and redirect to the quotation page
        $_SESSION['quote_id'] = $quote_id;
        header("Location: ../quotation.php");
        exit();

    } catch (mysqli_sql_exception $exception) {
        // Rollback the transaction if something failed
        $conn->rollback();
        // Handle error
        echo "Error: " . $exception->getMessage();
    }

    $conn->close();
} else {
    // If not a POST request, redirect
    header("Location: ../add_equipment.php");
    exit();
}
?>