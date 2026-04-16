<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Your Cart';
require_once __DIR__ . '/includes/header.php';
$items = cart_items();
$totals = cart_totals();
?>
<section class="split">
    <div class="card cart-row">
        <h1 style="margin-top:0">Shopping Cart</h1>
        <?php if (!$items): ?>
            <p class="muted">Your cart is empty.</p>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <div class="cart-item">
                <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>">
                <div>
                    <strong><?= e($item['name']) ?></strong>
                    <div class="muted">Unit <?= e(money(sale_price((float) $item['price'], (float) $item['discount_percent']))) ?></div>
                </div>
                <div>
                    <form method="post" data-cart-action data-reload="1">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                        <div class="qty-box">
                            <input type="number" min="1" max="10" name="quantity" value="<?= (int) $item['quantity'] ?>">
                        </div>
                        <button class="btn secondary" style="margin-top:8px" type="submit">Update</button>
                    </form>
                    <form method="post" data-cart-action data-reload="1" style="margin-top:8px">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                        <button class="btn danger" type="submit">Remove</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <aside class="card totals">
        <h3 style="margin-top:0">Order Summary</h3>
        <p>Subtotal: <strong><?= e(money($totals['subtotal'])) ?></strong></p>
        <p>Delivery: <strong><?= e(money($totals['delivery'])) ?></strong></p>
        <p>Total: <strong><?= e(money($totals['total'])) ?></strong></p>
        <a class="btn" href="<?= base_url('checkout.php') ?>">Proceed to Checkout</a>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
