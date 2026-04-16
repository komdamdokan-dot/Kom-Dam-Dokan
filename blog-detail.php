<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT * FROM blog_posts WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) {
    exit('Blog post not found.');
}
db()->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
$commentsStmt = db()->prepare('SELECT bc.*, u.name FROM blog_comments bc INNER JOIN users u ON u.id = bc.user_id WHERE bc.post_id = ? AND bc.status = 1 ORDER BY bc.id DESC');
$commentsStmt->execute([$post['id']]);
$comments = $commentsStmt->fetchAll();
$pageTitle = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>
<article class="card" style="padding:22px">
    <p class="muted"><?= e($post['created_at']) ?> | <?= e($post['category']) ?></p>
    <h1 style="margin-top:0"><?= e($post['title']) ?></h1>
    <div><?= nl2br($post['content']) ?></div>
</article>
<section class="card" style="padding:22px;margin-top:18px">
    <h2 style="margin-top:0">Comments</h2>
    <?php foreach ($comments as $comment): ?>
        <div style="padding:12px 0;border-bottom:1px solid var(--border)"><strong><?= e($comment['name']) ?></strong><p style="margin:6px 0 0"><?= e($comment['comment']) ?></p></div>
    <?php endforeach; ?>
    <?php if (is_logged_in()): ?>
        <form method="post" action="blog.php" style="margin-top:18px">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="slug" value="<?= e($post['slug']) ?>">
            <label>Comment</label>
            <textarea name="comment" rows="4" required></textarea>
            <button class="btn" style="margin-top:12px" type="submit">Post Comment</button>
        </form>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
