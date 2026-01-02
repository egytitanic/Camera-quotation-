<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الأعمال</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">كتالوج المنتجات</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="catalog.php">الكتالوج</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> مرحباً، <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                             <li><a class="dropdown-item" href="dashboard.php">لوحة التحكم</a></li>
                            <li><a class="dropdown-item" href="profile.php">الملف الشخصي</a></li>
                            <?php if ($_SESSION['role'] == 'manager'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">الإدارة</li>
                                <li><a class="dropdown-item" href="products.php">المخزون</a></li>
                                <li><a class="dropdown-item" href="invoices.php">الفواتير</a></li>
                                <li><a class="dropdown-item" href="purchases.php">المشتريات</a></li>
                                <li><a class="dropdown-item" href="suppliers.php">الموردين</a></li>
                                <li><a class="dropdown-item" href="users.php">المستخدمين</a></li>
                                <li><a class="dropdown-item" href="reports.php">التقارير</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                           <i class="bi bi-box-arrow-in-right"></i> تسجيل دخول الموظفين
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">