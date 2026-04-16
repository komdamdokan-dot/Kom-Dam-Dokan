<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns the shared application configuration array.
 */
function app_config(): array
{
    static $config;

    if ($config === null) {
        $configFile = __DIR__ . '/config.php';
        if (!file_exists($configFile)) {
            $configFile = __DIR__ . '/config.sample.php';
        }
        $config = require $configFile;
        date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Dhaka');
    }

    return $config;
}

/**
 * Returns the shared PDO connection instance.
 */
function db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config()['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

/**
 * Escapes user-facing output for safe HTML rendering.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Returns the base URL for local links and assets.
 */
function base_url(string $path = ''): string
{
    $url = '';
    $configuredUrl = trim((string) (app_config()['app']['url'] ?? ''));
    if ($configuredUrl !== '') {
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        $configuredHost = parse_url($configuredUrl, PHP_URL_HOST) ?: '';
        $configuredPort = parse_url($configuredUrl, PHP_URL_PORT);
        $currentPort = null;
        $currentHostOnly = preg_replace('/:\d+$/', '', $currentHost);
        if (strpos($currentHost, ':') !== false) {
            [, $port] = explode(':', $currentHost, 2);
            $currentPort = (int) $port;
        }

        if ($configuredHost !== '' && strcasecmp($configuredHost, $currentHostOnly) === 0) {
            $defaultCurrentPort = $currentPort === null || in_array($currentPort, [80, 443], true);
            $portsMatch = $configuredPort !== null && $configuredPort === $currentPort;
            $safeWithoutPort = $configuredPort === null && $defaultCurrentPort;
            if ($portsMatch || $safeWithoutPort) {
                $url = rtrim($configuredUrl, '/');
            }
        }
    }

    if (empty($url)) {
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $directory = str_replace('\\', '/', dirname($scriptName));
        $directory = $directory === '/' || $directory === '.' ? '' : $directory;
        if (str_ends_with($directory, '/admin')) {
            $directory = substr($directory, 0, -6);
        }
        $url = rtrim($scheme . '://' . $host . $directory, '/');
    }

    $path = ltrim($path, '/');
    return $path === '' ? $url : $url . '/' . $path;
}

/**
 * Redirects the browser and stops further execution.
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Stores a flash message for the next request.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = compact('type', 'message');
}

/**
 * Returns and clears flash messages.
 */
function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Creates or returns the guest session identifier used by the cart.
 */
function guest_session_id(): string
{
    if (empty($_SESSION['guest_session_id'])) {
        $_SESSION['guest_session_id'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['guest_session_id'];
}

/**
 * Generates and stores a CSRF token for forms.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validates a posted CSRF token and stops on failure.
 */
function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

/**
 * Returns the logged-in user row if available.
 */
function current_user(): ?array
{
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    $cookieName = app_config()['app']['remember_cookie'] ?? 'kdd_remember';
    if (!empty($_COOKIE[$cookieName])) {
        $parts = explode('|', $_COOKIE[$cookieName], 2);
        if (count($parts) === 2) {
            [$userId, $token] = $parts;
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND remember_token = ? LIMIT 1');
            $stmt->execute([(int) $userId, hash('sha256', $token)]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user'] = $user;
                return $user;
            }
        }
    }

    return null;
}

/**
 * Returns whether the current visitor is authenticated.
 */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/**
 * Returns whether the current visitor is an administrator.
 */
function is_admin(): bool
{
    $user = current_user();
    return $user !== null && (int) ($user['is_admin'] ?? 0) === 1;
}

/**
 * Ensures a logged-in user exists before proceeding.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in first.');
        redirect('login.php');
    }
}

/**
 * Ensures the current user is an admin before proceeding.
 */
function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}

/**
 * Loads site settings from the database as a key-value array.
 */
function settings(): array
{
    static $settings;

    if ($settings !== null) {
        return $settings;
    }

    $settings = [];
    try {
        $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {
        $settings = [];
    }

    return $settings;
}

/**
 * Returns a setting or fallback value.
 */
function setting(string $key, string $default = ''): string
{
    $settings = settings();
    return $settings[$key] ?? $default;
}

/**
 * Stores a setting value in the database.
 */
function save_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

/**
 * Validates that an email belongs to Gmail.
 */
function is_gmail(string $email): bool
{
    return (bool) preg_match('/^[A-Z0-9._%+-]+@gmail\\.com$/i', $email);
}

/**
 * Generates a six digit OTP string.
 */
function generate_otp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Creates an OTP row for a purpose and invalidates older unused ones.
 */
function create_otp(string $email, string $purpose): string
{
    $otp = generate_otp();
    $expiry = date('Y-m-d H:i:s', time() + 600);
    $pdo = db();
    $pdo->prepare('UPDATE otps SET used = 1 WHERE email = ? AND purpose = ? AND used = 0')->execute([$email, $purpose]);
    $pdo->prepare('INSERT INTO otps (email, otp, expiry, purpose, used, created_at) VALUES (?, ?, ?, ?, 0, NOW())')->execute([$email, $otp, $expiry, $purpose]);
    return $otp;
}

/**
 * Verifies an OTP row and marks it as used when valid.
 */
function verify_otp_code(string $email, string $otp, string $purpose): bool
{
    $stmt = db()->prepare('SELECT * FROM otps WHERE email = ? AND otp = ? AND purpose = ? AND used = 0 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email, $otp, $purpose]);
    $record = $stmt->fetch();

    if (!$record || strtotime($record['expiry']) < time()) {
        return false;
    }

    db()->prepare('UPDATE otps SET used = 1 WHERE id = ?')->execute([$record['id']]);
    return true;
}

/**
 * Returns true when too many failed logins happened recently.
 */
function login_blocked(string $email, string $ip): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE (email = ? OR ip_address = ?) AND success = 0 AND attempted_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
    $stmt->execute([$email, $ip]);
    return (int) $stmt->fetchColumn() >= 5;
}

/**
 * Saves a login attempt for throttling.
 */
function record_login_attempt(string $email, string $ip, bool $success): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$email, $ip, $success ? 1 : 0]);
}

