<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quote_id'])) {
    $quote_id = $_POST['quote_id'];
    $installation_expenses = $_POST['installation_expenses'] ?? 0.00;

    // --- Authorization Check ---
    // Fetch the quote to ensure it exists, is pending, and if user is employee, they own it
    $check_sql = "SELECT user_id FROM quotes WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $quote = $stmt->get_result()->fetch_assoc();

    if (!$quote || ($_SESSION['role'] === 'employee' && $quote['user_id'] != $_SESSION['user_id'])) {
        // Not found, already invoiced, or not authorized
        header("Location: ../dashboard.php?error=unauthorized");
        exit;
    }

    // --- Begin Update Transaction ---
    $conn->begin_transaction();
    try {
        // 1. Update the main quote record (installation expenses)
        $stmt_quote = $conn->prepare("UPDATE quotes SET installation_expenses = ? WHERE id = ?");
        $stmt_quote->bind_param("di", $installation_expenses, $quote_id);
        $stmt_quote->execute();

        // 2. Delete all existing items for this quote
        $stmt_delete = $conn->prepare("DELETE FROM quote_items WHERE quote_id = ?");
        $stmt_delete->bind_param("i", $quote_id);
        $stmt_delete->execute();

        // 3. Insert the new/updated items
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

        // Commit the transaction
        $conn->commit();

        header("Location: ../quotation.php?id=" . $quote_id . "&success=updated");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        // Log error and redirect
        error_log("Error updating quote: " . $e->getMessage());
        header("Location: ../edit_quote.php?id=" . $quote_id . "&error=db_error");
        exit;
    }
} else {
    header("Location: ../dashboard.php");
    exit;
}
?>