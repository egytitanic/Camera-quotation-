<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: invoices.php");
    exit;
}

$invoice_id = $_GET['id'];

// SQL to fetch all invoice data
$sql = "SELECT
            i.id as invoice_id, i.total_amount, i.created_at as invoice_date, i.quote_id,
            c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
            s.name AS site_name, s.address AS site_address,
            q.installation_expenses,
            ii.description, ii.quantity, ii.price
        FROM invoices i
        JOIN customers c ON i.customer_id = c.id
        JOIN quotes q ON i.quote_id = q.id
        JOIN sites s ON q.site_id = s.id
        JOIN invoice_items ii ON ii.invoice_id = i.id
        WHERE i.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: invoices.php");
    exit;
}

$items = [];
$invoice_data = null;
$installation_expenses = 0;

while ($row = $result->fetch_assoc()) {
    if (!$invoice_data) {
        $invoice_data = [
            'invoice_id' => $row['invoice_id'],
            'total_amount' => $row['total_amount'],
            'invoice_date' => $row['invoice_date'],
            'quote_id' => $row['quote_id'],
            'customer_name' => $row['customer_name'],
            'customer_phone' => $row['customer_phone'],
            'customer_address' => $row['customer_address'],
            'site_name' => $row['site_name'],
            'site_address' => $row['site_address'],
        ];
        $installation_expenses = $row['installation_expenses'];
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

include 'includes/header.php';
?>

<div class="card" id="invoice-card">
    <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>فاتورة #<?php echo htmlspecialchars($invoice_data['invoice_id']); ?></h2>
                <p class="mb-0">تاريخ الفاتورة: <?php echo date('Y-m-d', strtotime($invoice_data['invoice_date'])); ?></p>
            </div>
            <a href="invoices.php" class="btn btn-light d-print-none">العودة للفواتير</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>بيانات العميل</h4>
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($invoice_data['customer_name']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($invoice_data['customer_phone']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($invoice_data['customer_address']); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4>بيانات الموقع</h4>
                <p><strong>اسم الموقع:</strong> <?php echo htmlspecialchars($invoice_data['site_name']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($invoice_data['site_address']); ?></p>
                <p><em>(بناءً على المقايسة رقم #<?php echo $invoice_data['quote_id']; ?>)</em></p>
            </div>
        </div>

        <h4 class="mt-5">بنود الفاتورة</h4>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>الوصف</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price'], 2); ?> ج.م</td>
                    <td><?php echo number_format($item['total'], 2); ?> ج.م</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>إجمالي البنود</strong></td>
                    <td><strong><?php echo number_format($invoice_data['total_amount'] - $installation_expenses, 2); ?> ج.م</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>مصروفات التركيب والأدوات الإضافية</strong></td>
                    <td><strong><?php echo number_format($installation_expenses, 2); ?> ج.م</strong></td>
                </tr>
                <tr class="table-dark">
                    <td colspan="3" class="text-end"><strong>الإجمالي الكلي المطلوب</strong></td>
                    <td><strong><?php echo number_format($invoice_data['total_amount'], 2); ?> ج.م</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="text-center mt-4 d-print-none">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة الفاتورة</button>
</div>

<?php include 'includes/footer.php'; ?>