<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$quote_id = $_GET['id'];

$sql = "SELECT
            c.name AS customer_name, c.phone, c.address AS customer_address, c.id as customer_id,
            s.name AS site_name, s.address AS site_address, s.location,
            q.created_at, q.user_id, q.installation_expenses, q.status,
            qi.product_id, qi.description, qi.quantity, qi.price
        FROM quotes q
        JOIN sites s ON q.site_id = s.id
        JOIN customers c ON s.customer_id = c.id
        JOIN quote_items qi ON qi.quote_id = q.id
        WHERE q.id = ?";

if ($_SESSION['role'] == 'employee') {
    $sql .= " AND q.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quote_id, $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quote_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$items = [];
$quote_data = null;
$installation_expenses = 0;

while ($row = $result->fetch_assoc()) {
    if (!$quote_data) {
        $quote_data = [
            'customer_id' => $row['customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_phone' => $row['phone'],
            'customer_address' => $row['customer_address'],
            'site_name' => $row['site_name'],
            'site_address' => $row['site_address'],
            'location' => $row['location'],
            'created_at' => $row['created_at'],
            'status' => $row['status']
        ];
        $installation_expenses = $row['installation_expenses'];
    }
    $items[] = [
        'product_id' => $row['product_id'],
        'description' => $row['description'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'total' => $row['quantity'] * $row['price']
    ];
}
$stmt->close();
$conn->close();

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
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($quote_data['customer_name']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($quote_data['customer_phone']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($quote_data['customer_address']); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4>بيانات الموقع</h4>
                <p><strong>اسم الموقع:</strong> <?php echo htmlspecialchars($quote_data['site_name']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($quote_data['site_address']); ?></p>
                <p><strong>تاريخ المقايسة:</strong> <?php echo date('Y-m-d', strtotime($quote_data['created_at'])); ?></p>
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
                $items_total = 0;
                foreach ($items as $index => $item):
                    $items_total += $item['total'];
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
                <tr>
                    <td colspan="4" class="text-end"><strong>إجمالي البنود</strong></td>
                    <td><strong><?php echo number_format($items_total, 2); ?> ج.م</strong></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong>مصروفات التركيب والأدوات الإضافية</strong></td>
                    <td><strong><?php echo number_format($installation_expenses, 2); ?> ج.م</strong></td>
                </tr>
                <tr class="table-primary">
                    <td colspan="4" class="text-end"><strong>الإجمالي الكلي</strong></td>
                    <td><strong><?php echo number_format($items_total + $installation_expenses, 2); ?> ج.م</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="text-center mt-4 print-buttons">
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> طباعة المقايسة</button>
    <a href="dashboard.php" class="btn btn-secondary"> العودة للوحة التحكم</a>
    <?php if ($_SESSION['role'] === 'manager' && $quote_data['status'] === 'pending'): ?>
        <a href="actions/convert_to_invoice.php?quote_id=<?php echo $quote_id; ?>" class="btn btn-success" onclick="return confirm('هل أنت متأكد من تحويل هذه المقايسة إلى فاتورة؟ لا يمكن التراجع عن هذا الإجراء.');">
            <i class="bi bi-file-earmark-text"></i> تحويل إلى فاتورة
        </a>
    <?php endif; ?>
</div>

<style>
@media print {
    .print-buttons, .card-header a { display: none; }
    body * { visibility: hidden; }
    #quotation-card, #quotation-card * { visibility: visible; }
    #quotation-card { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>

<?php include 'includes/footer.php'; ?>