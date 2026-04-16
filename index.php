<?php
declare(strict_types=1);
$pageTitle = 'Kom Dam Dokan';
require_once __DIR__ . '/includes/header.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$products = fetch_products([], $page, 12);
$banners = [];
try {
    $banners = db()->query('SELECT * FROM banners ORDER BY order_by ASC, id DESC LIMIT 5')->fetchAll();
} catch (Throwable $e) {
    $banners = [];
}
?>
<section class="hero">
    <div class="hero-grid">
        <div class="hero-banner card">
            <span class="badge">Everyday Low Price</span>
            <h1>কম দামে প্রয়োজনীয় পণ্য, দ্রুত ডেলিভারি।</h1>
            <p>Mobile-first shopping experience with OTP account security, cash on delivery, wishlist, blog, and responsive admin tools.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
                <a class="btn" href="#all-products">ALL PRODUCTS</a>
                <a class="btn secondary" href="<?= base_url('blog.php') ?>">Read Blog</a>
            </div>
        </div>
        <div class="hero-side card">
            <h3 style="margin-top:0">Popular highlights</h3>
            <?php if ($banners): foreach ($banners as $banner): ?>
                <div style="margin-bottom:14px">
                    <strong><?= e($banner['title']) ?></strong>
                    <p class="muted" style="margin:4px 0 0">Banner link: <?= e($banner['link']) ?></p>
                </div>
            <?php endforeach; else: ?>
                <p class="muted">Add homepage banners from the admin panel to feature campaigns here.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<section id="all-products">
    <div class="section-title">
        <h2>All Products</h2>
        <a class="btn secondary" href="<?= base_url('search.php') ?>">Browse with filters</a>
    </div>
    <div class="grid product-grid">
        <?php foreach ($products['data'] as $product): $sale = sale_price((float) $product['price'], (float) $product['discount_percent']); ?>
            <article class="product-card">
                <a href="<?= base_url('product.php?id=' . $product['id']) ?>">
                    <div class="product-thumb"><img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>"></div>
                </a>
                <div class="product-body">
                    <span class="badge">-<?= (float) $product['discount_percent'] ?>%</span>
                    <h3 style="margin:10px 0 4px;font-size:18px"><a href="<?= base_url('product.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a></h3>
                    <div class="rating">Rating <?= e((string) $product['rating_avg']) ?> / 5 (<?= (int) $product['rating_count'] ?>)</div>
                    <div class="price-row">
                        <span class="price-new"><?= e(money($sale)) ?></span>
                        <span class="price-old"><?= e(money((float) $product['price'])) ?></span>
                    </div>
                    <form method="post" data-cart-action>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button class="btn" type="submit">Add to Cart</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?= pagination_links($products['page'], $products['pages']) ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
