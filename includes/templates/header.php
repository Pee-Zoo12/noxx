<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../init.php';
}

// Initialize variables with proper checks
$cartCount = isset($cart) ? $cart->getCartCount() : 0;
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$username = $isLoggedIn && isset($user) ? $user->getUsername() : '';

// Ensure Font Awesome and other required CSS are loaded
if (!isset($headerAssetsLoaded)):
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - NOX Clothing' : 'NOX Clothing'; ?></title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">

        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    </head>

    <body>
        <?php
        $headerAssetsLoaded = true;
endif;
?>

    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>" class="logo-link">
                        <img src="<?php echo SITE_URL; ?>/assets/images/noxlogo.png"
                            alt="<?php echo htmlspecialchars($company->getName() ?? 'NOX Clothing'); ?>"
                            class="logo-image">
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button type="button" class="menu-toggle" aria-label="Toggle navigation menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Main Navigation -->
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                    </ul>
                </nav>

                <!-- Menu Overlay -->
                <div class="menu-overlay"></div>

                <!-- Header Actions -->
                <div class="header-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo SITE_URL; ?>/cart.php" class="cart-link" title="View Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>

                        <div class="user-menu">
                            <button type="button" class="user-menu-toggle" aria-label="Toggle user menu"
                                aria-expanded="false" aria-controls="userDropdown">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                                </div>
                            </button>
                            <div class="user-dropdown" id="userDropdown" role="menu">
                                <div class="user-info">
                                    <div class="user-avatar-large">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                                        <div class="user-email">
                                            <?php echo isset($user) ? htmlspecialchars($user->getEmail()) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-user-circle"></i> My Profile
                                </a>
                                <a href="<?php echo SITE_URL; ?>/purchase-history.php" class="dropdown-item"
                                    role="menuitem">
                                    <i class="fas fa-shopping-bag"></i> Purchase History
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo SITE_URL; ?>/logout.php" class="dropdown-item text-danger"
                                    role="menuitem">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary">Login</a>
                            <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-secondary">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php if (function_exists('displayFlashMessage'))
        echo displayFlashMessage(); ?>