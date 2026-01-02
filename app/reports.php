<?php
require_once 'includes/auth_check.php';

// This page is for managers only
if ($_SESSION['role'] !== 'manager') {
    // Redirect non-managers to the dashboard
    header("Location: dashboard.php");
    exit;
}

include 'includes/header.php';
?>

<h1>مركز التقارير</h1>
<p class="lead">اختر التقرير الذي ترغب في عرضه.</p>

<div class="list-group">
    <a href="reports/sales_summary.php" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">تقرير ملخص المبيعات</h5>
        </div>
        <p class="mb-1">عرض ملخص لقيم المقايسات الإجمالية خلال فترة زمنية محددة.</p>
    </a>
    <a href="reports/employee_performance.php" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">تقرير أداء الموظفين</h5>
        </div>
        <p class="mb-1">عرض عدد وقيمة المقايسات التي أنشأها كل موظف.</p>
    </a>
</div>

<?php include 'includes/footer.php'; ?>