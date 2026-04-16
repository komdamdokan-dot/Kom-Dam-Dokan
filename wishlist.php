<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $productId = (int) ($_POST['product_id'] ?? 0);
    if (isset($_POST['add'])) {
        db()->prepare('INSERT IGNORE INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())')->execute([$user['id'], $productId]);
    }
    if (isset($_POST['remove'])) {
        db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute([$user['id'], $productId]);
    }
    if (isset($_POST['move_to_cart'])) {
        add_to_cart($productId, 1);
    }
    redirect('wishlist.php');
}

$stmt = db()->prepare('SELECT w.*, p.name, p.image, p.price, p.discount_percent FROM wishlist w INNER JOIN products p ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.id DESC');
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();
$pageTitle = 'Wishlist';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="padding:22px">
    <h1 style="margin-top:0">Wishlist</h1>
    <?php foreach ($items as $item): ?>
        <div class="cart-item">
            <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>">
            <div>
                <strong><?= e($item['name']) ?></strong>
                <div class="muted"><?= e(money(sale_price((float) $item['price'], (float) $item['discount_percent']))) ?></div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>"><button class="btn secondary" name="move_to_cart" value="1" type="submit">Add to Cart</button></form>
                <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>"><button class="btn danger" name="remove" value="1" type="submit">Remove</button></form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
