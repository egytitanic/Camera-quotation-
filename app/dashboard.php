<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Base SQL query
$sql = "SELECT
            q.id as quote_id,
            c.name as customer_name,
            s.name as site_name,
            u.username as created_by,
            q.created_at
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

<div class="card">
    <div class="card-header">
        قائمة المقايسات
    </div>
    <div class="card-body">
        <?php if (count($quotes) > 0): ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>رقم المقايسة</th>
                        <th>اسم العميل</th>
                        <th>اسم الموقع</th>
                        <?php if ($_SESSION['role'] == 'manager'): ?>
                            <th>تم إنشاؤها بواسطة</th>
                        <?php endif; ?>
                        <th>التاريخ</th>
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
                                <a href="quotation.php?id=<?php echo $quote['quote_id']; ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> عرض
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">لا توجد مقايسات لعرضها. ابدأ بإنشاء واحدة جديدة!</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>