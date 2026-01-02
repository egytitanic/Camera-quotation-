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
// We use a subquery to first calculate the total for each quote,
// then aggregate those totals for each user to avoid calculation errors.
$sql = "SELECT
            u.username,
            COUNT(t.quote_id) as quote_count,
            SUM(t.items_total) as total_items_value,
            SUM(t.installation_expenses) as total_installation_expenses
        FROM
            users u
        JOIN
            (
                SELECT
                    q.id as quote_id,
                    q.user_id,
                    q.installation_expenses,
                    SUM(qi.quantity * qi.price) as items_total
                FROM
                    quotes q
                JOIN
                    quote_items qi ON q.id = qi.quote_id
                WHERE
                    DATE(q.created_at) BETWEEN ? AND ?
                GROUP BY
                    q.id, q.user_id, q.installation_expenses
            ) as t ON u.id = t.user_id
        GROUP BY
            u.id, u.username
        ORDER BY
            total_items_value DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$performance_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include '../includes/header.php';
?>

<h1>تقرير أداء الموظفين</h1>
<p class="lead">عرض عدد وقيمة المقايسات التي أنشأها كل موظف خلال فترة زمنية محددة.</p>

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

<!-- Detailed Table -->
<div class="card">
    <div class="card-header">
        ملخص الأداء
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>الموظف</th>
                    <th>عدد المقايسات</th>
                    <th>إجمالي قيمة البنود</th>
                    <th>إجمالي مصروفات التركيب</th>
                    <th>القيمة الإجمالية</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($performance_data) > 0): ?>
                    <?php foreach ($performance_data as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['username']); ?></td>
                            <td><?php echo $data['quote_count']; ?></td>
                            <td><?php echo number_format($data['total_items_value'], 2); ?> ج.م</td>
                            <td><?php echo number_format($data['total_installation_expenses'], 2); ?> ج.م</td>
                            <td><strong><?php echo number_format($data['total_items_value'] + $data['total_installation_expenses'], 2); ?> ج.م</strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">لا توجد بيانات لعرضها في هذه الفترة.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>