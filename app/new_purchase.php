<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

// Fetch suppliers and products for the form
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">

<h1>تسجيل فاتورة شراء جديدة</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="actions/add_purchase.php">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">المورد</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">اختر مورد...</option>
                        <?php foreach($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="purchase_date" class="form-label">تاريخ الشراء</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <hr>
            <h5>بنود الفاتورة</h5>
            <div id="purchase_items_list">
                <!-- Dynamic items will be added here -->
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                     <select id="product-selector" placeholder="ابحث عن منتج لإضافته..."></select>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary">حفظ فاتورة الشراء</button>
        </form>
    </div>
</div>

<template id="item-template">
    <div class="row purchase-item mb-3 align-items-center">
        <input type="hidden" name="product_id[]" class="product-id">
        <div class="col-md-5">
            <label class="form-label">اسم المنتج</label>
            <input type="text" class="form-control product-name" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">الكمية</label>
            <input type="number" name="quantity[]" class="form-control" value="1" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">سعر الشراء (للقطعة)</label>
            <input type="number" step="0.01" name="unit_price[]" class="form-control" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm remove-item mt-3">X</button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsData = <?php echo json_encode($products); ?>;
    const itemsList = document.getElementById('purchase_items_list');
    const template = document.getElementById('item-template');

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
        const item = clone.querySelector('.purchase-item');
        item.querySelector('.product-id').value = product.id;
        item.querySelector('.product-name').value = product.name;
        itemsList.appendChild(item);
    }

    itemsList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.purchase-item').remove();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>