<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}
$quote_id = $_GET['id'];

// Fetch quote data to edit
$stmt = $conn->prepare("SELECT q.*, s.name as site_name, c.name as customer_name
                        FROM quotes q
                        JOIN sites s ON q.site_id = s.id
                        JOIN customers c ON s.customer_id = c.id
                        WHERE q.id = ? AND q.status = 'pending'");
$stmt->bind_param("i", $quote_id);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();

if (!$quote) {
    // Quote not found or already invoiced
    header("Location: dashboard.php");
    exit;
}

// Fetch quote items
$items = $conn->query("SELECT qi.*, p.name as product_name FROM quote_items qi JOIN products p ON qi.product_id = p.id WHERE qi.quote_id = $quote_id")->fetch_all(MYSQLI_ASSOC);

// Fetch all products for the selector
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);


include 'includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">

<div class="card">
    <div class="card-header">
        <h1>تعديل المقايسة #<?php echo $quote_id; ?></h1>
        <p class="lead">الموقع: "<?php echo htmlspecialchars($quote['site_name']); ?>" (العميل: <?php echo htmlspecialchars($quote['customer_name']); ?>)</p>
    </div>
    <div class="card-body">
        <form action="actions/update_quote.php" method="POST">
            <input type="hidden" name="quote_id" value="<?php echo $quote_id; ?>">
            <div id="equipment_list">
                <?php foreach($items as $item): ?>
                <div class="row equipment-item mb-3 align-items-center">
                    <input type="hidden" name="product_id[]" class="product-id" value="<?php echo $item['product_id']; ?>">
                    <div class="col-md-4">
                        <label class="form-label">اسم البند</label>
                        <input type="text" name="product_description[]" class="form-control product-description" value="<?php echo htmlspecialchars($item['description']); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity[]" class="form-control quantity" value="<?php echo $item['quantity']; ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">السعر (للقطعة)</label>
                        <input type="number" step="0.01" name="price[]" class="form-control price" value="<?php echo $item['price']; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الإجمالي</label>
                        <input type="text" class="form-control item-total" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item mt-3">X</button>
                    </div>
                </div>
                <?php endforeach; ?>
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
                    <input type="number" step="0.01" name="installation_expenses" class="form-control" value="<?php echo $quote['installation_expenses']; ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-warning">تحديث المقايسة</button>
            <a href="dashboard.php" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>

<template id="item-template">
    <!-- Same template as in add_equipment.php -->
    <div class="row equipment-item mb-3 align-items-center">
        <input type="hidden" name="product_id[]" class="product-id">
        <div class="col-md-4"><label class="form-label">اسم البند</label><input type="text" name="product_description[]" class="form-control product-description" required></div>
        <div class="col-md-2"><label class="form-label">الكمية</label><input type="number" name="quantity[]" class="form-control quantity" value="1" required></div>
        <div class="col-md-2"><label class="form-label">السعر (للقطعة)</label><input type="number" step="0.01" name="price[]" class="form-control price" required></div>
        <div class="col-md-3"><label class="form-label">الإجمالي</label><input type="text" class="form-control item-total" readonly></div>
        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-item mt-3">X</button></div>
    </div>
</template>

<script>
// Same script as in add_equipment.php to handle dynamic items
document.addEventListener('DOMContentLoaded', function() {
    const productsData = <?php echo json_encode($products); ?>;
    const equipmentList = document.getElementById('equipment_list');
    const template = document.getElementById('item-template');

    // Initialize totals for existing items
    equipmentList.querySelectorAll('.equipment-item').forEach(item => updateItemTotal(item));

    new TomSelect('#product-selector', {
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: productsData,
        create: false,
        onChange: function(value) {
            if (!value) return;
            const product = productsData.find(p => p.id == value);
            if (product) {
                addItem(product);
            }
            this.clear();
        }
    });

    function addItem(product) {
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.equipment-item');
        item.querySelector('.product-id').value = product.id;
        item.querySelector('.product-description').value = product.name;
        item.querySelector('.price').value = product.price;
        equipmentList.appendChild(item);
        updateItemTotal(item);
    }

    equipmentList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.equipment-item').remove();
        }
    });

    equipmentList.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            const item = e.target.closest('.equipment-item');
            updateItemTotal(item);
        }
    });

    function updateItemTotal(item) {
        const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
        const price = parseFloat(item.querySelector('.price').value) || 0;
        const total = quantity * price;
        item.querySelector('.item-total').value = total.toFixed(2) + ' ج.م';
    }
});
</script>


<?php include 'includes/footer.php'; ?>