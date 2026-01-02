<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// --- Fetch stats for Manager ---
if ($_SESSION['role'] == 'manager') {
    // 1. Pending quotes
    $pending_quotes = $conn->query("SELECT COUNT(id) as count FROM quotes WHERE status = 'pending'")->fetch_assoc()['count'];

    // 2. Revenue this month
    $rev_this_month = $conn->query("SELECT SUM(total_amount) as total FROM invoices WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'];

    // 3. Total customers
    $total_customers = $conn->query("SELECT COUNT(id) as count FROM customers")->fetch_assoc()['count'];

    // 4. Total products
    $total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];
}


// --- Fetch quotes list ---
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
    $sql .= " WHERE q.user_id = ? ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    $sql .= " ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$quotes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>لوحة التحكم</h1>
    <a href="new_quote_customer.php" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle"></i> إنشاء مقايسة جديدة</a>
</div>

<?php if ($_SESSION['role'] == 'manager'): ?>
<!-- Quick Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">مقايسات معلقة</h5>
                <p class="card-text fs-4"><?php echo $pending_quotes; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">إيرادات الشهر</h5>
                <p class="card-text fs-4"><?php echo number_format($rev_this_month ?? 0, 2); ?> ج.م</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">إجمالي العملاء</h5>
                <p class="card-text fs-4"><?php echo $total_customers; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
         <div class="card text-white bg-secondary mb-3">
            <div class="card-body">
                <h5 class="card-title">إجمالي المنتجات</h5>
                <p class="card-text fs-4"><?php echo $total_products; ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="card">
    <div class="card-header">
        آخر المقايسات
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>رقم المقايسة</th>
                    <th>العميل</th>
                    <th>الموقع</th>
                    <?php if ($_SESSION['role'] == 'manager'): ?>
                        <th>بواسطة</th>
                    <?php endif; ?>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr>
                        <td>#<?php echo $quote['quote_id']; ?></td>
                        <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($quote['site_name']); ?></td>
                        <?php if ($_SESSION['role'] == 'manager'): ?>
                            <td><?php echo htmlspecialchars($quote['created_by']); ?></td>
                        <?php endif; ?>
                        <td><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></td>
                        <td>
                            <?php if ($quote['status'] == 'invoiced'): ?>
                                <span class="badge bg-success">تمت الفوترة</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">قيد الانتظار</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="quotation.php?id=<?php echo $quote['quote_id']; ?>" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i> عرض
                            </a>
                             <?php if ($quote['status'] == 'pending'): ?>
                                <a href="edit_quote.php?id=<?php echo $quote['quote_id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> تعديل
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>