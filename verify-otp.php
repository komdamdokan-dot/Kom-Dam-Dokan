<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$email = strtolower(trim($_GET['email'] ?? $_POST['email'] ?? ''));
$purpose = $_GET['purpose'] ?? $_POST['purpose'] ?? 'register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (isset($_POST['resend'])) {
        if ((time() - (int) ($_SESSION['otp_last_sent_at'] ?? 0)) < 60) {
            set_flash('error', 'Please wait before resending OTP.');
        } else {
            $otp = create_otp($email, $purpose);
            sendOTP($email, $otp, $purpose);
            $_SESSION['otp_last_sent_at'] = time();
            set_flash('success', 'OTP resent successfully.');
        }
        redirect('verify-otp.php?purpose=' . urlencode($purpose) . '&email=' . urlencode($email));
    }

    if (verify_otp_code($email, trim($_POST['otp'] ?? ''), $purpose)) {
        if ($purpose === 'register') {
            $payload = $_SESSION['register_payload'] ?? null;
            if ($payload && $payload['email'] === $email) {
                $stmt = db()->prepare('INSERT INTO users (name, email, mobile, password, status, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
                $stmt->execute([$payload['name'], $payload['email'], $payload['mobile'], $payload['password']]);
                unset($_SESSION['register_payload']);
                set_flash('success', 'Registration completed. Please log in.');
                redirect('login.php');
            }
        }

        if ($purpose === 'forgot') {
            $_SESSION['password_reset_email'] = $email;
            redirect('reset-password.php');
        }
    } else {
        set_flash('error', 'OTP invalid or expired.');
    }
}

$pageTitle = 'Verify OTP';
require_once __DIR__ . '/includes/header.php';
$remaining = max(0, 60 - (time() - (int) ($_SESSION['otp_last_sent_at'] ?? 0)));
?>
<div class="auth-card card">
    <h1 style="margin-top:0">Verify OTP</h1>
    <p class="muted">We sent a 6-digit code to <?= e($email) ?>.</p>
    <form method="post">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <input type="hidden" name="purpose" value="<?= e($purpose) ?>">
        <label>OTP</label>
        <input class="form-control" name="otp" maxlength="6" required>
        <button class="btn" style="margin-top:16px" type="submit">Verify</button>
    </form>
    <form method="post" style="margin-top:12px">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <input type="hidden" name="purpose" value="<?= e($purpose) ?>">
        <input type="hidden" name="resend" value="1">
        <button class="btn secondary" type="submit" <?= $remaining > 0 ? 'disabled' : '' ?>>Resend OTP<?= $remaining > 0 ? ' in ' . $remaining . 's' : '' ?></button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
