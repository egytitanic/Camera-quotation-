<?php
session_start();
// If the user is not coming from the first step, redirect them back
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        الخطوة 2: بيانات الموقع
    </div>
    <div class="card-body">
        <h5 class="card-title">إضافة موقع جديد</h5>
        <form action="actions/add_site.php" method="POST">
            <div class="mb-3">
                <label for="site_name" class="form-label">اسم الموقع</label>
                <input type="text" class="form-control" id="site_name" name="site_name" required>
            </div>
            <div class="mb-3">
                <label for="site_address" class="form-label">عنوان الموقع</label>
                <textarea class="form-control" id="site_address" name="site_address" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="site_location" class="form-label">رابط الموقع على الخريطة (Location)</label>
                <input type="text" class="form-control" id="site_location" name="site_location" placeholder="e.g., Google Maps URL">
            </div>
            <button type="submit" class="btn btn-primary">التالي <i class="bi bi-arrow-left"></i></button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>