<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $image = upload_image_as_webp($_FILES['image'] ?? [], 'uploads/products') ?? null;
    $gallery = [];
    if (!empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['tmp_name'] as $index => $tmp) {
            $item = [
                'name' => $_FILES['gallery']['name'][$index],
                'type' => $_FILES['gallery']['type'][$index],
                'tmp_name' => $tmp,
                'error' => $_FILES['gallery']['error'][$index],
                'size' => $_FILES['gallery']['size'][$index],
            ];
            $saved = upload_image_as_webp($item, 'uploads/products');
            if ($saved) {
                $gallery[] = $saved;
            }
        }
    }
    db()->prepare('INSERT INTO products (cat_id, brand_id, name, slug, description, price, discount_percent, stock, image, gallery, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())')->execute([
        (int) ($_POST['cat_id'] ?? 0),
        !empty($_POST['brand_id']) ? (int) $_POST['brand_id'] : null,
        trim($_POST['name'] ?? ''),
        slugify((string) ($_POST['name'] ?? '')),
        trim($_POST['description'] ?? ''),
        (float) ($_POST['price'] ?? 0),
        (float) ($_POST['discount_percent'] ?? 0),
        (int) ($_POST['stock'] ?? 0),
        $image,
        $gallery ? json_encode($gallery) : null,
        (int) ($_POST['status'] ?? 1),
    ]);
    set_flash('success', 'Product added successfully.');
    redirect('products.php');
}

admin_header('Add Product', 'products');
$brands = db()->query('SELECT * FROM brands ORDER BY name ASC')->fetchAll();
?>
<h1 style="margin-top:0">Add Product</h1>
<form method="post" enctype="multipart/form-data" class="admin-card" style="padding:20px">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
        <div><label>Name</label><input class="form-control" name="name" required></div>
        <div><label>Category</label><select name="cat_id" required><?php foreach (all_categories() as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Brand</label><select name="brand_id"><option value="">None</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>"><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Price</label><input class="form-control" type="number" step="0.01" name="price"></div>
        <div><label>Discount %</label><input class="form-control" type="number" step="0.01" name="discount_percent"></div>
        <div><label>Stock</label><input class="form-control" type="number" name="stock"></div>
        <div><label>Main Image</label><input class="form-control" type="file" name="image" accept="image/*"></div>
        <div><label>Gallery Images (up to 3)</label><input class="form-control" type="file" name="gallery[]" accept="image/*" multiple></div>
        <div><label>Status</label><select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></div>
        <div><label>Description</label><textarea name="description" rows="6"></textarea></div>
    </div>
    <button class="btn" style="margin-top:16px" type="submit">Save Product</button>
</form>
<?php admin_footer(); ?>
