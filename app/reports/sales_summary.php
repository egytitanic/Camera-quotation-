<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: ../dashboard.php");
    exit;
}

// Default date range (last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// --- Data Fetching ---
$params = [];
$types = '';

$sql = "SELECT
            q.id,
            q.installation_expenses,
            SUM(qi.price * qi.quantity) as items_total,
            c.name as customer_name,
            u.username as created_by,
            q.created_at
        FROM quotes q
        JOIN quote_items qi ON q.id = qi.quote_id
        JOIN sites s ON q.site_id = s.id
        JOIN customers c ON s.customer_id = c.id
        JOIN users u ON q.user_id = u.id";

// Date filtering
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " WHERE DATE(q.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
}

$sql .= " GROUP BY q.id ORDER BY q.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$quotes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- Calculations ---
$total_items_value = 0;
$total_installation_expenses = 0;
foreach ($quotes as $quote) {
    $total_items_value += $quote['items_total'];
    $total_installation_expenses += $quote['installation_expenses'];
}
$grand_total = $total_items_value + $total_installation_expenses;


include '../includes/header.php';
?>

<h1>تقرير ملخص المبيعات</h1>
<p class="lead">عرض ملخص لقيم المقايسات الإجمالية خلال فترة زمنية محددة.</p>

<!-- Filter Form -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="start_date" class="form-label">من تاريخ</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-auto">
                <label for="end_date" class="form-label">إلى تاريخ</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-auto align-self-end">
                <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">إجمالي قيمة البنود</h5>
                <p class="card-text fs-4"><?php echo number_format($total_items_value, 2); ?> ج.م</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">إجمالي مصروفات التركيب</h5>
                <p class="card-text fs-4"><?php echo number_format($total_installation_expenses, 2); ?> ج.م</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">الإجمالي الكلي للمبيعات</h5>
                <p class="card-text fs-4"><?php echo number_format($grand_total, 2); ?> ج.م</p>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card">
    <div class="card-header">
        التفاصيل (<?php echo count($quotes); ?> مقايسة)
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>رقم المقايسة</th>
                    <th>العميل</th>
                    <th>إجمالي البنود</th>
                    <th>مصروفات التركيب</th>
                    <th>الإجمالي</th>
                    <th>تم إنشاؤها بواسطة</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr>
                        <td><a href="../quotation.php?id=<?php echo $quote['id']; ?>">#<?php echo $quote['id']; ?></a></td>
                        <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                        <td><?php echo number_format($quote['items_total'], 2); ?> ج.م</td>
                        <td><?php echo number_format($quote['installation_expenses'], 2); ?> ج.م</td>
                        <td><?php echo number_format($quote['items_total'] + $quote['installation_expenses'], 2); ?> ج.م</td>
                        <td><?php echo htmlspecialchars($quote['created_by']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include '../includes/footer.php'; ?>