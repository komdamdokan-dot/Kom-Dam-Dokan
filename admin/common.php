<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

/**
 * Renders the admin header and sidebar shell.
 */
function admin_header(string $title, string $active = 'dashboard'): void
{
    require_admin();
    $menu = [
        'dashboard' => ['Dashboard', 'index.php'],
        'products' => ['Products', 'products.php'],
        'categories' => ['Categories', 'categories.php'],
        'orders' => ['Orders', 'orders.php'],
        'users' => ['Users', 'users.php'],
        'reviews' => ['Reviews', 'reviews.php'],
        'coupons' => ['Coupons', 'coupons.php'],
        'settings' => ['Settings', 'settings.php'],
        'blog' => ['Blog', 'blog.php'],
        'backup' => ['Backup', 'backup.php'],
    ];
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . e($title) . '</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="admin-shell"><aside class="admin-nav"><h2 style="margin-top:0">Admin Panel</h2>';
    foreach ($menu as $key => [$label, $href]) {
        $class = $key === $active ? 'active' : '';
        echo '<a class="' . $class . '" href="' . e($href) . '">' . e($label) . '</a>';
    }
    echo '<a href="logout.php">Logout</a></aside><main class="admin-panel"><div class="flash-wrap">';
    foreach (get_flashes() as $flash) {
        echo '<div class="flash ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
    }
    echo '</div>';
}

/**
 * Closes the admin shell markup.
 */
function admin_footer(): void
{
    echo '</main></div><script src="../assets/js/main.js"></script></body></html>';
}
