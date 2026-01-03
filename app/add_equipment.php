<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// If a site_id is passed via URL, it means a site was selected. Store it in the session.
if (isset($_GET['site_id'])) {
    $_SESSION['site_id'] = $_GET['site_id'];
    // Redirect to the same page without the GET parameter to prevent issues on refresh
    header("Location: add_equipment.php");
    exit;
}

// Session management for quote creation flow - check if site_id is now in session
if (!isset($_SESSION['site_id'])) {
    // If no site is selected, user must start over from customer selection
    header("Location: new_quote_customer.php");
    exit;
}
$site_id = $_SESSION['site_id'];

// Fetch all products for the selector
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch site and customer names for display
$stmt = $conn->prepare("SELECT s.name as site_name, c.name as customer_name FROM sites s JOIN customers c ON s.customer_id = c.id WHERE s.id = ?");
$stmt->bind_param("i", $site_id);
$stmt->execute();
$site_info = $stmt->get_result()->fetch_assoc();

include 'includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">


<div class="card">
    <div class="card-header">
        الخطوة 3: إضافة بنود المقايسة للموقع "<?php echo htmlspecialchars($site_info['site_name']); ?>" (العميل: <?php echo htmlspecialchars($site_info['customer_name']); ?>)
    </div>
    <div class="card-body">
        <form action="actions/add_equipment.php" method="POST">
            <div id="equipment_list">
                <!-- Dynamic items will be added here -->
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                     <select id="product-selector" placeholder="ابحث عن منتج لإضافته..."></select>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="installation_expenses" class="form-label">مصروفات التركيب والأدوات الإضافية</label>
                    <input type="number" step="0.01" name="installation_expenses" class="form-control" value="0.00" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success">إنشاء المقايسة النهائية <i class="bi bi-check-lg"></i></button>
            <a href="add_site.php?customer_id=<?php echo $_SESSION['customer_id']; ?>" class="btn btn-secondary">الرجوع لاختيار الموقع</a>
        </form>
    </div>
</div>

<template id="item-template">
    <div class="row equipment-item mb-3 align-items-center">
        <input type="hidden" name="product_id[]" class="product-id">
        <div class="col-md-4">
            <label class="form-label">اسم البند</label>
            <input type="text" name="product_description[]" class="form-control product-description" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">الكمية</label>
            <input type="number" name="quantity[]" class="form-control quantity" value="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">السعر (للقطعة)</label>
            <input type="number" step="0.01" name="price[]" class="form-control price" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">الإجمالي</label>
            <input type="text" class="form-control item-total" readonly>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm remove-item mt-3">X</button>
        </div>
    </div>
</template>

<script>
    const productsData = <?php echo json_encode($products); ?>;
</script>
<script src="js/quote.js"></script>

<?php include 'includes/footer.php'; ?>