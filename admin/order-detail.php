<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT o.*, u.name, u.email FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE o.id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    exit('Order not found.');
}
$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();
admin_header('Order Detail', 'orders');
?>
<h1 style="margin-top:0">Order <?= e($order['order_number']) ?></h1>
<div class="split">
    <section class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Products</h2>
        <table class="table"><tr><th>Product</th><th>Qty</th><th>Price</th></tr><?php foreach ($items as $item): ?><tr><td><?= e($item['product_name']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= e(money((float) $item['price'])) ?></td></tr><?php endforeach; ?></table>
    </section>
    <aside class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Customer & Status</h2>
        <p><strong><?= e($order['name']) ?></strong><br><?= e($order['email']) ?><br><?= e($order['phone']) ?></p>
        <p><?= e($order['shipping_address']) ?>, <?= e($order['city']) ?>, <?= e($order['state']) ?>, <?= e($order['pincode']) ?></p>
        <form method="post" action="orders.php"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="status_update" value="1"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><select name="order_status"><option value="pending">Pending</option><option value="processing">Processing</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select><button class="btn" style="margin-top:12px" type="submit">Update Status</button></form>
    </aside>
</div>
<?php admin_footer(); ?>
