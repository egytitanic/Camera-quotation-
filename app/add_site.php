<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if (!isset($_GET['customer_id']) || empty($_GET['customer_id'])) {
    header("Location: new_quote_customer.php");
    exit;
}
$customer_id = $_GET['customer_id'];

// Handle new site submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_site'])) {
    $name = $_POST['site_name'];
    $address = $_POST['site_address'];
    $location = $_POST['site_location'];

    $stmt = $conn->prepare("INSERT INTO sites (customer_id, name, address, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $customer_id, $name, $address, $location);
    if ($stmt->execute()) {
        $site_id = $conn->insert_id;
        $_SESSION['site_id'] = $site_id; // Store site_id for the next step
        header("Location: add_equipment.php");
        exit;
    } else {
        $error = "Error creating site.";
    }
}

// Fetch customer details and their existing sites
$customer_stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();

if (!$customer) {
    header("Location: new_quote_customer.php");
    exit;
}

$sites_stmt = $conn->prepare("SELECT * FROM sites WHERE customer_id = ? ORDER BY name ASC");
$sites_stmt->bind_param("i", $customer_id);
$sites_stmt->execute();
$sites = $sites_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Store customer_id in session for the next step (if a new site is added)
$_SESSION['customer_id'] = $customer_id;

include 'includes/header.php';
?>

<h1 class="text-center">إنشاء مقايسة جديدة</h1>

<div class="card">
    <div class="card-header">
        الخطوة 2: اختيار الموقع للعميل "<?php echo htmlspecialchars($customer['name']); ?>"
    </div>
    <div class="card-body">
        <?php if (!empty($sites)): ?>
            <h5>اختر موقع حالي</h5>
            <div class="list-group mb-4">
                <?php foreach($sites as $site): ?>
                    <a href="add_equipment.php?site_id=<?php echo $site['id']; ?>" class="list-group-item list-group-item-action">
                        <strong><?php echo htmlspecialchars($site['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($site['address']); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
            <hr>
            <h5>أو أضف موقع جديد</h5>
        <?php else: ?>
            <h5>إضافة موقع جديد</h5>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="add_site.php?customer_id=<?php echo $customer_id; ?>">
            <div class="mb-3">
                <label for="site_name" class="form-label">اسم الموقع</label>
                <input type="text" class="form-control" id="site_name" name="site_name" required>
            </div>
            <div class="mb-3">
                <label for="site_address" class="form-label">عنوان الموقع</label>
                <textarea class="form-control" id="site_address" name="site_address" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="site_location" class="form-label">رابط الموقع على الخريطة (اختياري)</label>
                <input type="text" class="form-control" id="site_location" name="site_location">
            </div>
            <button type="submit" name="add_site" class="btn btn-primary">إضافة الموقع والمتابعة <i class="bi bi-arrow-left"></i></button>
            <a href="new_quote_customer.php" class="btn btn-secondary">الرجوع لاختيار العميل</a>
        </form>
    </div>
</div>


<?php include 'includes/footer.php'; ?>