<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Check if a quote ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$quote_id = $_GET['id'];

// Base SQL query
$sql = "SELECT
            c.name AS customer_name, c.phone, c.address AS customer_address,
            s.name AS site_name, s.address AS site_address, s.location,
            q.created_at, q.user_id,
            qi.description, qi.quantity, qi.price
        FROM quotes q
        JOIN sites s ON q.site_id = s.id
        JOIN customers c ON s.customer_id = c.id
        JOIN quote_items qi ON qi.quote_id = q.id
        WHERE q.id = ?";

// For employees, add a condition to ensure they can only see their own quotes
if ($_SESSION['role'] == 'employee') {
    $sql .= " AND q.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quote_id, $_SESSION['user_id']);
} else {
    // Managers can see any quote
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quote_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Check if a quote was found and if the user has permission
if ($result->num_rows === 0) {
    // Redirect to dashboard if no quote found or no permission
    header("Location: dashboard.php");
    exit();
}

$items = [];
$customer_data = null;

while ($row = $result->fetch_assoc()) {
    if (!$customer_data) {
        $customer_data = [
            'name' => $row['customer_name'],
            'phone' => $row['phone'],
            'address' => $row['customer_address'],
            'site_name' => $row['site_name'],
            'site_address' => $row['site_address'],
            'location' => $row['location'],
            'created_at' => $row['created_at']
        ];
    }
    $items[] = [
        'description' => $row['description'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'total' => $row['quantity'] * $row['price']
    ];
}

$stmt->close();
$conn->close();

// We are no longer destroying the session here to allow continuous navigation

include 'includes/header.php';
?>

<div class="card" id="quotation-card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2>مقايسة كاميرات مراقبة</h2>
        <a href="dashboard.php" class="btn btn-light">العودة للوحة التحكم</a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>بيانات العميل</h4>
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($customer_data['name']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($customer_data['phone']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($customer_data['address']); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4>بيانات الموقع</h4>
                <p><strong>اسم الموقع:</strong> <?php echo htmlspecialchars($customer_data['site_name']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($customer_data['site_address']); ?></p>
                <?php if (!empty($customer_data['location'])): ?>
                    <p><strong>رابط الموقع:</strong> <a href="<?php echo htmlspecialchars($customer_data['location']); ?>" target="_blank">عرض على الخريطة</a></p>
                <?php endif; ?>
                <p><strong>تاريخ المقايسة:</strong> <?php echo date('Y-m-d', strtotime($customer_data['created_at'])); ?></p>
            </div>
        </div>

        <h4 class="mt-5">بنود المقايسة</h4>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">الوصف</th>
                    <th scope="col">الكمية</th>
                    <th scope="col">سعر الوحدة</th>
                    <th scope="col">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                foreach ($items as $index => $item):
                    $grand_total += $item['total'];
                ?>
                <tr>
                    <th scope="row"><?php echo $index + 1; ?></th>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price'], 2); ?> ج.م</td>
                    <td><?php echo number_format($item['total'], 2); ?> ج.م</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <td colspan="4" class="text-end"><strong>الإجمالي الكلي</strong></td>
                    <td><strong><?php echo number_format($grand_total, 2); ?> ج.م</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="text-center mt-4 print-buttons">
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> طباعة المقايسة</button>
    <a href="dashboard.php" class="btn btn-secondary"> العودة للوحة التحكم</a>
</div>

<style>
@media print {
    .print-buttons, .card-header a {
        display: none;
    }
    body * {
        visibility: hidden;
    }
    #quotation-card, #quotation-card * {
        visibility: visible;
    }
    #quotation-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>