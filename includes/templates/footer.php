<?php if (!defined('SITE_URL')) exit; ?>

<?php
$company_name = isset($company) && $company->getName() ? $company->getName() : 'NOX Clothing';
$current_year = date('Y');
?>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Quick Links -->
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/products.php"><i class="fas fa-shopping-bag"></i> Shop</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p><i class="fas fa-map-marker-alt"></i> 123 Fashion Street, Manila, Philippines</p>
                <p><i class="fas fa-phone"></i> +63 123 456 7890</p>
                <p><i class="fas fa-envelope"></i> info@noxclothing.com</p>
            </div>

            <!-- Social Links -->
            <div class="footer-social">
                <h4>Follow Us</h4>
                <p>Stay connected with us on social media for the latest updates and exclusive offers.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/share/1GCEvjoCDM/?mibextid=wwXI" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/_nox.apparel?igsh=MTJ6ZmJxZ29ybWIwbg==" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> NOX Clothing. All rights reserved.</p>
        </div>
    </div>
</footer>



<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Custom JavaScript -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<?php if (basename($_SERVER['PHP_SELF']) === 'cart.php'): ?>
<!-- Cart specific JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/cart.js"></script>
<?php endif; ?>

</body>
</html>