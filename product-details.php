<?php
require_once 'includes/init.php';

// Get product ID from URL
$productId = $_GET['id'] ?? null;

if (!$productId) {
    header('Location: products.php');
    exit();
}

// Get product details
$product = Product::getById($productId);

if (!$product) {
    header('Location: products.php');
    exit();
}

$error = '';
$success = '';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $error = 'Please log in to add items to your cart.';
    } else {
        $cart = new ShoppingCart($_SESSION['user_id']);
        $quantity = (int) $_POST['quantity'];

        if ($quantity > 0 && $quantity <= $product->getStockQuantity()) {
            if ($cart->addCartItem($productId, $quantity)) {
                // Refresh the page to update cart count
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $productId . "&success=1");
                exit();
            } else {
                $error = 'Failed to add product to cart.';
            }
        } else {
            $error = 'Invalid quantity selected.';
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Product added to cart successfully!';
}

$pageTitle = $product->getProductName() . ' - NOX Clothing';
include 'includes/templates/header.php';
?>

<div class="product-details-section">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Login</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
                <a href="cart.php" class="btn btn-primary btn-sm">View Cart</a>
            </div>
        <?php endif; ?>

        <div class="product-details-container">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product->getImageUrl()); ?>"
                        alt="<?php echo htmlspecialchars($product->getProductName()); ?>"
                        onerror="this.src='assets/images/noxlogo.png'">
                </div>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($product->getProductName()); ?></h1>

                <div class="product-meta">
                    <p>Product ID: <?php echo htmlspecialchars($product->getProductID()); ?></p>
                    <p>Serial Number: <?php echo htmlspecialchars($product->getSerialNumber()); ?></p>
                </div>

                <div class="product-price">
                    ₱<?php echo number_format($product->getUnitCost(), 2); ?>
                </div>

                <div class="product-stock">
                    <?php if ($product->getStockQuantity() > 0): ?>
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i>
                            In Stock (<?php echo $product->getStockQuantity(); ?> available)
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i>
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product->getDescription())); ?></p>
                </div>

                <?php if ($product->getStockQuantity() > 0): ?>
                    <form action="" method="POST" class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1"
                                max="<?php echo $product->getStockQuantity(); ?>" class="quantity-input">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>