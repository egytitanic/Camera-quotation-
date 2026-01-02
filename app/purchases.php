<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

// Fetch all purchases with supplier and user names
$sql = "SELECT p.id, p.purchase_date, p.total_amount, s.name as supplier_name, u.username
        FROM purchases p
        JOIN suppliers s ON p.supplier_id = s.id
        JOIN users u ON p.user_id = u.id
        ORDER BY p.purchase_date DESC, p.id DESC";
$purchases = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>فواتير المشتريات</h1>
    <a href="new_purchase.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> تسجيل فاتورة شراء جديدة
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>المورد</th>
                    <th>تاريخ الشراء</th>
                    <th>المبلغ الإجمالي</th>
                    <th>تم التسجيل بواسطة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td>#<?php echo $purchase['id']; ?></td>
                        <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                        <td><?php echo $purchase['purchase_date']; ?></td>
                        <td><?php echo number_format($purchase['total_amount'], 2); ?> ج.م</td>
                        <td><?php echo htmlspecialchars($purchase['username']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>