/**
 * Logs a user in and optionally persists a remember token.
 */
function login_user(array $user, bool $remember): void
{
    $_SESSION['user'] = $user;
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

    if ($remember) {
        $token = bin2hex(random_bytes(24));
        db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?')->execute([hash('sha256', $token), $user['id']]);
        setcookie(
            app_config()['app']['remember_cookie'],
            $user['id'] . '|' . $token,
            time() + ((int) app_config()['app']['remember_days'] * 86400),
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true
        );
    }
}

/**
 * Logs out the current user and clears persistent auth cookies.
 */
function logout_user(): void
{
    $user = current_user();
    if ($user) {
        db()->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$user['id']]);
    }

    setcookie(app_config()['app']['remember_cookie'], '', time() - 3600, '/');
    unset($_SESSION['user']);
}

/**
 * Builds a product slug from a human readable name.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

/**
 * Returns a product image URL with fallback placeholder.
 */
function product_image(?string $image): string
{
    if ($image && file_exists(__DIR__ . '/../' . ltrim($image, '/'))) {
        return base_url($image);
    }
    return base_url('assets/images/placeholder-product.svg');
}

/**
 * Calculates the sale price after discount percentage.
 */
function sale_price(float $price, float $discountPercent): float
{
    return round($price - (($price * $discountPercent) / 100), 2);
}

/**
 * Formats a number as Bangladeshi taka.
 */
function money(float $amount): string
{
    return 'Tk ' . number_format($amount, 2);
}

/**
 * Returns available categories ordered by name.
 */
function all_categories(): array
{
    try {
        return db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Returns a paginated product listing based on filters.
 */
function fetch_products(array $filters = [], int $page = 1, int $perPage = 12): array
{
    $where = ['p.status = 1'];
    $params = [];

    if (!empty($filters['q'])) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $filters['q'] . '%';
        $params[] = '%' . $filters['q'] . '%';
    }

    if (!empty($filters['category'])) {
        $where[] = 'c.slug = ?';
        $params[] = $filters['category'];
    }

    if (isset($filters['min']) && $filters['min'] !== '') {
        $where[] = '(p.price - ((p.price * p.discount_percent) / 100)) >= ?';
        $params[] = (float) $filters['min'];
    }

    if (isset($filters['max']) && $filters['max'] !== '') {
        $where[] = '(p.price - ((p.price * p.discount_percent) / 100)) <= ?';
        $params[] = (float) $filters['max'];
    }

    $order = 'p.created_at DESC';
    $sort = $filters['sort'] ?? 'newest';
    if ($sort === 'price_asc') {
        $order = '(p.price - ((p.price * p.discount_percent) / 100)) ASC';
    } elseif ($sort === 'price_desc') {
        $order = '(p.price - ((p.price * p.discount_percent) / 100)) DESC';
    } elseif ($sort === 'name_asc') {
        $order = 'p.name ASC';
    }

    $offset = max($page - 1, 0) * $perPage;
    $pdo = db();
    $countSql = 'SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.cat_id WHERE ' . implode(' AND ', $where);
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.cat_id WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => max(1, (int) ceil($total / $perPage)),
    ];
}

/**
 * Returns one product row by identifier.
 */
function find_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.cat_id WHERE p.id = ? LIMIT 1');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

/**
 * Returns a list of related products from the same category.
 */
function related_products(int $catId, int $excludeId): array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE cat_id = ? AND id != ? AND status = 1 ORDER BY created_at DESC LIMIT 4');
    $stmt->execute([$catId, $excludeId]);
    return $stmt->fetchAll();
}

/**
 * Returns the active cart items for the current guest or user.
 */
