<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/pagination.php';

// ... (stats fetching logic is the same) ...

// --- Pagination Setup for Quotes List ---
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// --- Fetch quotes list with pagination ---
$count_sql = "SELECT COUNT(q.id) as total FROM quotes q";
$sql = "SELECT
            q.id as quote_id,
            c.name as customer_name,
            s.name as site_name,
            u.username as created_by,
            q.created_at,
            q.status
        FROM quotes q
        JOIN sites s ON q.site_id = s.id
        JOIN customers c ON s.customer_id = c.id
        JOIN users u ON q.user_id = u.id";

if ($_SESSION['role'] == 'employee') {
    $where_clause = " WHERE q.user_id = ?";
    $count_sql .= $where_clause;
    $sql .= $where_clause;

    $stmt_count = $conn->prepare($count_sql);
    $stmt_count->bind_param("i", $_SESSION['user_id']);
} else {
    $stmt_count = $conn->prepare($count_sql);
}
$stmt_count->execute();
$total_quotes = $stmt_count->get_result()->fetch_assoc()['total'];

$sql .= " ORDER BY q.created_at DESC LIMIT $records_per_page OFFSET $offset";
if ($_SESSION['role'] == 'employee') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$quotes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// ... (closing db connection, include header) ...
?>
<!-- ... (HTML for title and stats cards) ... -->
<div class="card">
    <!-- ... (card header) ... -->
    <div class="card-body">
        <table class="table table-striped table-hover">
            <!-- ... (table head and body are the same) ... -->
        </table>
    </div>
    <div class="card-footer">
        <?php echo generate_pagination($total_quotes, $current_page, $records_per_page, 'dashboard.php?'); ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>