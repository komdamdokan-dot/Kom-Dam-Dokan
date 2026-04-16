<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$productId = (int) ($_GET['id'] ?? 0);
$product = find_product($productId);
if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (isset($_POST['add_review'])) {
        require_login();
        $user = current_user();
        if ($user && can_review_product((int) $user['id'], $productId)) {
            $stmt = db()->prepare('INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, "approved", NOW())');
            $stmt->execute([$productId, $user['id'], max(1, min(5, (int) $_POST['rating'])), trim($_POST['comment'] ?? '')]);
            refresh_product_rating($productId);
            set_flash('success', 'Review submitted.');
        } else {
            set_flash('error', 'Only delivered order customers can review this product.');
        }
        redirect('product.php?id=' . $productId);
    }
}

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
$reviewsStmt = db()->prepare('SELECT r.*, u.name FROM reviews r INNER JOIN users u ON u.id = r.user_id WHERE r.product_id = ? AND r.status = "approved" ORDER BY r.id DESC LIMIT 10');
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll();
$related = related_products((int) $product['cat_id'], $productId);
$sale = sale_price((float) $product['price'], (float) $product['discount_percent']);
$user = current_user();
?>
<div class="split">
    <section class="card" style="padding:22px">
        <div class="split">
            <div><img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>"></div>
            <div>
                <span class="badge">-<?= (float) $product['discount_percent'] ?>%</span>
                <h1><?= e($product['name']) ?></h1>
                <p class="muted"><?= nl2br(e($product['description'])) ?></p>
                <div class="rating">Average rating <?= e((string) $product['rating_avg']) ?> / 5 from <?= (int) $product['rating_count'] ?> reviews</div>
                <div class="price-row"><span class="price-new"><?= e(money($sale)) ?></span><span class="price-old"><?= e(money((float) $product['price'])) ?></span></div>
                <p><strong>Stock:</strong> <?= (int) $product['stock'] ?></p>
                <form method="post" data-cart-action>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <label>Quantity</label>
                    <select name="quantity" style="max-width:120px;margin-bottom:12px">
                        <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                    </select>
                    <button class="btn" type="submit">Add to Cart</button>
                </form>
            </div>
        </div>
    </section>
    <aside class="card" style="padding:22px">
        <h3 style="margin-top:0">Related Products</h3>
        <?php foreach ($related as $item): ?>
            <div style="display:grid;grid-template-columns:64px 1fr;gap:10px;margin-bottom:12px;align-items:center">
                <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>">
                <div>
                    <a href="product.php?id=<?= (int) $item['id'] ?>"><strong><?= e($item['name']) ?></strong></a>
                    <div class="muted"><?= e(money(sale_price((float) $item['price'], (float) $item['discount_percent']))) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </aside>
</div>
<section class="card" style="padding:22px;margin-top:20px">
    <div class="section-title"><h2>Reviews</h2></div>
    <?php foreach ($reviews as $review): ?>
        <div style="padding:12px 0;border-bottom:1px solid var(--border)">
            <strong><?= e($review['name']) ?></strong>
            <div class="rating"><?= str_repeat('★', (int) $review['rating']) ?></div>
            <p style="margin:6px 0 0"><?= e($review['comment']) ?></p>
        </div>
    <?php endforeach; ?>
    <?php if ($user && can_review_product((int) $user['id'], $productId)): ?>
        <form method="post" style="margin-top:18px">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="add_review" value="1">
            <div class="form-grid">
                <div>
                    <label>Rating</label>
                    <select name="rating"><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></select>
                </div>
                <div>
                    <label>Comment</label>
                    <textarea name="comment" rows="4"></textarea>
                </div>
            </div>
            <button class="btn" style="margin-top:14px" type="submit">Submit Review</button>
        </form>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
