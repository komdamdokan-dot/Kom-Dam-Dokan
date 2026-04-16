<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM users WHERE id = ? AND is_admin = 0')->execute([(int) $_GET['delete']]);
    set_flash('success', 'User deleted.');
    redirect('users.php');
}
$users = db()->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
admin_header('Users', 'users');
?>
<h1 style="margin-top:0">Users</h1>
<table class="table admin-card"><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Status</th><th>Joined</th><th></th></tr><?php foreach ($users as $user): ?><tr><td><?= e($user['name']) ?></td><td><?= e($user['email']) ?></td><td><?= e($user['mobile']) ?></td><td><?= (int) $user['status'] ? 'Active' : 'Inactive' ?></td><td><?= e($user['created_at']) ?></td><td><?php if ((int) $user['is_admin'] === 0): ?><a href="edit-user.php?id=<?= (int) $user['id'] ?>">Edit</a> | <a href="users.php?delete=<?= (int) $user['id'] ?>">Delete</a><?php else: ?>Admin<?php endif; ?></td></tr><?php endforeach; ?></table>
<?php admin_footer(); ?>
