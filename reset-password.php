<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$email = $_SESSION['password_reset_email'] ?? '';
if ($email === '') {
    redirect('forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters.');
    } else {
        db()->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $email]);
        unset($_SESSION['password_reset_email']);
        set_flash('success', 'Password updated successfully.');
        redirect('login.php');
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Reset Password</h1>
    <form method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <label>New Password</label>
        <input class="form-control" type="password" name="password" minlength="6" required>
        <button class="btn" style="margin-top:16px" type="submit">Update Password</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
