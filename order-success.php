<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Order Success';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Order Placed</h1>
    <p>Your order <strong><?= e($_GET['order'] ?? '') ?></strong> has been placed successfully.</p>
    <p><a class="btn" href="order-history.php">View My Orders</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
