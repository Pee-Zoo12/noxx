// Utility Functions
const utils = {
    isValidEmail: (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    formatCurrency: (amount) => {
        return '₱' + parseFloat(amount).toFixed(2);
    },
    
    validateRequiredFields: (form) => {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        return isValid;
    }
};

// Mobile Menu Functionality
function initializeMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const menuOverlay = document.querySelector('.menu-overlay');
    const body = document.body;

    if (!menuToggle || !mainNav || !menuOverlay) return;

    function toggleMenu(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const isOpen = menuToggle.classList.contains('active');
        isOpen ? closeMenu() : openMenu();
    }

    function openMenu() {
        menuToggle.classList.add('active');
        mainNav.classList.add('show');
        menuOverlay.classList.add('active');
        body.classList.add('menu-open');
    }

    function closeMenu() {
        menuToggle.classList.remove('active');
        mainNav.classList.remove('show');
        menuOverlay.classList.remove('active');
        body.classList.remove('menu-open');
    }

    menuToggle.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', closeMenu);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
    });
}

// User Menu Functionality
function initializeUserMenu() {
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    const userDropdown = document.querySelector('.user-dropdown');
    const body = document.body;

    if (!userMenuToggle || !userDropdown) return;

    function toggleUserMenu(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const isOpen = userDropdown.classList.contains('show');
        isOpen ? closeUserMenu() : openUserMenu();
    }

    function openUserMenu() {
        userDropdown.classList.add('show');
        body.addEventListener('click', handleOutsideClick);
        document.addEventListener('keydown', handleEscapeKey);
    }

    function closeUserMenu() {
        userDropdown.classList.remove('show');
        body.removeEventListener('click', handleOutsideClick);
        document.removeEventListener('keydown', handleEscapeKey);
    }

    function handleOutsideClick(event) {
        if (!userDropdown.contains(event.target) && !userMenuToggle.contains(event.target)) {
            closeUserMenu();
        }
    }

    function handleEscapeKey(event) {
        if (event.key === 'Escape') closeUserMenu();
    }

    userMenuToggle.addEventListener('click', toggleUserMenu);
    }

// Product Gallery
function initializeProductGallery() {
    const mainImage = document.querySelector('.main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                mainImage.src = this.src;
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
}
    
// Cart Functionality
function initializeCart() {
    const cartItems = document.querySelectorAll('.cart-item');
    const cartTotal = document.querySelector('.summary-item.total span:last-child');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    function updateCartTotal() {
        if (!cartItems.length || !cartTotal) return;

        let total = 0;
        cartItems.forEach(item => {
            const checkbox = item.querySelector('.item-checkbox');
            if (checkbox && checkbox.checked) {
                const price = parseFloat(item.querySelector('.price').textContent.replace('₱', '').replace(',', ''));
                const quantity = parseInt(item.querySelector('.quantity-input')?.value || 1);
                total += price * quantity;
            }
        });

        total += 50; // ₱50 shipping
        cartTotal.textContent = utils.formatCurrency(total);
    }

    if (itemCheckboxes.length) {
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCartTotal);
        });

    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
                updateCartTotal();
        });
    });
    }
}

// Form Handling
function initializeForms() {
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!utils.validateRequiredFields(this)) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Newsletter subscription
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (utils.isValidEmail(email)) {
                alert('Thank you for subscribing! We will keep you updated.');
                this.reset();
            } else {
                alert('Please enter a valid email address.');
            }
        });
    }

    // Password visibility toggle
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // Payment method toggle
    const paymentMethod = document.getElementById('payment_method');
    const eMoneyDetails = document.getElementById('e-money-details');
    if (paymentMethod && eMoneyDetails) {
        paymentMethod.addEventListener('change', function() {
            eMoneyDetails.style.display = this.value === 'e-money' ? 'block' : 'none';
        });
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Create menu overlay if it doesn't exist
    let menuOverlay = document.querySelector('.menu-overlay');
    if (!menuOverlay) {
        menuOverlay = document.createElement('div');
        menuOverlay.className = 'menu-overlay';
        document.body.appendChild(menuOverlay);
    }

    initializeMobileMenu();
    initializeUserMenu();
    initializeProductGallery();
    initializeCart();
    initializeForms();

    // Initialize lazy loading
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Quantity input validation
const quantityInputs = document.querySelectorAll('input[type="number"]');
quantityInputs.forEach(input => {
    input.addEventListener('change', function() {
        if (this.value < 1) {
            this.value = 1;
            }
        });
    });
    
    // Price range filter
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (minPriceInput && maxPriceInput) {
        minPriceInput.addEventListener('change', validatePriceRange);
        maxPriceInput.addEventListener('change', validatePriceRange);
    }
    
    function validatePriceRange() {
        const min = parseFloat(minPriceInput.value);
        const max = parseFloat(maxPriceInput.value);
        
        if (min && max && min > max) {
            alert('Minimum price cannot be greater than maximum price.');
            minPriceInput.value = '';
            maxPriceInput.value = '';
        }
    }

    // Handle checkbox changes for cart items
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotal);
    });

    // Initialize address selection if elements exist
    const islandGroupSelect = document.getElementById('island_group');
    if (islandGroupSelect) {
        islandGroupSelect.addEventListener('change', updateRegions);
}

// Address selection functions
function updateRegions() {
    const islandGroup = document.getElementById('island_group').value;
    const regionSelect = document.getElementById('region');
    regionSelect.innerHTML = '<option value="">Select Region</option>';
    
    if (islandGroup) {
        const regions = getRegionsByIslandGroup(islandGroup);
        regions.forEach(region => {
            const option = document.createElement('option');
            option.value = region.code;
            option.textContent = region.name;
            regionSelect.appendChild(option);
        });
    }
    
    // Reset dependent fields
    document.getElementById('province').innerHTML = '<option value="">Select Province</option>';
    document.getElementById('city').innerHTML = '<option value="">Select City/Municipality</option>';
    document.getElementById('barangay').innerHTML = '<option value="">Select Barangay (Optional)</option>';
}

