<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    header("Location: dashboard.php");
    exit;
}

// Handle POST requests for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new product
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $description, $price, $category_id);
        $stmt->execute();
    }
    // Edit product
    elseif (isset($_POST['edit_product'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $name, $description, $price, $category_id, $id);
        $stmt->execute();
    }
    // Delete product
    elseif (isset($_POST['delete_product'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: products.php");
    exit;
}

// Fetch all products with category names
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.name ASC";
$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Fetch all categories for the dropdowns in modals
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة المنتجات (المخزون)</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
        <i class="bi bi-plus-circle"></i> إضافة منتج جديد
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>اسم المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> ج.م</td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#productModal"
                                    data-product='<?php echo json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                <i class="bi bi-pencil"></i> تعديل
                            </button>
                            <form method="POST" action="products.php" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المنتج؟');">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="products.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">إضافة منتج جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="product_id" name="id">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">اسم المنتج</label>
                        <input type="text" class="form-control" id="product_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_description" class="form-label">الوصف</label>
                        <textarea class="form-control" id="product_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="product_price" class="form-label">السعر الافتراضي</label>
                            <input type="number" step="0.01" class="form-control" id="product_price" name="price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="product_category" class="form-label">القسم</label>
                            <select class="form-select" id="product_category" name="category_id" required>
                                <option value="">اختر قسم...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" name="add_product" id="addProductBtn" class="btn btn-primary">حفظ</button>
                    <button type="submit" name="edit_product" id="editProductBtn" class="btn btn-warning">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const productModal = document.getElementById('productModal');

    productModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const productData = button.getAttribute('data-product');

        const modalTitle = productModal.querySelector('#productModalLabel');
        const addBtn = productModal.querySelector('#addProductBtn');
        const editBtn = productModal.querySelector('#editProductBtn');
        const form = productModal.querySelector('form');

        // Reset form for adding new product
        form.reset();
        productModal.querySelector('#product_id').value = '';
        modalTitle.textContent = 'إضافة منتج جديد';
        addBtn.style.display = 'inline-block';
        editBtn.style.display = 'none';

        // If editing, populate form with product data
        if (productData) {
            const product = JSON.parse(productData);
            modalTitle.textContent = 'تعديل المنتج: ' + product.name;
            addBtn.style.display = 'none';
            editBtn.style.display = 'inline-block';

            productModal.querySelector('#product_id').value = product.id;
            productModal.querySelector('#product_name').value = product.name;
            productModal.querySelector('#product_description').value = product.description;
            productModal.querySelector('#product_price').value = product.price;
            productModal.querySelector('#product_category').value = product.category_id;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>