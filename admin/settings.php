<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    $keys = ['site_name', 'contact_email', 'copyright_text', 'delivery_charge', 'free_delivery_threshold'];
    foreach ($keys as $key) {
        save_setting($key, trim((string) ($_POST[$key] ?? '')));
    }
    save_setting('smtp_host', trim((string) ($_POST['smtp_host'] ?? '')));
    save_setting('smtp_port', trim((string) ($_POST['smtp_port'] ?? '')));
    save_setting('smtp_email', trim((string) ($_POST['smtp_email'] ?? '')));
    save_setting('smtp_password', trim((string) ($_POST['smtp_password'] ?? '')));

    if (!empty($_FILES['logo']['name'])) {
        $logo = upload_image_as_webp($_FILES['logo'], 'uploads/banners');
        if ($logo) {
            save_setting('site_logo', $logo);
        }
    }

    if (!empty($_POST['banner_title'])) {
        $bannerImage = upload_image_as_webp($_FILES['banner_image'] ?? [], 'uploads/banners');
        if ($bannerImage) {
            db()->prepare('INSERT INTO banners (title, image, link, order_by) VALUES (?, ?, ?, ?)')->execute([
                trim($_POST['banner_title']), $bannerImage, trim($_POST['banner_link'] ?? ''), (int) ($_POST['order_by'] ?? 0)
            ]);
        }
    }
    set_flash('success', 'Settings updated.');
    redirect('settings.php');
}

if (isset($_GET['delete_banner'])) {
    db()->prepare('DELETE FROM banners WHERE id = ?')->execute([(int) $_GET['delete_banner']]);
    set_flash('success', 'Banner removed.');
    redirect('settings.php');
}

$banners = db()->query('SELECT * FROM banners ORDER BY order_by ASC, id DESC')->fetchAll();
admin_header('Settings', 'settings');
?>
<h1 style="margin-top:0">Site Settings</h1>
<form method="post" enctype="multipart/form-data" class="admin-card" style="padding:20px">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
        <div><label>Site Name</label><input class="form-control" name="site_name" value="<?= e(setting('site_name', 'Kom Dam Dokan')) ?>"></div>
        <div><label>Contact Email</label><input class="form-control" name="contact_email" value="<?= e(setting('contact_email', 'komdamdokan@gmail.com')) ?>"></div>
        <div><label>Copyright</label><input class="form-control" name="copyright_text" value="<?= e(setting('copyright_text', '')) ?>"></div>
        <div><label>Logo</label><input class="form-control" type="file" name="logo"></div>
        <div><label>Delivery Charge</label><input class="form-control" name="delivery_charge" value="<?= e(setting('delivery_charge', '60')) ?>"></div>
        <div><label>Free Delivery Threshold</label><input class="form-control" name="free_delivery_threshold" value="<?= e(setting('free_delivery_threshold', '1000')) ?>"></div>
        <div><label>SMTP Host</label><input class="form-control" name="smtp_host" value="<?= e(setting('smtp_host', 'smtp.gmail.com')) ?>"></div>
        <div><label>SMTP Port</label><input class="form-control" name="smtp_port" value="<?= e(setting('smtp_port', '587')) ?>"></div>
        <div><label>SMTP Email</label><input class="form-control" name="smtp_email" value="<?= e(setting('smtp_email', '')) ?>"></div>
        <div><label>SMTP Password</label><input class="form-control" name="smtp_password" value="<?= e(setting('smtp_password', '')) ?>"></div>
        <div><label>Banner Title</label><input class="form-control" name="banner_title"></div>
        <div><label>Banner Link</label><input class="form-control" name="banner_link"></div>
        <div><label>Banner Order</label><input class="form-control" type="number" name="order_by" value="0"></div>
        <div><label>Banner Image</label><input class="form-control" type="file" name="banner_image"></div>
    </div>
    <button class="btn" style="margin-top:16px" type="submit">Save Settings</button>
</form>
<div class="admin-card" style="padding:20px;margin-top:18px"><h2 style="margin-top:0">Banners</h2><?php foreach ($banners as $banner): ?><p><?= e($banner['title']) ?> - <a href="settings.php?delete_banner=<?= (int) $banner['id'] ?>">Delete</a></p><?php endforeach; ?></div>
<?php admin_footer(); ?>
