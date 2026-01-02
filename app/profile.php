<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

$message = '';
$message_type = ''; // 'success' or 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        $message = 'كلمات المرور الجديدة غير متطابقة.';
        $message_type = 'danger';
    } else {
        // Fetch current user's hashed password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $message = 'تم تحديث كلمة المرور بنجاح.';
                $message_type = 'success';
            } else {
                $message = 'حدث خطأ أثناء تحديث كلمة المرور.';
                $message_type = 'danger';
            }
        } else {
            $message = 'كلمة المرور الحالية غير صحيحة.';
            $message_type = 'danger';
        }
    }
}

include 'includes/header.php';
?>

<h1>الملف الشخصي</h1>
<p class="lead">عرض تفاصيل حسابك وتغيير كلمة المرور الخاصة بك.</p>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>تفاصيل الحساب</h4>
            </div>
            <div class="card-body">
                <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>الصلاحية:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>تغيير كلمة المرور</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="profile.php">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">تحديث كلمة المرور</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>