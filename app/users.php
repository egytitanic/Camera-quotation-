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
    // Add new user
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        $stmt->execute();
    }
    // Edit user
    elseif (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $password, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
        $stmt->execute();
    }
    // Delete user
    elseif (isset($_POST['delete_user'])) {
        $id = $_POST['id'];
        // Prevent manager from deleting themselves
        if ($id != $_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
    header("Location: users.php");
    exit;
}

// Fetch all users
$users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>إدارة المستخدمين</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
        <i class="bi bi-plus-circle"></i> إضافة مستخدم جديد
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>اسم المستخدم</th>
                    <th>الصلاحية</th>
                    <th>تاريخ الإنشاء</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo $user['role'] == 'manager' ? 'مدير' : 'موظف'; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#userModal"
                                    data-user='<?php echo json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                <i class="bi bi-pencil"></i> تعديل
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): // Prevent deleting self ?>
                            <form method="POST" action="users.php" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المستخدم؟');">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> حذف
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="users.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">إضافة مستخدم جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="id">
                    <div class="mb-3">
                        <label for="user_username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control" id="user_username" name="username" required>
                    </div>
                     <div class="mb-3">
                        <label for="user_password" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" id="user_password" name="password">
                        <small class="form-text text-muted" id="passwordHelp">اتركه فارغاً لعدم تغيير كلمة المرور عند التعديل.</small>
                    </div>
                    <div class="mb-3">
                        <label for="user_role" class="form-label">الصلاحية</label>
                        <select class="form-select" id="user_role" name="role" required>
                            <option value="employee">موظف</option>
                            <option value="manager">مدير</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" name="add_user" id="addUserBtn" class="btn btn-primary">حفظ</button>
                    <button type="submit" name="edit_user" id="editUserBtn" class="btn btn-warning">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const userModal = document.getElementById('userModal');

    userModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const userData = button.getAttribute('data-user');

        const modalTitle = userModal.querySelector('#userModalLabel');
        const addBtn = userModal.querySelector('#addUserBtn');
        const editBtn = userModal.querySelector('#editUserBtn');
        const passwordInput = userModal.querySelector('#user_password');
        const passwordHelp = userModal.querySelector('#passwordHelp');
        const form = userModal.querySelector('form');

        form.reset();
        userModal.querySelector('#user_id').value = '';
        modalTitle.textContent = 'إضافة مستخدم جديد';
        addBtn.style.display = 'inline-block';
        editBtn.style.display = 'none';
        passwordInput.required = true;
        passwordHelp.style.display = 'none';

        if (userData) {
            const user = JSON.parse(userData);
            modalTitle.textContent = 'تعديل المستخدم: ' + user.username;
            addBtn.style.display = 'none';
            editBtn.style.display = 'inline-block';
            passwordInput.required = false;
            passwordHelp.style.display = 'block';

            userModal.querySelector('#user_id').value = user.id;
            userModal.querySelector('#user_username').value = user.username;
            userModal.querySelector('#user_role').value = user.role;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>