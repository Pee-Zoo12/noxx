<?php
require_once 'includes/init.php';


$company = Company::getInstance();

$pageTitle = 'About Us - ' . $company->getName();
include 'includes/templates/header.php';
?>

<main class="about-page">
    <div class="container py-5">
        <!-- Hero Section -->
        <div class="section-card text-center mb-5">
            <img src="<?php echo SITE_URL . '/' . $company->getLogo(); ?>"
                alt="<?php echo htmlspecialchars($company->getName()); ?>" class="company-logo mb-4">
            <h1 class="display-4 mb-3"><?php echo htmlspecialchars($company->getName()); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($company->getBrandIdentity()); ?></p>
        </div>

        <!-- Company Information Section -->
        <div class="section-card mb-5">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="section-title mb-4">Company Details</h2>
                    <div class="company-details">
                        <p><strong>Registration Number:</strong>
                            <?php echo htmlspecialchars($company->getRegistrationNumber()); ?></p>
                        <p><strong>Tax ID:</strong> <?php echo htmlspecialchars($company->getTaxID()); ?></p>
                        <p><strong>Business Type:</strong> <?php echo htmlspecialchars($company->getBusinessType()); ?>
                        </p>
                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($company->getIndustry()); ?></p>
                        <p><strong>Founded:</strong> <?php echo htmlspecialchars($company->getFoundingDate()); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="section-title mb-4">Our Mission</h2>
                    <p class="mission-text">
                        <?php echo htmlspecialchars($company->getMissionStatement()); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="section-card mb-5">
            <h2 class="section-title text-center mb-4">Our Values</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <i class="fas fa-star fa-3x mb-3"></i>
                        <h3>Quality</h3>
                        <p>We are committed to providing high-quality products that meet the highest standards.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <i class="fas fa-heart fa-3x mb-3"></i>
                        <h3>Customer Focus</h3>
                        <p>Our customers are at the heart of everything we do, ensuring their satisfaction is our
                            priority.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <i class="fas fa-leaf fa-3x mb-3"></i>
                        <h3>Sustainability</h3>
                        <p>We are dedicated to sustainable practices and reducing our environmental impact.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="section-card">
            <h2 class="section-title text-center mb-4">Connect With Us</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="contact-info text-center">
                        <div class="contact-details">
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($company->getEmail()); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($company->getPhone()); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php
                            $address = $company->getAddress();
                            echo htmlspecialchars($address['street'] ?? '') . ', ' .
                                htmlspecialchars($address['city'] ?? '') . ', ' .
                                htmlspecialchars($address['country'] ?? '');
                            ?></p>
                        </div>
                        <div class="social-links">
                            <a href="https://www.facebook.com/share/1GCEvjoCDM/?mibextid=wwXI" target="_blank"
                                title="Follow us on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/_nox.apparel?igsh=MTJ6ZmJxZ29ybWIwbg==" target="_blank"
                                title="Follow us on Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/templates/footer.php'; ?>