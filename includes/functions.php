<?php

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// User role functions
function getUserRole() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $users = readJsonFile(USERS_FILE);
    $userId = $_SESSION['user_id'];
    
    foreach ($users as $type => $userList) {
        foreach ($userList as $user) {
            if ($user['userID'] == $userId) {
                return $type;
            }
        }
    }
    
    return null;
}

function isAdmin() {
    return getUserRole() === 'employees';
}

function isCustomer() {
    return getUserRole() === 'customers';
}

// Message handling functions
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        setFlashMessage($message, $type);
    }
    header("Location: $url");
    exit();
}

function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'] ?? 'success';
        $message = htmlspecialchars($_SESSION['flash_message']['message'] ?? '');
        
        unset($_SESSION['flash_message']);
        
        return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => strip_tags($message)
    ];
}

// Cart functions
function getCartItems($userId) {
    $cart = readJsonFile(CART_FILE);
    return $cart[$userId] ?? [];
}

function addToCart($userId, $productId, $quantity) {
    $cart = readJsonFile(CART_FILE);
    if (!isset($cart[$userId])) {
        $cart[$userId] = [];
    }
    
    $cart[$userId][] = [
        'productId' => $productId,
        'quantity' => $quantity,
        'addedAt' => date('Y-m-d H:i:s')
    ];
    
    return writeJsonFile(CART_FILE, $cart);
}

function removeFromCart($userId, $cartItemId) {
    $cart = readJsonFile(CART_FILE);
    if (isset($cart[$userId])) {
        unset($cart[$userId][$cartItemId]);
        $cart[$userId] = array_values($cart[$userId]);
    }
    return writeJsonFile(CART_FILE, $cart);
}

function clearCart($userId) {
    $cart = readJsonFile(CART_FILE);
    if (isset($cart[$userId])) {
        unset($cart[$userId]);
    }
    return writeJsonFile(CART_FILE, $cart);
}

function getCartTotal($userId) {
    $cart = readJsonFile(CART_FILE);
    $total = 0;
    
    if (isset($cart[$userId])) {
        foreach ($cart[$userId] as $item) {
            $product = new Product($item['productId']);
            $total += $product->getUnitCost() * $item['quantity'];
        }
    }
    
    return $total;
}

function getCartCount($userId) {
    $cart = readJsonFile(CART_FILE);
    if (!isset($cart[$userId])) {
        return 0;
    }
    
    $count = 0;
    foreach ($cart[$userId] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// Order functions
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function getOrderStatus($orderId) {
    $orders = readJsonFile(ORDERS_FILE);
    foreach ($orders['orders'] as $order) {
        if ($order['orderID'] === $orderId) {
            return $order['status'];
        }
    }
    return null;
}

function updateOrderStatus($orderId, $status) {
    $orders = readJsonFile(ORDERS_FILE);
    foreach ($orders['orders'] as &$order) {
        if ($order['orderID'] === $orderId) {
            $order['status'] = $status;
            $order['updatedAt'] = date('Y-m-d H:i:s');
            break;
        }
    }
    return writeJsonFile(ORDERS_FILE, $orders);
}

// Payment functions
function getPaymentStatus($orderId) {
    $payments = readJsonFile(PAYMENTS_FILE);
    foreach ($payments['payments'] as $payment) {
        if ($payment['orderID'] === $orderId) {
            return $payment['status'];
        }
    }
    return null;
}

function updatePaymentStatus($orderId, $status) {
    $payments = readJsonFile(PAYMENTS_FILE);
    foreach ($payments['payments'] as &$payment) {
        if ($payment['orderID'] === $orderId) {
            $payment['status'] = $status;
            $payment['updatedAt'] = date('Y-m-d H:i:s');
            break;
        }
    }
    return writeJsonFile(PAYMENTS_FILE, $payments);
}