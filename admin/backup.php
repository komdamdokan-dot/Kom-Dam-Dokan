<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';
require_admin();
admin_header('Backup', 'backup');

if (isset($_GET['download']) && $_GET['download'] === '1') {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="komdamdokan-backup-' . date('Ymd-His') . '.sql"');
    $tables = ['users','otps','login_attempts','categories','brands','products','product_variants','cart','wishlist','coupons','orders','order_items','reviews','blog_posts','blog_comments','settings','banners'];
    foreach ($tables as $table) {
        $rows = db()->query('SELECT * FROM ' . $table)->fetchAll();
        echo "-- Table: {$table}\n";
        foreach ($rows as $row) {
            $columns = array_map(static fn($col) => '`' . $col . '`', array_keys($row));
            $values = array_map(static fn($value) => $value === null ? 'NULL' : db()->quote((string) $value), array_values($row));
            echo 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n";
        }
        echo "\n";
    }
    exit;
}
?>
<h1 style="margin-top:0">Backup & Restore</h1>
<div class="admin-card" style="padding:20px"><p>Generate a simple SQL-style dump of table contents for migration or backup.</p><a class="btn" href="backup.php?download=1">Download Backup</a></div>
<?php admin_footer(); ?>
