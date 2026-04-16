<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (isset($_POST['delete'])) {
        db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([(int) $_POST['review_id']]);
    } else {
        db()->prepare('UPDATE reviews SET status = ? WHERE id = ?')->execute([$_POST['status'] ?? 'approved', (int) $_POST['review_id']]);
        $prod = db()->prepare('SELECT product_id FROM reviews WHERE id = ?');
        $prod->execute([(int) $_POST['review_id']]);
        if ($productId = $prod->fetchColumn()) {
            refresh_product_rating((int) $productId);
        }
    }
    set_flash('success', 'Review updated.');
    redirect('reviews.php');
}
$reviews = db()->query('SELECT r.*, p.name AS product_name, u.name AS user_name FROM reviews r INNER JOIN products p ON p.id = r.product_id INNER JOIN users u ON u.id = r.user_id ORDER BY r.id DESC')->fetchAll();
admin_header('Reviews', 'reviews');
?>
<h1 style="margin-top:0">Reviews</h1>
<table class="table admin-card"><tr><th>Product</th><th>User</th><th>Rating</th><th>Comment</th><th>Status</th><th></th></tr><?php foreach ($reviews as $review): ?><tr><td><?= e($review['product_name']) ?></td><td><?= e($review['user_name']) ?></td><td><?= (int) $review['rating'] ?></td><td><?= e($review['comment']) ?></td><td><?= e($review['status']) ?></td><td><form method="post" style="display:inline"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>"><select name="status"><option value="approved">Approved</option><option value="pending">Pending</option></select><button class="btn secondary" type="submit">Save</button></form> <form method="post" style="display:inline"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>"><button class="btn danger" name="delete" value="1" type="submit">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php admin_footer(); ?>
