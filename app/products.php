<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/pagination.php';

if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

define('UPLOAD_DIR', 'uploads/products/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_product'])) {
        // Add/Update Product
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'] ?: null;
        $price = $_POST['price'];
        $image_name = $_POST['current_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
            move_uploaded_file($tmp_name, UPLOAD_DIR . $image_name);
        }

        if (empty($id)) {
            // Add new product
            $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssids", $name, $description, $category_id, $price, $image_name);
        } else {
            // Update existing product
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ?, price = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssidsi", $name, $description, $category_id, $price, $image_name, $id);
        }
        $stmt->execute();
        $stmt->close();

    } elseif (isset($_POST['delete_product'])) {
        // Delete Product
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: products.php");
    exit;
}


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
<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">إضافة منتج جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm" method="POST" action="products.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="productId">
                    <div class="mb-3">
                        <label for="productName" class="form-label">اسم المنتج</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">الوصف</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">القسم</label>
                        <select class="form-select" id="productCategory" name="category_id">
                            <option value="">اختر قسماً</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">السعر</label>
                        <input type="number" step="0.01" class="form-control" id="productPrice" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">صورة المنتج</label>
                        <input class="form-control" type="file" id="productImage" name="image">
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                         <button type="submit" name="save_product" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var productModal = document.getElementById('productModal');
    productModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var form = document.getElementById('productForm');
        var modalTitle = productModal.querySelector('.modal-title');
        var productId = document.getElementById('productId');

        var productData = button.getAttribute('data-product');

        if (productData) {
            // Edit mode
            productData = JSON.parse(productData);
            modalTitle.textContent = 'تعديل المنتج';
            productId.value = productData.id;
            document.getElementById('productName').value = productData.name;
            document.getElementById('productDescription').value = productData.description;
            document.getElementById('productCategory').value = productData.category_id;
            document.getElementById('productPrice').value = productData.price;
        } else {
            // Add mode
            modalTitle.textContent = 'إضافة منتج جديد';
            form.reset();
            productId.value = '';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>