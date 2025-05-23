// Toggle payment fields visibility
function togglePaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const eMoneyFields = document.getElementById('e_money_fields');
    const debitCardFields = document.getElementById('debit_card_fields');

    eMoneyFields.style.display = paymentMethod === 'e_money' ? 'block' : 'none';
    debitCardFields.style.display = paymentMethod === 'debit_card' ? 'block' : 'none';
}

// Format card number with spaces
function formatCardNumber(input) {
    let value = input.value.replace(/\D/g, '');
    let formattedValue = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    input.value = formattedValue;
}

// Format expiry date
function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    input.value = value;
}

// Validate CVV
function validateCVV(input) {
    input.value = input.value.replace(/\D/g, '');
}

// Update quantity
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('.quantity-input');
    const currentValue = parseInt(input.value);
    const newValue = currentValue + change;
    const maxValue = parseInt(input.getAttribute('max'));
    
    if (newValue >= 1 && newValue <= maxValue) {
        input.value = newValue;
        input.form.submit();
    }
}

// Validate quantity
function validateQuantity(input) {
    const value = parseInt(input.value);
    const max = parseInt(input.getAttribute('max'));
    
    if (value < 1) {
        input.value = 1;
    } else if (value > max) {
        input.value = max;
    }
    
    input.form.submit();
}

function updateItemTotal(input) {
    const cartItem = input.closest('.cart-item');
    const price = parseFloat(cartItem.querySelector('.price').textContent.replace('₱', '').replace(',', ''));
    const quantity = parseInt(input.value);
    const total = price * quantity;
    
    // Update item total
    cartItem.querySelector('.item-total').textContent = 'Total: ₱' + total.toFixed(2);
    
    // Update cart summary
    updateCartSummary();
}

function updateCartSummary() {
    let subtotal = 0;
    const selectedItems = document.querySelectorAll('.item-checkbox:checked');
    
    selectedItems.forEach(checkbox => {
        const cartItem = checkbox.closest('.cart-item');
        const itemTotal = parseFloat(cartItem.querySelector('.item-total').textContent.replace('Total: ₱', '').replace(',', ''));
        subtotal += itemTotal;
    });
    
    // Update summary totals
    const subtotalElement = document.querySelector('.summary-item:first-child span:last-child');
    const totalElement = document.querySelector('.summary-item.total span:last-child');
    
    if (subtotalElement && totalElement) {
        subtotalElement.textContent = '₱' + subtotal.toFixed(2);
        totalElement.textContent = '₱' + (subtotal + 50).toFixed(2);
    }
}

// Initialize cart functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for checkboxes
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateCartSummary);
    });

    // Add event listeners for quantity inputs
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            validateQuantity(this);
        });
    });

    const paymentMethod = document.getElementById('payment_method');
    if (paymentMethod) {
        paymentMethod.addEventListener('change', togglePaymentFields);
    }

    const expiryDate = document.getElementById('expiry_date');
    if (expiryDate) {
        expiryDate.addEventListener('input', function() {
            formatExpiryDate(this);
        });
    }

    const cvv = document.getElementById('cvv');
    if (cvv) {
        cvv.addEventListener('input', function() {
            validateCVV(this);
        });
    }
}); 