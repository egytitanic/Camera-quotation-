<?php
require_once 'includes/auth_check.php';

// Session management for quote creation flow
if (!isset($_SESSION['site_id'])) {
    header("Location: new_quote_customer.php"); // Redirect to the start of the flow
    exit();
}
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        الخطوة 3: إضافة المعدات والأدوات
    </div>
    <div class="card-body">
        <h5 class="card-title">إضافة أصناف للمقايسة</h5>
        <form action="actions/add_equipment.php" method="POST">
            <div id="equipment_list">
                <div class="row equipment-item mb-3">
                    <div class="col-md-5">
                        <label class="form-label">وصف البند</label>
                        <input type="text" name="product_description[]" class="form-control" placeholder="مثال: كاميرا خارجية 5 ميجا بكسل" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity[]" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">السعر (للقطعة)</label>
                        <input type="number" step="0.01" name="price[]" class="form-control" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item" disabled>X</button>
                    </div>
                </div>
            </div>
            <button type="button" id="add_item" class="btn btn-secondary mb-3"><i class="bi bi-plus"></i> إضافة بند آخر</button>
            <hr>
            <button type="submit" class="btn btn-success">إنشاء المقايسة النهائية <i class="bi bi-check-lg"></i></button>
        </form>
    </div>
</div>

<script>
document.getElementById('add_item').addEventListener('click', function() {
    const list = document.getElementById('equipment_list');
    const firstItem = list.querySelector('.equipment-item');
    const newItem = firstItem.cloneNode(true);

    // Clear input values in the new cloned item
    newItem.querySelectorAll('input').forEach(input => input.value = '');
    // Set default quantity to 1
    newItem.querySelector('input[name="quantity[]"]').value = '1';

    // Enable the remove button on the new item
    const removeBtn = newItem.querySelector('.remove-item');
    removeBtn.disabled = false;
    removeBtn.addEventListener('click', function() {
        newItem.remove();
    });

    list.appendChild(newItem);

    // Also enable the remove button on the first item if it's not the only one
    if (list.children.length > 1) {
        firstItem.querySelector('.remove-item').disabled = false;
    }
});

// Add event listener for the first remove button (which is initially disabled)
const firstRemoveBtn = document.querySelector('.remove-item');
firstRemoveBtn.addEventListener('click', function() {
    if (document.querySelectorAll('.equipment-item').length > 1) {
        firstRemoveBtn.closest('.equipment-item').remove();
    }
});
</script>

<?php include 'includes/footer.php'; ?>