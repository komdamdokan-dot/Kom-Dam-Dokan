<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    verify_csrf($_POST['_token'] ?? '');
    $stmt = db()->prepare('INSERT INTO blog_comments (post_id, user_id, comment, created_at, status) VALUES (?, ?, ?, NOW(), 1)');
    $stmt->execute([(int) ($_POST['post_id'] ?? 0), current_user()['id'], trim($_POST['comment'] ?? '')]);
    redirect('blog-detail.php?slug=' . urlencode($_POST['slug'] ?? ''));
}

$pageTitle = 'Blog';
require_once __DIR__ . '/includes/header.php';
$posts = db()->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll();
$popular = db()->query('SELECT * FROM blog_posts ORDER BY views DESC, id DESC LIMIT 5')->fetchAll();
?>
<div class="layout">
    <section>
        <div class="section-title"><h2>Blog Posts</h2></div>
        <?php foreach ($posts as $post): ?>
            <article class="blog-card card" style="margin-bottom:18px">
                <h3 style="margin-top:0"><a href="blog-detail.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                <p class="muted">Category: <?= e($post['category']) ?> | Tags: <?= e($post['tags']) ?></p>
                <p><?= e(substr(strip_tags($post['content']), 0, 180)) ?>...</p>
                <a class="btn secondary" href="blog-detail.php?slug=<?= e($post['slug']) ?>">Read More</a>
            </article>
        <?php endforeach; ?>
    </section>
    <aside class="sidebar card">
        <h3 style="margin-top:0">Popular Posts</h3>
        <?php foreach ($popular as $post): ?>
            <p><a href="blog-detail.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></p>
        <?php endforeach; ?>
        <h3>Categories</h3>
        <?php foreach (array_unique(array_filter(array_column($posts, 'category'))) as $category): ?>
            <p><?= e($category) ?></p>
        <?php endforeach; ?>
    </aside>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
