<?php
require_once 'includes/db.php';
require_once 'config.php'; // Include the new config file

define('UPLOAD_DIR', 'uploads/products/');

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC";
$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1>كتالوج المنتجات</h1>
        <p class="lead">تصفح أحدث المنتجات والحلول التي نقدمها.</p>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <?php
                    $image_path = UPLOAD_DIR . ($product['image'] ?? '');
                    if (!file_exists($image_path) || empty($product['image'])) {
                        $image_path = 'https://via.placeholder.com/300x200.png?text=No+Image';
                    }
                    ?>
                    <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5 text-primary"><?php echo number_format($product['price'], 2); ?> ج.م</span>
                            <?php
                                $product_name_encoded = urlencode("مرحبا، أنا مهتم بالمنتج: " . $product['name']);
                                $whatsapp_url = "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . $product_name_encoded;
                            ?>
                            <a href="<?php echo $whatsapp_url; ?>" class="btn btn-success" target="_blank">
                                <i class="bi bi-whatsapp"></i> تواصل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>