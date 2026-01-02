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
    // Add new category
    if (isset($_POST['add_category'])) {
        $name = $_POST['category_name'];
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
    }
    // Edit category
    elseif (isset($_POST['edit_category'])) {
        $id = $_POST['category_id'];
        $name = $_POST['category_name'];
        if (!empty($name) && !empty($id)) {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
        }
    }
    // Delete category
    elseif (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    // Redirect to avoid form resubmission
    header("Location: categories.php");
    exit;
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة الأقسام</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-circle"></i> إضافة قسم جديد
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>اسم القسم</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCategoryModal"
                                    data-id="<?php echo $category['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                <i class="bi bi-pencil"></i> تعديل
                            </button>
                            <form method="POST" action="categories.php" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا القسم؟');">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" name="delete_category" class="btn btn-danger btn-sm">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة قسم جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="categories.php">
                    <div class="mb-3">
                        <label for="category_name_add" class="form-label">اسم القسم</label>
                        <input type="text" class="form-control" id="category_name_add" name="category_name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" name="add_category" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل القسم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="categories.php">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">اسم القسم</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" name="edit_category" class="btn btn-warning">تحديث</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Script to populate the edit modal with category data
document.addEventListener('DOMContentLoaded', () => {
    const editCategoryModal = document.getElementById('editCategoryModal');
    editCategoryModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        const modalTitle = editCategoryModal.querySelector('.modal-title');
        const modalBodyInputId = editCategoryModal.querySelector('#edit_category_id');
        const modalBodyInputName = editCategoryModal.querySelector('#edit_category_name');

        modalTitle.textContent = 'تعديل القسم: ' + name;
        modalBodyInputId.value = id;
        modalBodyInputName.value = name;
    });
});
</script>

<?php include 'includes/footer.php'; ?>