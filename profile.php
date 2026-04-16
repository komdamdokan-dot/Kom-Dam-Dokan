<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    if (isset($_POST['save_profile'])) {
        db()->prepare('UPDATE users SET name = ?, mobile = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?')->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['mobile'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['pincode'] ?? ''),
            $user['id'],
        ]);
        $_SESSION['user'] = db()->query('SELECT * FROM users WHERE id = ' . (int) $user['id'])->fetch();
        set_flash('success', 'Profile updated.');
        redirect('profile.php');
    }

    if (isset($_POST['change_password'])) {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        if (!password_verify($current, $user['password'])) {
            set_flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
        } else {
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            set_flash('success', 'Password changed successfully.');
        }
        redirect('profile.php');
    }
}

$pageTitle = 'Your Profile';
require_once __DIR__ . '/includes/header.php';
$user = current_user();
?>
<div class="split">
    <section class="card" style="padding:22px">
        <h1 style="margin-top:0">Profile</h1>
        <form method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="save_profile" value="1">
            <div class="form-grid">
                <div><label>Name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>"></div>
                <div><label>Mobile</label><input class="form-control" name="mobile" value="<?= e($user['mobile']) ?>"></div>
                <div><label>Address</label><textarea name="address" rows="4"><?= e($user['address']) ?></textarea></div>
                <div>
                    <label>City</label><input class="form-control" name="city" value="<?= e($user['city']) ?>">
                    <label style="margin-top:12px">District/State</label><input class="form-control" name="state" value="<?= e($user['state']) ?>">
                    <label style="margin-top:12px">Pincode</label><input class="form-control" name="pincode" value="<?= e($user['pincode']) ?>">
                </div>
            </div>
            <button class="btn" style="margin-top:16px" type="submit">Save Profile</button>
        </form>
    </section>
    <aside class="card" style="padding:22px">
        <h3 style="margin-top:0">Change Password</h3>
        <form method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="change_password" value="1">
            <label>Current Password</label>
            <input class="form-control" type="password" name="current_password">
            <label style="margin-top:12px">New Password</label>
            <input class="form-control" type="password" name="new_password" minlength="6">
            <button class="btn" style="margin-top:16px" type="submit">Update Password</button>
        </form>
        <p style="margin-top:18px"><a href="order-history.php">View order history</a></p>
    </aside>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
