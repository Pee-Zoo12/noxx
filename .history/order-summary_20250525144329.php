<?php
require_once 'includes/init.php';

// Authentication and validation
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['current_order_id'])) {
    header('Location: index.php');
    exit();
}

// Get order details
$orderID = $_SESSION['current_order_id'];
$order = Orders::getByID($orderID);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get shipping and payment information
$shippingInfo = $_SESSION['shipping_info'] ?? null;
$paymentMethod = $order->getPaymentMethod();
$paymentMethodDisplay = ucwords(str_replace('_', ' ', $paymentMethod));

// Prepare order details
$cartDetails = [
    'items' => $order->getItems(),
    'subtotal' => $order->getSubtotal(),
    'shipping' => ['cost' => $order->getShippingCost()],
    'total' => $order->getTotal()
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary - NOX Clothing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php include 'includes/templates/header.php'; ?>

    <main>
        <section class="order-summary-section">
            <div class="container">
                <div class="order-confirmation">
                    <!-- Order Confirmation Header -->
                    <div class="confirmation-header">
                        <i class="fas fa-check-circle"></i>
                        <h2>Order Confirmed!</h2>
                        <p>Thank you for your purchase. Your order has been received.</p>
                    </div>

                    <div class="order-details">
                        <!-- Order Information -->
                        <div class="order-info">
                            <h3>Order Information</h3>
                            <p><strong>Order ID:</strong> <?php echo $order->getOrderID(); ?></p>
                            <p><strong>Order Date:</strong>
                                <?php echo date('F j, Y', strtotime($order->getOrderDate())); ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst($order->getStatus()); ?></p>
                        </div>

                        <!-- Order Items -->
                        <div class="order-items">
                            <h3>Order Summary</h3>
                            <?php foreach ($cartDetails['items'] as $item): ?>
                                <div class="order-item">
                                    <img src="<?php
                                    $imageUrl = isset($item['imageUrl']) && !empty($item['imageUrl'])
                                        ? SITE_URL . '/' . $item['imageUrl']
                                        : SITE_URL . '/assets/images/noxlogo.png';
                                    echo $imageUrl;
                                    ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" class="item-image"
                                        onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/noxlogo.png';">
                                    <div class="item-details">
                                        <span class="item-name"><?php echo htmlspecialchars($item['productName']); ?></span>
                                        <span class="item-id">Product ID:
                                            <?php echo htmlspecialchars($item['productID']); ?></span>
                                        <span class="item-quantity">Quantity: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="item-price">₱<?php echo number_format($item['itemTotal'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Shipping Information -->
                        <div class="shipping-info">
                            <h3>Shipping Information</h3>
                            <?php if ($shippingInfo && isset($shippingInfo['shippingAddress'])): ?>
                                <p>
                                    <?php
                                    $address = $shippingInfo['shippingAddress'];
                                    $addressParts = [
                                        $address['street'] ?? '',
                                        $address['barangay'] ?? '',
                                        $address['city'] ?? '',
                                        $address['province'] ?? '',
                                        $address['region'] ?? '',
                                        $address['island_group'] ?? ''
                                    ];
                                    echo htmlspecialchars(implode(', ', array_filter($addressParts)));
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Information -->
                        <div class="payment-info">
                            <h3>Payment Information</h3>
                            <p><strong>Payment Method:</strong> <?php echo $paymentMethodDisplay; ?></p>
                            <?php if ($paymentMethod === 'e_money' && isset($shippingInfo['eMoneyType'])): ?>
                                <p><strong>E-Money Type:</strong> <?php echo ucfirst($shippingInfo['eMoneyType']); ?></p>
                                <p><strong>Account Number:</strong> <?php echo $shippingInfo['accountNumber']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Tracking Information -->
                        <?php if ($order->getTrackingNumber()): ?>
                            <div class="tracking-info">
                                <h3>Tracking Information</h3>
                                <p><strong>Tracking Number:</strong> <span
                                        class="tracking-number"><?php echo $order->getTrackingNumber(); ?></span></p>
                                <?php if ($order->getEstimatedDelivery()): ?>
                                    <p><strong>Estimated Delivery:</strong> <span
                                            class="estimated-delivery"><?php echo date('F j, Y', strtotime($order->getEstimatedDelivery())); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Order Total -->
                        <div class="order-total">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($cartDetails['subtotal'], 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Shipping:</span>
                                <span>₱<?php echo number_format($cartDetails['shipping']['cost'], 2); ?></span>
                            </div>
                            <div class="total-row final">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($cartDetails['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <form action="cancel-order.php" method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order->getOrderID(); ?>">
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to cancel this order?');">Cancel
                                Order</button>

                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>

</html>