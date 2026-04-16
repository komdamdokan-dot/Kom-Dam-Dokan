<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!is_gmail($email)) {
        set_flash('error', 'Enter your registered Gmail address.');
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $otp = create_otp($email, 'forgot');
            sendOTP($email, $otp, 'forgot');
            $_SESSION['otp_last_sent_at'] = time();
            redirect('verify-otp.php?purpose=forgot&email=' . urlencode($email));
        } else {
            set_flash('error', 'No user found for this Gmail.');
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Forgot Password</h1>
    <form method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <label>Registered Gmail</label>
        <input class="form-control" type="email" name="email" required>
        <button class="btn" style="margin-top:16px" type="submit">Send OTP</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
