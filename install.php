<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['_token'] ?? '');
    try {
        $sql = file_get_contents(__DIR__ . '/database.sql');
        if ($sql === false) {
            throw new RuntimeException('database.sql not found.');
        }
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if ($statement !== '') {
                db()->exec($statement);
            }
        }
        db()->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([
            password_hash('Sakib@7890', PASSWORD_DEFAULT),
            'komdamdokan@gmail.com',
        ]);
        $message = 'Installation completed successfully. For security, delete install.php after setup.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Install Kom Dam Dokan</title><link rel="stylesheet" href="assets/css/style.css"></head><body><div class="auth-card card"><h1 style="margin-top:0">Install Kom Dam Dokan</h1><?php if ($message): ?><div class="flash success"><?= e($message) ?></div><?php endif; ?><?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><ol><li>Copy <code>includes/config.sample.php</code> to <code>includes/config.php</code> and update database credentials.</li><li>Make sure your MySQL database exists.</li><li>Click install to create tables and seed default data.</li><li>Delete <code>install.php</code> after completion.</li></ol><form method="post"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><button class="btn" type="submit">Run Installation</button></form></div></body></html>
