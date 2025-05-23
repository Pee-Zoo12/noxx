<?php
require_once 'includes/init.php';

$error = '';
$success = '';
$cart = isLoggedIn() ? new ShoppingCart($_SESSION['user_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $error = 'Please log in to add items to your cart.';
    } else {
        $productId = $_POST['product_id'] ?? '';
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($productId) {
            $product = Product::getById($productId);
            if ($product && $product->getStockQuantity() >= $quantity) {
                if ($cart->addCartItem($productId, $quantity)) {
                    $success = 'Product added to cart successfully!';
                } else {
                    $error = 'Failed to add product to cart.';
                }
            } else {
                $error = 'Product is out of stock or invalid quantity.';
            }
        }
    }
}

try {
    $products = readJsonFile(PRODUCTS_FILE)['products'] ?? [];
    $categoryProducts = [];
    foreach ($products as $product) {
        if (!isset($product['category']))
            continue;
        if ($product['status'] === 'active') {
            $categoryId = $product['category'];
            if (!isset($categoryProducts[$categoryId])) {
                $categoryProducts[$categoryId] = [];
            }
            $categoryProducts[$categoryId][] = $product;
        }
    }
    $categories = [
        '1' => ['name' => "Men's Wear", 'icon' => 'fa-male'],
        '2' => ['name' => "Women's Wear", 'icon' => 'fa-female'],
        '3' => ['name' => 'Footwear', 'icon' => 'fa-shoe-prints'],
        '4' => ['name' => 'Accessories', 'icon' => 'fa-gem']
    ];
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Sorry, there was a problem loading the products. Please try again later.";
}

$pageTitle = 'Products - NOX Clothing';
include 'includes/templates/header.php';
?>

<main class="products-page">
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
        <?php foreach ($categories as $categoryId => $category): ?>
            <?php if (!empty($categoryProducts[$categoryId])): ?>
                <section class="category-section">
                    <h2 class="category-title">
                        <i class="fas <?php echo $category['icon']; ?>"></i>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h2>
                    <div class="product-grid">
                        <?php foreach ($categoryProducts[$categoryId] as $product): ?>
                            <div class="product-card">
                                <?php if ($product['stockQuantity'] <= 0): ?>
                                    <div class="out-of-stock-badge">
                                        <i class="fas fa-times-circle"></i> Out of Stock
                                    </div>
                                <?php endif; ?>
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['imageUrl']); ?>"
                                        alt="<?php echo htmlspecialchars($product['productName']); ?>"
                                        onerror="this.onerror=null; this.src='assets/images/noxlogo.png';">
                                    <div class="product-overlay">
                                        <div class="overlay-buttons">
                                            <a href="product-details.php?id=<?php echo $product['productID']; ?>"
                                                class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                            <?php if ($product['stockQuantity'] > 0): ?>
                                                <form action="" method="POST" class="quick-add-form">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['productID']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" name="add_to_cart" class="btn btn-secondary">
                                                        <i class="fas fa-shopping-cart"></i> Quick Add
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="product-details.php?id=<?php echo $product['productID']; ?>">
                                            <?php echo htmlspecialchars($product['productName']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">
                                        ₱<?php echo number_format($product['unitCost'], 2); ?>
                                    </div>
                                    <?php if ($product['stockQuantity'] > 0): ?>
                                        <div class="stock-info">
                                            <i class="fas fa-check-circle"></i> In Stock
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'includes/templates/footer.php'; ?>