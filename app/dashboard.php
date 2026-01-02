<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Base SQL query
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

// Modify query based on user role
if ($_SESSION['role'] == 'employee') {
    $sql .= " WHERE q.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    // Manager sees all quotes
    $sql .= " ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$quotes = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>لوحة التحكم</h1>
    <a href="new_quote_customer.php" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle"></i> إنشاء مقايسة جديدة</a>
</div>

<?php if ($_SESSION['role'] == 'manager'): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-box-seam fs-2"></i></h5>
                <a href="products.php" class="btn btn-secondary">إدارة المخزون</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-file-earmark-text fs-2"></i></h5>
                <a href="invoices.php" class="btn btn-secondary">إدارة الفواتير</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-truck fs-2"></i></h5>
                <a href="suppliers.php" class="btn btn-secondary">إدارة الموردين</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-graph-up fs-2"></i></h5>
                <a href="reports.php" class="btn btn-secondary">عرض التقارير</a>
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
                        <th>تم إنشاؤها بواسطة</th>
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
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>