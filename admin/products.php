<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([(int) $_GET['delete']]);
    set_flash('success', 'Product deleted.');
    redirect('products.php');
}
if (isset($_GET['duplicate'])) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_GET['duplicate']]);
    $product = $stmt->fetch();
    if ($product) {
        db()->prepare('INSERT INTO products (cat_id, brand_id, name, slug, description, price, discount_percent, stock, image, gallery, rating_avg, rating_count, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, NOW())')->execute([
            $product['cat_id'], $product['brand_id'], $product['name'] . ' Copy', slugify($product['name'] . '-' . bin2hex(random_bytes(2))), $product['description'], $product['price'], $product['discount_percent'], $product['stock'], $product['image'], $product['gallery'], $product['status']
        ]);
        set_flash('success', 'Product duplicated.');
    }
    redirect('products.php');
}

$where = ['1=1'];
$params = [];
if (!empty($_GET['q'])) {
    $where[] = 'p.name LIKE ?';
    $params[] = '%' . trim($_GET['q']) . '%';
}
if (!empty($_GET['category'])) {
    $where[] = 'c.id = ?';
    $params[] = (int) $_GET['category'];
}
if (!empty($_GET['stock']) && $_GET['stock'] === 'out') {
    $where[] = 'p.stock <= 0';
}
$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.cat_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC');
$stmt->execute($params);
$products = $stmt->fetchAll();
admin_header('Products', 'products');
?>
<h1 style="margin-top:0">Products</h1>
<p><a class="btn" href="add-product.php">Add Product</a></p>
<form method="get" class="form-grid admin-card" style="padding:16px;margin-bottom:18px"><input class="form-control" name="q" placeholder="Search product" value="<?= e($_GET['q'] ?? '') ?>"><select name="category"><option value="">All categories</option><?php foreach (all_categories() as $category): ?><option value="<?= (int) $category['id'] ?>" <?= ((string) ($category['id'])) === ($_GET['category'] ?? '') ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select><select name="stock"><option value="">All stock</option><option value="out" <?= ($_GET['stock'] ?? '') === 'out' ? 'selected' : '' ?>>Out of stock</option></select><button class="btn" type="submit">Filter</button></form>
<table class="table admin-card"><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr><?php foreach ($products as $product): ?><tr><td><?= e($product['name']) ?></td><td><?= e($product['category_name']) ?></td><td><?= e(money((float) $product['price'])) ?></td><td><?= (int) $product['stock'] ?></td><td><a href="edit-product.php?id=<?= (int) $product['id'] ?>">Edit</a> | <a href="products.php?duplicate=<?= (int) $product['id'] ?>">Duplicate</a> | <a href="products.php?delete=<?= (int) $product['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a></td></tr><?php endforeach; ?></table>
<?php admin_footer(); ?>
