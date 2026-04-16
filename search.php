<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'category' => $_GET['category'] ?? '',
    'min' => $_GET['min'] ?? '',
    'max' => $_GET['max'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
];
$products = fetch_products($filters, $page, 12);
$pageTitle = 'Search Products';
require_once __DIR__ . '/includes/header.php';
?>
<div class="layout">
    <aside class="sidebar card filters">
        <form method="get">
            <label>Keyword</label>
            <input class="form-control" type="text" name="q" value="<?= e($filters['q']) ?>">
            <label style="margin-top:12px">Category</label>
            <select name="category">
                <option value="">All categories</option>
                <?php foreach (all_categories() as $category): ?>
                    <option value="<?= e($category['slug']) ?>" <?= $filters['category'] === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label style="margin-top:12px">Min Price</label>
            <input id="minPrice" class="form-control" type="number" name="min" value="<?= e((string) $filters['min']) ?>">
            <input type="range" min="0" max="5000" value="<?= e((string) ($filters['min'] ?: 0)) ?>" data-range-sync="minPrice">
            <label style="margin-top:12px">Max Price</label>
            <input id="maxPrice" class="form-control" type="number" name="max" value="<?= e((string) $filters['max']) ?>">
            <input type="range" min="0" max="5000" value="<?= e((string) ($filters['max'] ?: 5000)) ?>" data-range-sync="maxPrice">
            <label style="margin-top:12px">Sort</label>
            <select name="sort">
                <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest first</option>
                <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price low to high</option>
                <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price high to low</option>
                <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
            </select>
            <button class="btn" style="margin-top:14px" type="submit">Apply Filters</button>
        </form>
    </aside>
    <section>
        <div class="section-title"><h2>Search Results</h2><span class="muted"><?= (int) $products['total'] ?> items found</span></div>
        <div class="grid product-grid">
            <?php foreach ($products['data'] as $product): $sale = sale_price((float) $product['price'], (float) $product['discount_percent']); ?>
                <article class="product-card">
                    <a href="product.php?id=<?= (int) $product['id'] ?>"><div class="product-thumb"><img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>"></div></a>
                    <div class="product-body">
                        <h3 style="margin:0 0 8px"><a href="product.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
                        <div class="rating">Rating <?= e((string) $product['rating_avg']) ?> / 5</div>
                        <div class="price-row"><span class="price-new"><?= e(money($sale)) ?></span><span class="price-old"><?= e(money((float) $product['price'])) ?></span></div>
                        <form method="post" data-cart-action>
                            <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="quantity" value="1">
                            <button class="btn" type="submit">Add to Cart</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= pagination_links($products['page'], $products['pages']) ?>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
