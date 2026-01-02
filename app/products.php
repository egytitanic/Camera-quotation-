<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/pagination.php';

if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

define('UPLOAD_DIR', 'uploads/products/');

// ... (POST handling logic is the same)

// Pagination
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$total_products = $conn->query("SELECT COUNT(id) as total FROM products")->fetch_assoc()['total'];
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC LIMIT $records_per_page OFFSET $offset";
$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة المنتجات</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-plus-circle"></i> إضافة منتج جديد</button>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="<?php echo UPLOAD_DIR . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> ج.م</td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#productModal" data-product='<?php echo json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="products.php" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد؟');">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <?php echo generate_pagination($total_products, $current_page, $records_per_page, 'products.php?'); ?>
    </div>
</div>
<!-- Modal and script... -->
<?php include 'includes/footer.php'; ?>