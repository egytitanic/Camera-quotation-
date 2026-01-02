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
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>المبلغ الإجمالي</th>
                    <th>تاريخ الإنشاء</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td>#<?php echo $invoice['id']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                        <td><?php echo number_format($invoice['total_amount'], 2); ?> ج.م</td>
                        <td><?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></td>
                        <td class="text-end">
                            <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i> عرض
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <?php echo generate_pagination($total_invoices, $current_page, $records_per_page, 'invoices.php?'); ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>