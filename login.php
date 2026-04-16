<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (login_blocked($email, $ip)) {
        set_flash('error', 'Too many failed attempts. Try again after 15 minutes.');
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            record_login_attempt($email, $ip, true);
            login_user($user, $remember);
            merge_guest_cart_into_user((int) $user['id']);
            redirect('index.php');
        }

        record_login_attempt($email, $ip, false);
        set_flash('error', 'Invalid credentials.');
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Login</h1>
    <form method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <label>Gmail</label>
        <input class="form-control" type="email" name="email" required>
        <label style="margin-top:12px">Password</label>
        <input class="form-control" type="password" name="password" required>
        <label style="display:flex;gap:8px;align-items:center;margin-top:12px"><input type="checkbox" name="remember" value="1"> Remember Me for 30 days</label>
        <button class="btn" style="margin-top:16px" type="submit">Login</button>
        <p style="margin-top:14px"><a href="forgot-password.php">Forgot password?</a></p>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
