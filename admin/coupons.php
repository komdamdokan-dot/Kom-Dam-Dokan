<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    db()->prepare('INSERT INTO coupons (code, discount_type, discount_value, min_order, max_discount, expiry_date, usage_limit, used_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)')->execute([
        strtoupper(trim($_POST['code'] ?? '')),
        $_POST['discount_type'] ?? 'fixed',
        (float) ($_POST['discount_value'] ?? 0),
        (float) ($_POST['min_order'] ?? 0),
        (float) ($_POST['max_discount'] ?? 0),
        $_POST['expiry_date'] ?? date('Y-m-d'),
        (int) ($_POST['usage_limit'] ?? 1),
    ]);
    set_flash('success', 'Coupon saved.');
    redirect('coupons.php');
}
$coupons = db()->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
admin_header('Coupons', 'coupons');
?>
<h1 style="margin-top:0">Coupons</h1>
<div class="split">
    <section class="admin-card" style="padding:20px"><form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><div class="form-grid"><div><label>Code</label><input class="form-control" name="code" required></div><div><label>Type</label><select name="discount_type"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div><div><label>Value</label><input class="form-control" type="number" step="0.01" name="discount_value"></div><div><label>Min Order</label><input class="form-control" type="number" step="0.01" name="min_order"></div><div><label>Max Discount</label><input class="form-control" type="number" step="0.01" name="max_discount"></div><div><label>Expiry</label><input class="form-control" type="date" name="expiry_date"></div><div><label>Usage Limit</label><input class="form-control" type="number" name="usage_limit"></div></div><button class="btn" style="margin-top:16px" type="submit">Create Coupon</button></form></section>
    <aside class="admin-card" style="padding:20px"><h2 style="margin-top:0">Existing Coupons</h2><?php foreach ($coupons as $coupon): ?><p><?= e($coupon['code']) ?> - <?= e($coupon['discount_type']) ?> - used <?= (int) $coupon['used_count'] ?>/<?= (int) $coupon['usage_limit'] ?></p><?php endforeach; ?></aside>
</div>
<?php admin_footer(); ?>
