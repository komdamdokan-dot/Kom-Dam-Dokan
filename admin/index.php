<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
admin_header('Dashboard', 'dashboard');

$totalOrders = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users WHERE is_admin = 0')->fetchColumn();
$totalProducts = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalRevenue = (float) db()->query('SELECT COALESCE(SUM(net_amount),0) FROM orders WHERE order_status = "delivered"')->fetchColumn();
$recentOrders = db()->query('SELECT * FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();
$salesRows = db()->query('SELECT DATE_FORMAT(created_at, "%b") AS month_name, COALESCE(SUM(net_amount),0) AS total FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)')->fetchAll();
?>
<h1 style="margin-top:0">Dashboard</h1>
<div class="stats">
    <div class="admin-card stat"><h3>Total Orders</h3><p><?= $totalOrders ?></p></div>
    <div class="admin-card stat"><h3>Total Users</h3><p><?= $totalUsers ?></p></div>
    <div class="admin-card stat"><h3>Total Products</h3><p><?= $totalProducts ?></p></div>
    <div class="admin-card stat"><h3>Total Revenue</h3><p><?= e(money($totalRevenue)) ?></p></div>
</div>
<div class="split" style="margin-top:20px">
    <section class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Recent Orders</h2>
        <table class="table"><tr><th>Order</th><th>Status</th><th>Total</th></tr><?php foreach ($recentOrders as $order): ?><tr><td><a href="order-detail.php?id=<?= (int) $order['id'] ?>"><?= e($order['order_number']) ?></a></td><td><?= e($order['order_status']) ?></td><td><?= e(money((float) $order['net_amount'])) ?></td></tr><?php endforeach; ?></table>
    </section>
    <aside class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Monthly Sales</h2>
        <canvas id="salesChart" height="240"></canvas>
    </aside>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {type:'bar',data:{labels:<?= json_encode(array_column($salesRows, 'month_name')) ?>,datasets:[{label:'Sales',data:<?= json_encode(array_map('floatval', array_column($salesRows, 'total'))) ?>,backgroundColor:'#d97904'}]},options:{responsive:true,plugins:{legend:{display:false}}}});
</script>
<?php admin_footer(); ?>
