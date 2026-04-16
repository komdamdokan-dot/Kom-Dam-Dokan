<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();
$pageTitle = 'Order History';
require_once __DIR__ . '/includes/header.php';
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();
?>
<div class="card order-row">
    <h1 style="margin-top:0">Order History</h1>
    <table class="table">
        <tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= e($order['order_number']) ?></td>
                <td><?= e($order['created_at']) ?></td>
                <td><?= e(money((float) $order['net_amount'])) ?></td>
                <td><span class="status <?= e($order['order_status']) ?>"><?= e($order['order_status']) ?></span></td>
                <td><a href="order-details.php?id=<?= (int) $order['id'] ?>">Details</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
