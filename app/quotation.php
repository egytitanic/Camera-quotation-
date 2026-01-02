<?php
session_start();
require_once 'includes/db.php';

// If the user hasn't completed the process, redirect them
if (!isset($_SESSION['quote_id'])) {
    header("Location: index.php");
    exit();
}

$quote_id = $_SESSION['quote_id'];

// Fetch all data for the quotation
$sql = "SELECT
            c.name AS customer_name, c.phone, c.address AS customer_address,
            s.name AS site_name, s.address AS site_address, s.location,
            q.created_at,
            qi.description, qi.quantity, qi.price
        FROM quotes q
        JOIN sites s ON q.site_id = s.id
        JOIN customers c ON s.customer_id = c.id
        JOIN quote_items qi ON qi.quote_id = q.id
        WHERE q.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quote_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$customer_data = null;
$site_data = null;
$quote_date = null;

while ($row = $result->fetch_assoc()) {
    if (!$customer_data) {
        $customer_data = [
            'name' => $row['customer_name'],
            'phone' => $row['phone'],
            'address' => $row['customer_address']
        ];
        $site_data = [
            'name' => $row['site_name'],
            'address' => $row['site_address'],
            'location' => $row['location']
        ];
        $quote_date = $row['created_at'];
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

// Clear the session data to start a new quote
session_destroy();

include 'includes/header.php';
?>

<div class="card" id="quotation-card">
    <div class="card-header bg-primary text-white">
        <h2>مقايسة كاميرات مراقبة</h2>
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
                <p><strong>اسم الموقع:</strong> <?php echo htmlspecialchars($site_data['name']); ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($site_data['address']); ?></p>
                <?php if (!empty($site_data['location'])): ?>
                    <p><strong>رابط الموقع:</strong> <a href="<?php echo htmlspecialchars($site_data['location']); ?>" target="_blank">عرض على الخريطة</a></p>
                <?php endif; ?>
                <p><strong>تاريخ المقايسة:</strong> <?php echo date('Y-m-d', strtotime($quote_date)); ?></p>
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

<div class="text-center mt-4">
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> طباعة المقايسة</button>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-plus-circle"></i> إنشاء مقايسة جديدة</a>
</div>

<style>
@media print {
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
    .btn {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>