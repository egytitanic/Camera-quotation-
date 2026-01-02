<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

// This action is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $user_id = $_SESSION['user_id'];

    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $unit_prices = $_POST['unit_price'];

    if (empty($supplier_id) || empty($purchase_date) || empty($product_ids)) {
        // Basic validation
        header("Location: ../new_purchase.php?error=missing_data");
        exit;
    }

    $total_amount = 0;
    for ($i=0; $i < count($product_ids); $i++) {
        $total_amount += $quantities[$i] * $unit_prices[$i];
    }

    $conn->begin_transaction();
    try {
        // Create purchase record
        $stmt = $conn->prepare("INSERT INTO purchases (supplier_id, purchase_date, total_amount, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isdi", $supplier_id, $purchase_date, $total_amount, $user_id);
        $stmt->execute();
        $purchase_id = $conn->insert_id;

        // Create purchase items
        $item_stmt = $conn->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        for ($i=0; $i < count($product_ids); $i++) {
            $item_stmt->bind_param("iiid", $purchase_id, $product_ids[$i], $quantities[$i], $unit_prices[$i]);
            $item_stmt->execute();
        }

        $conn->commit();
        header("Location: ../purchases.php?success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        // Log error instead of dying
        error_log("Error creating purchase: " . $e->getMessage());
        header("Location: ../new_purchase.php?error=db_error");
        exit;
    }
} else {
    // Redirect if accessed directly
    header("Location: ../new_purchase.php");
    exit;
}
?>