CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mobile VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    remember_token VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expiry DATETIME NOT NULL,
    purpose ENUM('register', 'forgot') NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    ip_address VARCHAR(64) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    parent_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    logo VARCHAR(255) NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    brand_id INT NULL,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    gallery TEXT NULL,
    rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
    rating_count INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(50) NULL,
    color VARCHAR(50) NULL,
    stock INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(64) NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    variant_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_product (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) NOT NULL UNIQUE,
    discount_type ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    expiry_date DATE NOT NULL,
    usage_limit INT NOT NULL DEFAULT 1,
    used_count INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(60) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0,
    coupon_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(20) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    order_note TEXT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(190) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    variant VARCHAR(100) NULL
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NULL,
    status ENUM('approved', 'pending') NOT NULL DEFAULT 'approved',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) NULL,
    category VARCHAR(120) NULL,
    tags VARCHAR(255) NULL,
    views INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL
);

CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,
    order_by INT NOT NULL DEFAULT 0
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Kom Dam Dokan'),
('contact_email', 'komdamdokan@gmail.com'),
('delivery_charge', '60'),
('free_delivery_threshold', '1000'),
('copyright_text', 'Copyright 2026 Kom Dam Dokan')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO categories (name, slug) VALUES
('Groceries', 'groceries'),
('Daily Essentials', 'daily-essentials'),
('Household', 'household')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (cat_id, name, slug, description, price, discount_percent, stock, image, rating_avg, rating_count, status) VALUES
(1, 'Fresh Rice 5kg', 'fresh-rice-5kg', 'Premium quality rice for daily family meals.', 520, 8, 25, '', 4.50, 12, 1),
(1, 'Mustard Oil 1L', 'mustard-oil-1l', 'Cold-pressed mustard oil with rich aroma.', 240, 5, 40, '', 4.20, 8, 1),
(2, 'Bath Soap Pack', 'bath-soap-pack', 'Value pack of gentle bath soaps.', 180, 10, 60, '', 4.40, 16, 1),
(3, 'Kitchen Cleaner', 'kitchen-cleaner', 'Fast-action cleaner for tiles and counters.', 150, 0, 32, '', 4.10, 4, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO users (name, email, mobile, password, status, is_admin) VALUES
('Super Admin', 'komdamdokan@gmail.com', '01700000000', '$2y$10$BzBG4pyI9CWB4Q3l/GcNQOO96usKdRRwyfPLXXyS4RIq9YmLuOwCq', 1, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), is_admin = VALUES(is_admin);
