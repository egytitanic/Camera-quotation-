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

// --- Build queries based on role ---
$params = [];
$param_types = "";
$where_clause = "";

if ($_SESSION['role'] == 'employee') {
    $where_clause = " WHERE q.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $param_types .= "i";
}

$count_sql .= $where_clause;
$sql .= $where_clause;

// --- Get total count for pagination ---
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($param_types, ...$params);
}
$stmt_count->execute();
$total_quotes_result = $stmt_count->get_result()->fetch_assoc();
$total_quotes = $total_quotes_result ? $total_quotes_result['total'] : 0;
$stmt_count->close();

// --- Get paginated results ---
$sql .= " ORDER BY q.created_at DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);

$stmt->execute();
$result = $stmt->get_result();
$quotes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
// ... (closing db connection, include header) ...
?>
<!-- ... (HTML for title and stats cards) ... -->
<div class="card">
    <!-- ... (card header) ... -->
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>رقم المقايسة</th>
                    <th>العميل</th>
                    <th>الموقع</th>
                    <th>الحالة</th>
                    <th>أنشئ بواسطة</th>
                    <th>التاريخ</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr>
                        <td>#<?php echo $quote['quote_id']; ?></td>
                        <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($quote['site_name']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $quote['status'] == 'pending' ? 'warning' : 'success'; ?>">
                                <?php echo htmlspecialchars($quote['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($quote['created_by']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></td>
                        <td class="text-end">
                            <a href="quotation.php?id=<?php echo $quote['quote_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i> عرض
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <?php echo generate_pagination($total_quotes, $current_page, $records_per_page, 'dashboard.php?'); ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>