<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Session management for quote creation flow
if (!isset($_SESSION['site_id'])) {
    header("Location: new_quote_customer.php");
    exit;
}

// Fetch all products for the selector
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">


<div class="card">
    <div class="card-header">
        الخطوة 3: إضافة بنود المقايسة من المخزون
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
document.addEventListener('DOMContentLoaded', function() {
    const productsData = <?php echo json_encode($products); ?>;
    const equipmentList = document.getElementById('equipment_list');
    const template = document.getElementById('item-template');

    const tomSelect = new TomSelect('#product-selector', {
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
            this.clear(); // Clear the selector after adding
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