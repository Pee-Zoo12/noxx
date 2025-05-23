<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your cart.', 'error');
    header('Location: login.php');
    exit;
}

// Initialize cart
$cart = new ShoppingCart($_SESSION['user_id']);

// ✅ Load all products for both POST and GET requests
$products = readJsonFile(PRODUCTS_FILE)['products'] ?? [];

// Handle POST requests for cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int) ($_POST['quantity'] ?? 1);

            $product = null;
            foreach ($products as $p) {
                if ($p['productID'] === $productId) {
                    $product = $p;
                    break;
                }
            }

            if ($product && $product['stockQuantity'] >= $quantity) {
                if ($cart->addCartItem($productId, $quantity)) {
                    setFlashMessage("Item added to cart successfully!", "success");
                } else {
                    setFlashMessage("Failed to add item to cart. Please try again.", "error");
                }
            } else {
                setFlashMessage("Insufficient stock available.", "error");
            }
            break;

        case 'update':
            $cartItemId = $_POST['cart_item_id'] ?? '';
            $quantity = (int) ($_POST['quantity'] ?? 1);

            $cartItem = null;
            foreach ($cart->getItems() as $item) {
                if ($item['cartItemID'] === $cartItemId) {
                    $cartItem = $item;
                    break;
                }
            }

            $product = null;
            if ($cartItem) {
                foreach ($products as $p) {
                    if ($p['productID'] === $cartItem['productID']) {
                        $product = $p;
                        break;
                    }
                }
            }

            if ($product && $product['stockQuantity'] >= $quantity) {
                if ($cart->updateQuantity($cartItemId, $quantity)) {
                    setFlashMessage("Cart updated successfully!", "success");
                } else {
                    setFlashMessage("Failed to update cart. Please try again.", "error");
                }
            } else {
                setFlashMessage("Insufficient stock available.", "error");
            }
            break;

        case 'remove':
            $cartItemId = $_POST['cart_item_id'] ?? '';
            if ($cart->removeCartItem($cartItemId)) {
                setFlashMessage("Item removed from cart successfully!", "success");
            } else {
                setFlashMessage("Failed to remove item from cart. Please try again.", "error");
            }
            break;

        case 'update_shipping':
            $shippingInfo = [
                'shippingID' => uniqid(),
                'shippingType' => $_POST['shipping_type'],
                'shippingCost' => 50.00,
                'islandGroup' => $_POST['island_group'],
                'region' => $_POST['region'],
                'province' => $_POST['province'],
                'city' => $_POST['city'],
                'shippingAddress' => $_POST['address'],
                'paymentMethod' => $_POST['payment_method']
            ];
            if ($_POST['payment_method'] === 'debit_card') {
                $shippingInfo['cardNumber'] = $_POST['card_number'];
                $shippingInfo['cardHolderName'] = $_POST['card_holder_name'];
                $shippingInfo['expiryDate'] = $_POST['expiry_date'];
                $shippingInfo['cvv'] = $_POST['cvv'];
            }
            if ($cart->updateShippingInfo($shippingInfo)) {
                setFlashMessage("Shipping and payment information updated.", "success");
            } else {
                setFlashMessage("Failed to update shipping information.", "error");
            }
            break;
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Get cart details
$cartDetails = $cart->viewCartDetails();

// Include header
require_once 'includes/templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <main class="cart-page">
        <div class="container">
            <h1 class="page-title text-center">Shopping Cart</h1>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div
                    class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?>">
                    <?php
                    echo $_SESSION['flash_message']['message'];
                    unset($_SESSION['flash_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cartDetails['items'])): ?>
                <div class="empty-cart">
                    <div class="empty-cart-content">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty.</p>
                        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php
                        // Prepare product stock lookup for all items
                        $productStockMap = [];
                        foreach ($products as $product) {
                            $productStockMap[$product['productID']] = $product['stockQuantity'];
                        }
                        ?>
                        <?php foreach ($cartDetails['items'] as $item): ?>
                            <div class="cart-item">
                                <div class="item-select">
                                    <input type="checkbox" name="selected_items[]" value="<?php echo $item['cartItemID']; ?>"
                                        class="item-checkbox" checked>
                                </div>
                                <div class="item-image">
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($item['imageUrl']); ?>"
                                        alt="<?php echo htmlspecialchars($item['productName']); ?>"
                                        onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/noxlogo.png';">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['productName']); ?></h3>
                                    <p class="product-id">ID: <?php echo htmlspecialchars($item['productID']); ?></p>
                                    <p class="price">₱<?php echo number_format($item['unitCost'], 2); ?></p>

                                    <form action="cart.php" method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cartItemID']; ?>">
                                        <div class="quantity-controls">
                                            <button type="button" class="quantity-btn minus"
                                                onclick="updateQuantity(this, -1)">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                min="1" max="<?php echo $productStockMap[$item['productID']] ?? 1; ?>"
                                                class="quantity-input" onchange="validateQuantity(this)">
                                            <button type="button" class="quantity-btn plus"
                                                onclick="updateQuantity(this, 1)">+</button>
                                        </div>
                                    </form>

                                    <form action="cart.php" method="POST" class="remove-form">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cartItemID']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                                <div class="item-total">
                                    <p>Total: ₱<?php echo number_format($item['itemTotal'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-section">
                            <h2>Order Summary</h2>
                            <div class="summary-item">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($cartDetails['subtotal'], 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Shipping:</span>
                                <span>₱50.00</span>
                            </div>
                            <div class="summary-item total">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($cartDetails['subtotal'] + 50, 2); ?></span>
                            </div>
                        </div>

                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/cart.js"></script>
</body>

</html>