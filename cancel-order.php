<?php
require_once 'includes/init.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Please log in to cancel an order.'
    ];
    header('Location: login.php');
    exit();
}

// Ensure order ID is provided
$orderID = $_POST['order_id'] ?? null;
if (!$orderID) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Order ID not provided.'
    ];
    header('Location: orders.php');
    exit();
}

// Retrieve order using Orders class
$order = Orders::getByID($orderID);
if (!$order) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Order not found.'
    ];
    header('Location: orders.php');
    exit();
}

// Ensure the logged-in user owns the order
if ($order->getCustomerID() != $_SESSION['user_id']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You are not authorized to cancel this order.'
    ];
    header('Location: orders.php');
    exit();
}

// Prevent cancellation if already delivered
if ($order->getStatus() === 'delivered') {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'This order cannot be cancelled as it has already been delivered.'
    ];
    header('Location: view-order.php?order_id=' . urlencode($orderID));
    exit();
}

// Attempt to cancel the order
if ($order->cancelOrder()) {
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Order has been cancelled successfully.'
    ];
    header('Location: index.php');
    exit();
} else {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Failed to cancel order. Please try again.'
    ];
    header('Location: view-order.php?order_id=' . urlencode($orderID));
    exit();
}

