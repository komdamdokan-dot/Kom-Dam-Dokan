<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();
$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();
if (!$order) {
    exit('Order not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    verify_csrf($_POST['_token'] ?? '');
    if (in_array($order['order_status'], ['pending', 'processing'], true)) {
        db()->prepare('UPDATE orders SET order_status = "cancelled", updated_at = NOW() WHERE id = ?')->execute([$orderId]);
        sendOrderStatusEmail($user['email'], $order['order_number'], 'cancelled');
        set_flash('success', 'Order cancelled.');
        redirect('order-details.php?id=' . $orderId);
    }
}

$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
$pageTitle = 'Order Details';
require_once __DIR__ . '/includes/header.php';
$timeline = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
?>
<div class="split">
    <section class="card" style="padding:22px">
        <h1 style="margin-top:0">Order <?= e($order['order_number']) ?></h1>
        <p><span class="status <?= e($order['order_status']) ?>"><?= e($order['order_status']) ?></span></p>
        <table class="table">
            <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
            <?php foreach ($items as $item): ?>
                <tr><td><?= e($item['product_name']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= e(money((float) $item['price'])) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <p><strong>Shipping:</strong> <?= e($order['shipping_address']) ?>, <?= e($order['city']) ?>, <?= e($order['state']) ?>, <?= e($order['pincode']) ?></p>
        <?php if (in_array($order['order_status'], ['pending', 'processing'], true)): ?>
            <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="cancel_order" value="1"><button class="btn danger" type="submit">Cancel Order</button></form>
        <?php endif; ?>
    </section>
    <aside class="card" style="padding:22px">
        <h3 style="margin-top:0">Status Timeline</h3>
        <div class="timeline">
            <?php foreach ($timeline as $status): ?>
                <div class="timeline-item"><?= e(ucfirst($status)) ?><?= $status === $order['order_status'] ? ' - current' : '' ?></div>
            <?php endforeach; ?>
        </div>
    </aside>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
