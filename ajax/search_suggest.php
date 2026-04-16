<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}
$stmt = db()->prepare('SELECT id, name FROM products WHERE status = 1 AND name LIKE ? ORDER BY rating_count DESC, id DESC LIMIT 6');
$stmt->execute(['%' . $q . '%']);
echo json_encode($stmt->fetchAll());
