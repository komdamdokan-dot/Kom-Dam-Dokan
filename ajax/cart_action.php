<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
if ($action === 'add') {
    add_to_cart((int) ($_POST['product_id'] ?? 0), max(1, min(10, (int) ($_POST['quantity'] ?? 1))));
} elseif ($action === 'update') {
    db()->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([max(1, min(10, (int) ($_POST['quantity'] ?? 1))), (int) ($_POST['cart_id'] ?? 0)]);
} elseif ($action === 'remove') {
    db()->prepare('DELETE FROM cart WHERE id = ?')->execute([(int) ($_POST['cart_id'] ?? 0)]);
}

echo json_encode(['message' => 'Cart updated.', 'count' => count(cart_items())]);

