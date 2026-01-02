<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Fetch all customers for the selector
$customers = $conn->query("SELECT id, name, phone FROM customers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">

<h1 class="text-center">إنشاء مقايسة جديدة</h1>

<div class="card">
    <div class="card-header">
        الخطوة 1: اختيار العميل
    </div>
    <div class="card-body">
        <form action="add_site.php" method="GET">
            <div class="mb-3">
                <label for="customer_id" class="form-label">اختر عميل حالي أو ابحث عنه</label>
                <select id="customer-selector" name="customer_id" required>
                    <option value="">ابحث بالاسم أو رقم الهاتف...</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']) . ' - ' . htmlspecialchars($customer['phone']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">التالي <i class="bi bi-arrow-left"></i></button>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                أو إضافة عميل جديد
            </button>
        </form>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة عميل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add_customer.php" method="POST">
                 <div class="modal-body">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">اسم العميل</label>
                        <input type="text" class="form-control" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_phone" class="form-label">رقم الهاتف</label>
                        <input type="tel" class="form-control" name="customer_phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_address" class="form-label">العنوان</label>
                        <textarea class="form-control" name="customer_address" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="source" value="modal">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary">حفظ والمتابعة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#customer-selector', {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>