<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get order history for current user
$orderHistory = Orders::getByCustomerID($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - NOX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include 'includes/templates/header.php'; ?>

    <main class="container py-5">
        <h1 class="text-center mb-4">Order History</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orderHistory)): ?>
            <div class="alert alert-info">
                <p class="text-center">No orders found.</p>
                <div class="text-center">
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderHistory as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order->getOrderID()) ?></td>
                                <td><?= htmlspecialchars($order->getOrderDate()) ?></td>
                                <td>
                                    <?php foreach ($order->getItems() as $item): ?>
                                        <div class="mb-1">
                                            <?= htmlspecialchars($item['productName']) ?>
                                            (<?= $item['quantity'] ?> x ₱<?= number_format($item['price'], 2) ?>)
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td>₱<?= number_format($order->getTotal(), 2) ?></td>
                                <td><?= htmlspecialchars($order->getStatus()) ?></td>
                                <td><?= htmlspecialchars($order->getPaymentStatus()) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>