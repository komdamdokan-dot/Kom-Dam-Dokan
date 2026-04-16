<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (isset($_POST['add_category'])) {
        db()->prepare('INSERT INTO categories (name, slug, parent_id, created_at) VALUES (?, ?, ?, NOW())')->execute([
            trim($_POST['name'] ?? ''),
            slugify((string) ($_POST['slug'] ?? $_POST['name'] ?? '')),
            !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
        ]);
        set_flash('success', 'Category added.');
    }
    if (isset($_POST['add_brand'])) {
        db()->prepare('INSERT INTO brands (name, logo) VALUES (?, ?)')->execute([trim($_POST['brand_name'] ?? ''), null]);
        set_flash('success', 'Brand added.');
    }
    redirect('categories.php');
}
if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM categories WHERE id = ?')->execute([(int) $_GET['delete']]);
    set_flash('success', 'Category deleted.');
    redirect('categories.php');
}
$categories = db()->query('SELECT c.*, p.name AS parent_name FROM categories c LEFT JOIN categories p ON p.id = c.parent_id ORDER BY c.id DESC')->fetchAll();
$brands = db()->query('SELECT * FROM brands ORDER BY id DESC')->fetchAll();
admin_header('Categories', 'categories');
?>
<h1 style="margin-top:0">Categories & Brands</h1>
<div class="split">
    <section class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Add Category</h2>
        <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="add_category" value="1"><div class="form-grid"><div><label>Name</label><input class="form-control" name="name" required></div><div><label>Slug</label><input class="form-control" name="slug"></div><div><label>Parent</label><select name="parent_id"><option value="">None</option><?php foreach (all_categories() as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select></div></div><button class="btn" style="margin-top:16px" type="submit">Save Category</button></form>
        <table class="table" style="margin-top:18px"><tr><th>Name</th><th>Slug</th><th>Parent</th><th></th></tr><?php foreach ($categories as $category): ?><tr><td><?= e($category['name']) ?></td><td><?= e($category['slug']) ?></td><td><?= e($category['parent_name']) ?></td><td><a href="categories.php?delete=<?= (int) $category['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table>
    </section>
    <aside class="admin-card" style="padding:20px">
        <h2 style="margin-top:0">Brands</h2>
        <form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="add_brand" value="1"><label>Brand Name</label><input class="form-control" name="brand_name" required><button class="btn" style="margin-top:16px" type="submit">Save Brand</button></form>
        <div style="margin-top:18px"><?php foreach ($brands as $brand): ?><p><?= e($brand['name']) ?></p><?php endforeach; ?></div>
    </aside>
</div>
<?php admin_footer(); ?>
