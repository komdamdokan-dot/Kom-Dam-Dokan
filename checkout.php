<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();
$items = cart_items();
$totals = cart_totals();

if (!$items) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $orderNumber = generate_order_number();
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, order_number, total_amount, shipping_charge, coupon_discount, net_amount, shipping_address, city, state, pincode, phone, order_note, payment_method, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "COD", "pending", NOW())');
        $stmt->execute([
            $user['id'],
            $orderNumber,
            $totals['subtotal'],
            $totals['delivery'],
            $totals['coupon_discount'],
            $totals['total'],
            trim($_POST['shipping_address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['pincode'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['order_note'] ?? ''),
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity, variant) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['product_id'], $item['name'], sale_price((float) $item['price'], (float) $item['discount_percent']), $item['quantity'], null]);
            $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?')->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$user['id']]);
        $pdo->commit();

        sendOrderPlacedEmail($user['email'], $orderNumber);
        sendOrderPlacedEmail(setting('contact_email', 'komdamdokan@gmail.com'), $orderNumber);
        redirect('order-success.php?order=' . urlencode($orderNumber));
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('error', 'Order could not be placed.');
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>
<div class="split">
    <section class="card" style="padding:22px">
        <h1 style="margin-top:0">Checkout</h1>
        <form method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <div class="form-grid">
                <div><label>House Address</label><textarea name="shipping_address" rows="4" required><?= e($user['address']) ?></textarea></div>
                <div>
                    <label>City</label><input class="form-control" name="city" value="<?= e($user['city']) ?>" required>
                    <label style="margin-top:12px">District/State</label><input class="form-control" name="state" value="<?= e($user['state']) ?>" required>
                    <label style="margin-top:12px">Pincode</label><input class="form-control" name="pincode" value="<?= e($user['pincode']) ?>" required>
                </div>
                <div><label>Phone</label><input class="form-control" name="phone" value="<?= e($user['mobile']) ?>" required></div>
                <div><label>Order Note</label><textarea name="order_note" rows="4"></textarea></div>
            </div>
            <p style="margin-top:16px"><strong>Payment Method:</strong> Cash on Delivery</p>
            <button class="btn" type="submit">Place Order</button>
        </form>
    </section>
    <aside class="card totals">
        <h3 style="margin-top:0">Summary</h3>
        <p>Subtotal: <strong><?= e(money($totals['subtotal'])) ?></strong></p>
        <p>Shipping: <strong><?= e(money($totals['delivery'])) ?></strong></p>
        <p>Net: <strong><?= e(money($totals['total'])) ?></strong></p>
    </aside>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
