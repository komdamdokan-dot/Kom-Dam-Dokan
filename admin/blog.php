<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (!empty($_POST['post_id'])) {
        db()->prepare('UPDATE blog_posts SET title = ?, slug = ?, content = ?, category = ?, tags = ? WHERE id = ?')->execute([
            trim($_POST['title']), slugify((string) ($_POST['title'] ?? '')), trim($_POST['content']), trim($_POST['category']), trim($_POST['tags']), (int) $_POST['post_id']
        ]);
        set_flash('success', 'Blog post updated.');
    } else {
        $image = upload_image_as_webp($_FILES['image'] ?? [], 'uploads/blog');
        db()->prepare('INSERT INTO blog_posts (title, slug, content, image, category, tags, views, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())')->execute([
            trim($_POST['title']), slugify((string) ($_POST['title'] ?? '')), trim($_POST['content']), $image, trim($_POST['category']), trim($_POST['tags'])
        ]);
        set_flash('success', 'Blog post added.');
    }
    redirect('blog.php');
}
if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([(int) $_GET['delete']]);
    set_flash('success', 'Blog post deleted.');
    redirect('blog.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$posts = db()->query('SELECT * FROM blog_posts ORDER BY id DESC')->fetchAll();
admin_header('Blog', 'blog');
?>
<h1 style="margin-top:0">Blog Management</h1>
<div class="split">
    <section class="admin-card" style="padding:20px">
        <form method="post" enctype="multipart/form-data"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><?php if ($edit): ?><input type="hidden" name="post_id" value="<?= (int) $edit['id'] ?>"><?php endif; ?><div class="form-grid"><div><label>Title</label><input class="form-control" name="title" value="<?= e($edit['title'] ?? '') ?>" required></div><div><label>Category</label><input class="form-control" name="category" value="<?= e($edit['category'] ?? '') ?>"></div><div><label>Tags</label><input class="form-control" name="tags" value="<?= e($edit['tags'] ?? '') ?>"></div><div><label>Image</label><input class="form-control" type="file" name="image"></div><div><label>Content</label><textarea name="content" rows="12"><?= e($edit['content'] ?? '') ?></textarea></div></div><button class="btn" style="margin-top:16px" type="submit"><?= $edit ? 'Update' : 'Add' ?> Post</button></form>
    </section>
    <aside class="admin-card" style="padding:20px"><h2 style="margin-top:0">Posts</h2><?php foreach ($posts as $post): ?><p><a href="blog.php?edit=<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a> | <a href="blog.php?delete=<?= (int) $post['id'] ?>">Delete</a></p><?php endforeach; ?></aside>
</div>
<?php admin_footer(); ?>
