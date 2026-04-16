<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    exit('User not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    db()->prepare('UPDATE users SET name = ?, mobile = ?, status = ? WHERE id = ?')->execute([trim($_POST['name'] ?? ''), trim($_POST['mobile'] ?? ''), (int) ($_POST['status'] ?? 1), $id]);
    set_flash('success', 'User updated.');
    redirect('users.php');
}
$orderStmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$orderStmt->execute([$id]);
$orders = $orderStmt->fetchAll();
admin_header('Edit User', 'users');
?>
<h1 style="margin-top:0">Edit User</h1>
<div class="split">
    <section class="admin-card" style="padding:20px">
        <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><div class="form-grid"><div><label>Name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>"></div><div><label>Mobile</label><input class="form-control" name="mobile" value="<?= e($user['mobile']) ?>"></div><div><label>Status</label><select name="status"><option value="1" <?= (int) $user['status'] === 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= (int) $user['status'] === 0 ? 'selected' : '' ?>>Inactive</option></select></div></div><button class="btn" style="margin-top:16px" type="submit">Save User</button></form>
    </section>
    <aside class="admin-card" style="padding:20px"><h2 style="margin-top:0">Order History</h2><?php foreach ($orders as $order): ?><p><a href="order-detail.php?id=<?= (int) $order['id'] ?>"><?= e($order['order_number']) ?></a> - <?= e($order['order_status']) ?></p><?php endforeach; ?></aside>
</div>
<?php admin_footer(); ?>
