<?php
// Start session
session_start();

// Define base path first
define('BASE_PATH', dirname(__DIR__));

// Initialize required files
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

// Helper functions for JSON handling
function readJsonFile($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    
    return json_decode($content, true) ?: [];
}

function writeJsonFile($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Autoload classes
spl_autoload_register(function ($class) {
    $classFile = BASE_PATH . '/classes/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Initialize user object
$user = new User();
if (isLoggedIn()) {
    $userData = $user->getById($_SESSION['user_id']);
    if ($userData) {
        $user = new User($userData);
    }
}

// Initialize cart
$cart = new ShoppingCart($_SESSION['user_id'] ?? null);

// Initialize company
$company = Company::getInstance();

// Initialize data directory and files if they don't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Initialize JSON data files with default data if they don't exist
$dataFiles = [
    USERS_FILE => ['customers' => [], 'employees' => []],
    PRODUCTS_FILE => ['products' => []],
    ORDERS_FILE => ['orders' => []],
    CART_FILE => ['shipping' => []],
    INVENTORY_FILE => ['inventory' => []],
    PAYMENTS_FILE => ['payments' => []]
];

foreach ($dataFiles as $file => $defaultData) {
    if (!file_exists($file)) {
        writeJsonFile($file, $defaultData);
    }
}

// Authentication helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = readJsonFile(USERS_FILE);
    $userId = $_SESSION['user_id'];
    
    foreach ($users as $type => $userList) {
        foreach ($userList as $user) {
            if ($user['userID'] == $userId) {
                return $user;
            }
        }
    }
    
    return null;
}
?>