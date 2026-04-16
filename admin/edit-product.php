<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    exit('Product not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $image = $product['image'];
    $newImage = upload_image_as_webp($_FILES['image'] ?? [], 'uploads/products');
    if ($newImage) {
        $image = $newImage;
    }
    db()->prepare('UPDATE products SET cat_id = ?, brand_id = ?, name = ?, slug = ?, description = ?, price = ?, discount_percent = ?, stock = ?, image = ?, status = ? WHERE id = ?')->execute([
        (int) ($_POST['cat_id'] ?? 0),
        !empty($_POST['brand_id']) ? (int) $_POST['brand_id'] : null,
        trim($_POST['name'] ?? ''),
        slugify((string) ($_POST['name'] ?? '')),
        trim($_POST['description'] ?? ''),
        (float) ($_POST['price'] ?? 0),
        (float) ($_POST['discount_percent'] ?? 0),
        (int) ($_POST['stock'] ?? 0),
        $image,
        (int) ($_POST['status'] ?? 1),
        $id,
    ]);
    set_flash('success', 'Product updated successfully.');
    redirect('products.php');
}
admin_header('Edit Product', 'products');
$brands = db()->query('SELECT * FROM brands ORDER BY name ASC')->fetchAll();
?>
<h1 style="margin-top:0">Edit Product</h1>
<form method="post" enctype="multipart/form-data" class="admin-card" style="padding:20px">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
        <div><label>Name</label><input class="form-control" name="name" value="<?= e($product['name']) ?>" required></div>
        <div><label>Category</label><select name="cat_id" required><?php foreach (all_categories() as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $product['cat_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Brand</label><select name="brand_id"><option value="">None</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= (int) $product['brand_id'] === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Price</label><input class="form-control" type="number" step="0.01" name="price" value="<?= e($product['price']) ?>"></div>
        <div><label>Discount %</label><input class="form-control" type="number" step="0.01" name="discount_percent" value="<?= e($product['discount_percent']) ?>"></div>
        <div><label>Stock</label><input class="form-control" type="number" name="stock" value="<?= e($product['stock']) ?>"></div>
        <div><label>Main Image</label><input class="form-control" type="file" name="image" accept="image/*"></div>
        <div><label>Status</label><select name="status"><option value="1" <?= (int) $product['status'] === 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= (int) $product['status'] === 0 ? 'selected' : '' ?>>Inactive</option></select></div>
        <div><label>Description</label><textarea name="description" rows="6"><?= e($product['description']) ?></textarea></div>
    </div>
    <button class="btn" style="margin-top:16px" type="submit">Update Product</button>
</form>
<?php admin_footer(); ?>
