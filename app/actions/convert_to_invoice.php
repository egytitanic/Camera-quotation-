<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

// This action is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: ../dashboard.php");
    exit;
}

if (!isset($_GET['quote_id']) || empty($_GET['quote_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$quote_id = $_GET['quote_id'];

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Fetch quote data to ensure it exists and is 'pending'
    $quote_sql = "SELECT site_id, installation_expenses, status FROM quotes WHERE id = ?";
    $stmt = $conn->prepare($quote_sql);
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $quote_result = $stmt->get_result()->fetch_assoc();

    if (!$quote_result || $quote_result['status'] !== 'pending') {
        throw new Exception("Quote not found or has already been invoiced.");
    }

    // 2. Fetch customer ID from site
    $site_sql = "SELECT customer_id FROM sites WHERE id = ?";
    $stmt = $conn->prepare($site_sql);
    $stmt->bind_param("i", $quote_result['site_id']);
    $stmt->execute();
    $site_result = $stmt->get_result()->fetch_assoc();
    $customer_id = $site_result['customer_id'];

    // 3. Fetch all items from the quote and calculate total
    $items_sql = "SELECT product_id, description, quantity, price FROM quote_items WHERE quote_id = ?";
    $stmt = $conn->prepare($items_sql);
    $stmt->bind_param("i", $quote_id);
    $stmt->execute();
    $items_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $items_total = 0;
    foreach ($items_result as $item) {
        $items_total += $item['quantity'] * $item['price'];
    }
    $grand_total = $items_total + $quote_result['installation_expenses'];

    // 4. Create a new invoice
    $invoice_stmt = $conn->prepare("INSERT INTO invoices (quote_id, customer_id, total_amount) VALUES (?, ?, ?)");
    $invoice_stmt->bind_param("iid", $quote_id, $customer_id, $grand_total);
    $invoice_stmt->execute();
    $invoice_id = $conn->insert_id;

    // 5. Copy quote items to invoice items
    $invoice_item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, description, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items_result as $item) {
        $invoice_item_stmt->bind_param("iisid", $invoice_id, $item['product_id'], $item['description'], $item['quantity'], $item['price']);
        $invoice_item_stmt->execute();
    }

    // 6. Update the quote status to 'invoiced'
    $update_quote_stmt = $conn->prepare("UPDATE quotes SET status = 'invoiced' WHERE id = ?");
    $update_quote_stmt->bind_param("i", $quote_id);
    $update_quote_stmt->execute();

    // If all queries were successful, commit the transaction
    $conn->commit();

    // Redirect to the new invoice view page
    header("Location: ../view_invoice.php?id=" . $invoice_id);
    exit;

} catch (Exception $e) {
    // If any query fails, roll back the transaction
    $conn->rollback();
    // Optional: Log the error message
    // error_log($e->getMessage());
    header("Location: ../dashboard.php?error=invoice_creation_failed");
    exit;
}
?>