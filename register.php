<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $mobile = trim($_POST['mobile'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !is_gmail($email) || strlen($password) < 6) {
        set_flash('error', 'Use valid name, Gmail address, and password with at least 6 characters.');
    } else {
        $exists = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            set_flash('error', 'This email is already registered.');
        } else {
            $_SESSION['register_payload'] = [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];
            $_SESSION['otp_last_sent_at'] = time();
            $otp = create_otp($email, 'register');
            sendOTP($email, $otp, 'register');
            set_flash('success', 'OTP sent to your Gmail.');
            redirect('verify-otp.php?purpose=register&email=' . urlencode($email));
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Create Account</h1>
    <form method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <div><label>Name</label><input class="form-control" name="name" required></div>
            <div><label>Gmail</label><input class="form-control" type="email" name="email" required></div>
            <div><label>Mobile</label><input class="form-control" name="mobile" required></div>
            <div><label>Password</label><input class="form-control" type="password" name="password" minlength="6" required></div>
        </div>
        <button class="btn" style="margin-top:16px" type="submit">Register & Send OTP</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
