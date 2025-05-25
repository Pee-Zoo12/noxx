<?php
// Site information
define('SITE_NAME', 'NOX');

// Dynamically determine site URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['PHP_SELF']);
$basePath = $basePath === '/' ? '' : $basePath;
define('SITE_URL', rtrim($protocol . $host . $basePath, '/'));

// Data directory
define('DATA_DIR', __DIR__ . '/../data/');

// Data file paths
define('USERS_FILE', DATA_DIR . 'users.json');
define('PRODUCTS_FILE', DATA_DIR . 'products.json');
define('ORDERS_FILE', DATA_DIR . 'orders.json');
define('CART_FILE', DATA_DIR . 'cart.json');
define('INVENTORY_FILE', DATA_DIR . 'inventory.json');
define('PAYMENTS_FILE', DATA_DIR . 'payments.json');
define('COMPANY_FILE', DATA_DIR . 'company.json');
define('PURCHASES_FILE', DATA_DIR . 'purchases.json');