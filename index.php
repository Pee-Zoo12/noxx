<?php
require_once 'includes/init.php';
$pageTitle = 'Home';
include 'includes/templates/header.php';
?>
    <section class="hero-section">
        <div class="hero-content">
            <h1>Welcome to NOX</h1>
            <p>Discover the latest trends in fashion and express your unique style</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
    </section>

    <section class="featured-products">
        <div class="container">
            <h2>Shop by Category</h2>
            <div class="category-grid">
                <a href="products.php?category=men" class="category-card">
                    <img src="assets/images/24.png" alt="Men's Wear">
                    <h3>Men's Wear</h3>
                </a>
                <a href="products.php?category=women" class="category-card">
                    <img src="assets/images/25.png" alt="Women's Wear">
                    <h3>Women's Wear</h3>
                </a>
                <a href="products.php?category=footwear" class="category-card">
                    <img src="assets/images/22.png" alt="Footwear">
                    <h3>Footwear</h3>
                </a>
                <a href="products.php?category=accessories" class="category-card">
                    <img src="assets/images/15.png" alt="Accessories">
                    <h3>Accessories</h3>
                </a>
            </div>
        </div>
    </section>

    <section class="newsletter">
        <div class="container">
            <h2>Stay Updated</h2>
            <p>Subscribe to our newsletter for the latest updates and exclusive offers</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email" required>
                <button type="submit" class="btn">Subscribe</button>
            </form>
        </div>
    </section>

    <?php include 'includes/templates/footer.php'; ?>
