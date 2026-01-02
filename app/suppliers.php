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
    // Add new supplier
    if (isset($_POST['add_supplier'])) {
        $name = $_POST['name'];
        $contact_person = $_POST['contact_person'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $contact_person, $phone, $email);
        $stmt->execute();
    }
    // Edit supplier
    elseif (isset($_POST['edit_supplier'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $contact_person = $_POST['contact_person'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $contact_person, $phone, $email, $id);
        $stmt->execute();
    }
    // Delete supplier
    elseif (isset($_POST['delete_supplier'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: suppliers.php");
    exit;
}

// Fetch all suppliers
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة الموردين</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal">
        <i class="bi bi-plus-circle"></i> إضافة مورد جديد
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>اسم المورد</th>
                    <th>الشخص المسؤول</th>
                    <th>الهاتف</th>
                    <th>البريد الإلكتروني</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#supplierModal"
                                    data-supplier='<?php echo json_encode($supplier, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                <i class="bi bi-pencil"></i> تعديل
                            </button>
                            <form method="POST" action="suppliers.php" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المورد؟');">
                                <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                                <button type="submit" name="delete_supplier" class="btn btn-danger btn-sm">
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

<!-- Add/Edit Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="suppliers.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">إضافة مورد جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="supplier_id" name="id">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">اسم المورد</label>
                        <input type="text" class="form-control" id="supplier_name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_contact" class="form-label">الشخص المسؤول</label>
                            <input type="text" class="form-control" id="supplier_contact" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplier_phone" class="form-label">الهاتف</label>
                            <input type="tel" class="form-control" id="supplier_phone" name="phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="supplier_email" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" name="add_supplier" id="addSupplierBtn" class="btn btn-primary">حفظ</button>
                    <button type="submit" name="edit_supplier" id="editSupplierBtn" class="btn btn-warning">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const supplierModal = document.getElementById('supplierModal');

    supplierModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const supplierData = button.getAttribute('data-supplier');

        const modalTitle = supplierModal.querySelector('#supplierModalLabel');
        const addBtn = supplierModal.querySelector('#addSupplierBtn');
        const editBtn = supplierModal.querySelector('#editSupplierBtn');
        const form = supplierModal.querySelector('form');

        form.reset();
        supplierModal.querySelector('#supplier_id').value = '';
        modalTitle.textContent = 'إضافة مورد جديد';
        addBtn.style.display = 'inline-block';
        editBtn.style.display = 'none';

        if (supplierData) {
            const supplier = JSON.parse(supplierData);
            modalTitle.textContent = 'تعديل المورد: ' + supplier.name;
            addBtn.style.display = 'none';
            editBtn.style.display = 'inline-block';

            supplierModal.querySelector('#supplier_id').value = supplier.id;
            supplierModal.querySelector('#supplier_name').value = supplier.name;
            supplierModal.querySelector('#supplier_contact').value = supplier.contact_person;
            supplierModal.querySelector('#supplier_phone').value = supplier.phone;
            supplierModal.querySelector('#supplier_email').value = supplier.email;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>