function cart_items(): array
{
    $user = current_user();
    $field = $user ? 'c.user_id = ?' : 'c.session_id = ?';
    $value = $user ? $user['id'] : guest_session_id();

    $stmt = db()->prepare('SELECT c.*, p.name, p.image, p.price, p.discount_percent, p.stock FROM cart c INNER JOIN products p ON p.id = c.product_id WHERE ' . $field . ' ORDER BY c.id DESC');
    $stmt->execute([$value]);
    return $stmt->fetchAll();
}

/**
 * Adds a product to the current cart or increases quantity.
 */
function add_to_cart(int $productId, int $quantity = 1): void
{
    $product = find_product($productId);
    if (!$product) {
        return;
    }

    $user = current_user();
    $pdo = db();
    if ($user) {
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1');
        $stmt->execute([$user['id'], $productId]);
    } else {
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? LIMIT 1');
        $stmt->execute([guest_session_id(), $productId]);
    }
    $item = $stmt->fetch();

    if ($item) {
        $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([min(10, $item['quantity'] + $quantity), $item['id']]);
    } else {
        $pdo->prepare('INSERT INTO cart (user_id, session_id, product_id, quantity, created_at) VALUES (?, ?, ?, ?, NOW())')->execute([$user['id'] ?? null, $user ? null : guest_session_id(), $productId, min(10, $quantity)]);
    }
}

/**
 * Synchronizes guest cart rows into the user cart after login.
 */
function merge_guest_cart_into_user(int $userId): void
{
    $guestId = guest_session_id();
    $items = db()->prepare('SELECT * FROM cart WHERE session_id = ?');
    $items->execute([$guestId]);
    foreach ($items->fetchAll() as $item) {
        $existing = db()->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1');
        $existing->execute([$userId, $item['product_id']]);
        $row = $existing->fetch();
        if ($row) {
            db()->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([min(10, $row['quantity'] + $item['quantity']), $row['id']]);
        } else {
            db()->prepare('UPDATE cart SET user_id = ?, session_id = NULL WHERE id = ?')->execute([$userId, $item['id']]);
        }
    }
}

/**
 * Returns the current cart totals.
 */
function cart_totals(): array
{
    $subtotal = 0.0;
    foreach (cart_items() as $item) {
        $subtotal += sale_price((float) $item['price'], (float) $item['discount_percent']) * (int) $item['quantity'];
    }

    $delivery = $subtotal >= (float) setting('free_delivery_threshold', '1000') ? 0.0 : (float) setting('delivery_charge', '60');
    return [
        'subtotal' => $subtotal,
        'delivery' => $delivery,
        'coupon_discount' => 0.0,
        'total' => $subtotal + $delivery,
    ];
}

/**
 * Returns whether a user can review a given product.
 */
function can_review_product(int $userId, int $productId): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = "delivered"');
    $stmt->execute([$userId, $productId]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Recalculates the product rating aggregate columns.
 */
function refresh_product_rating(int $productId): void
{
    $stmt = db()->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE product_id = ? AND status = "approved"');
    $stmt->execute([$productId]);
    $row = $stmt->fetch() ?: ['avg_rating' => 0, 'total_reviews' => 0];
    db()->prepare('UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?')->execute([round((float) $row['avg_rating'], 2), (int) $row['total_reviews'], $productId]);
}

/**
 * Sends a simple HTML email via the configured transport.
 */
function send_html_mail(string $to, string $subject, string $html): bool
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= 'From: ' . setting('contact_email', app_config()['mail']['from_email']) . "\r\n";
    return mail($to, $subject, $html, $headers);
}

/**
 * Saves an uploaded image as WebP and returns the relative path.
 */
function upload_image_as_webp(array $file, string $targetDir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        return null;
    }

    $imageData = file_get_contents($file['tmp_name']);
    if ($imageData === false) {
        return null;
    }

    $image = imagecreatefromstring($imageData);
    if (!$image) {
        return null;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $newWidth = min(500, $width);
    $newHeight = (int) round(($height / max($width, 1)) * $newWidth);

    $canvas = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $filename = bin2hex(random_bytes(12)) . '.webp';
    $relative = trim($targetDir, '/') . '/' . $filename;
    $absolute = __DIR__ . '/../' . $relative;
    if (!is_dir(dirname($absolute))) {
        mkdir(dirname($absolute), 0775, true);
    }

    imagewebp($canvas, $absolute, 80);
    imagedestroy($image);
    imagedestroy($canvas);

    return $relative;
}

/**
 * Creates a unique order number string.
 */
function generate_order_number(): string
{
    return 'KDD' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

/**
 * Creates pagination links preserving current query parameters.
 */
function pagination_links(int $page, int $pages): string
{
    if ($pages <= 1) {
        return '';
    }

    $query = $_GET;
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $query['page'] = $i;
        $class = $i === $page ? 'active' : '';
        $html .= '<a class="' . $class . '" href="?' . http_build_query($query) . '">' . $i . '</a>';
    }
    $html .= '</div>';
    return $html;
}

