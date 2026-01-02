<?php require_once 'includes/auth_check.php'; ?>
<?php include 'includes/header.php'; ?>

<h1 class="text-center">نظام إنشاء مقايسات الكاميرات</h1>

<div class="card">
    <div class="card-header">
        الخطوة 1: بيانات العميل
    </div>
    <div class="card-body">
        <h5 class="card-title">إضافة عميل جديد</h5>
        <form action="actions/add_customer.php" method="POST">
            <div class="mb-3">
                <label for="customer_name" class="form-label">اسم العميل</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>
            <div class="mb-3">
                <label for="customer_phone" class="form-label">رقم الهاتف</label>
                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
            </div>
            <div class="mb-3">
                <label for="customer_address" class="form-label">العنوان</label>
                <textarea class="form-control" id="customer_address" name="customer_address" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">التالي <i class="bi bi-arrow-left"></i></button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>