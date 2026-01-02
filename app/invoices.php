<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/pagination.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

// Pagination
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$total_invoices = $conn->query("SELECT COUNT(id) as total FROM invoices")->fetch_assoc()['total'];

// Fetch invoices for the current page
$sql = "SELECT i.id, i.total_amount, i.created_at, c.name as customer_name, i.quote_id
        FROM invoices i
        JOIN customers c ON i.customer_id = c.id
        ORDER BY i.created_at DESC
        LIMIT $records_per_page OFFSET $offset";
$invoices = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة الفواتير</h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <!-- ... (table head and body are the same) ... -->
        </table>
    </div>
    <div class="card-footer">
        <?php echo generate_pagination($total_invoices, $current_page, $records_per_page, 'invoices.php?'); ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>