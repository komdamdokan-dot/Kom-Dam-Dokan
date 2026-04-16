<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_admin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_admin = 1 AND status = 1 LIMIT 1');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password'])) {
        login_user($user, false);
        redirect('index.php');
    }
    set_flash('error', 'Invalid admin credentials.');
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Login</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="auth-card card"><h1 style="margin-top:0">Admin Login</h1><p class="muted">Default email: komdamdokan@gmail.com</p><?php foreach (get_flashes() as $flash): ?><div class="flash <?= e($flash['type']) ?>" style="margin-bottom:10px"><?= e($flash['message']) ?></div><?php endforeach; ?><form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><label>Email</label><input class="form-control" type="email" name="email" required><label style="margin-top:12px">Password</label><input class="form-control" type="password" name="password" required><button class="btn" style="margin-top:16px" type="submit">Login</button></form></div></body></html>
