<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update'])) {
    verify_csrf($_POST['_token'] ?? '');
    $stmt = db()->prepare('UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$_POST['order_status'] ?? 'pending', (int) ($_POST['order_id'] ?? 0)]);
    $info = db()->prepare('SELECT o.order_number, u.email FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $info->execute([(int) ($_POST['order_id'] ?? 0)]);
    if ($row = $info->fetch()) {
        sendOrderStatusEmail($row['email'], $row['order_number'], (string) ($_POST['order_status'] ?? 'pending'));
    }
    set_flash('success', 'Order status updated.');
    redirect('orders.php');
}

$where = ['1=1'];
$params = [];
if (!empty($_GET['status'])) {
    $where[] = 'o.order_status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['from'])) {
    $where[] = 'DATE(o.created_at) >= ?';
    $params[] = $_GET['from'];
}
if (!empty($_GET['to'])) {
    $where[] = 'DATE(o.created_at) <= ?';
    $params[] = $_GET['to'];
}
$sql = 'SELECT o.*, u.name FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
admin_header('Orders', 'orders');
?>
<h1 style="margin-top:0">Orders</h1>
<form method="get" class="form-grid admin-card" style="padding:16px;margin-bottom:18px"><select name="status"><option value="">All status</option><option value="pending">Pending</option><option value="processing">Processing</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select><input class="form-control" type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>"><input class="form-control" type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>"><button class="btn" type="submit">Filter</button></form>
<table class="table admin-card"><tr><th>Order</th><th>User</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr><?php foreach ($orders as $order): ?><tr><td><?= e($order['order_number']) ?></td><td><?= e($order['name']) ?></td><td><?= e(money((float) $order['net_amount'])) ?></td><td><?= e($order['order_status']) ?></td><td><?= e($order['created_at']) ?></td><td><a href="order-detail.php?id=<?= (int) $order['id'] ?>">View</a></td></tr><?php endforeach; ?></table>
<?php admin_footer(); ?>
