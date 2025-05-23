<?php
require_once 'includes/init.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$cart = new ShoppingCart($_SESSION['user_id']);
$cartDetails = $cart->viewCartDetails();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingType = $_POST['shipping_type'];
    $paymentMethod = $_POST['payment_method'];
    $eMoneyType = $_POST['e_money_type'] ?? null;
    $accountNumber = $_POST['account_number'] ?? null;

    $cartDetails = $cart->viewCartDetails();
    if (empty($cartDetails['items'])) {
        $error = 'Your cart is empty. Please add items before checkout.';
    } else {
        $shippingInfo = [
            'shippingID' => uniqid(),
            'shippingType' => $shippingType,
            'shippingCost' => 50.00,
            'shippingAddress' => [
                'island_group' => $_POST['island_group'],
                'region' => $_POST['region'],
                'province' => $_POST['province'],
                'city' => $_POST['city'],
                'barangay' => $_POST['barangay'],
                'street' => $_POST['street']
            ],
            'paymentMethod' => $paymentMethod,
            'eMoneyType' => $eMoneyType,
            'accountNumber' => $accountNumber,
            'items' => $cartDetails['items']
        ];

        if ($paymentMethod === 'debit_card') {
            $shippingInfo['cardNumber'] = $_POST['card_number'] ?? '';
            $shippingInfo['cardHolderName'] = $_POST['card_holder_name'] ?? '';
            $shippingInfo['expiryDate'] = $_POST['expiry_date'] ?? '';
            $shippingInfo['cvv'] = $_POST['cvv'] ?? '';
        }

        $_SESSION['shipping_info'] = $shippingInfo;

        if ($cart->updateShippingInfo($shippingInfo)) {
            $orderID = $cart->checkout();
            if ($orderID) {
                $_SESSION['current_order_id'] = $orderID;
                unset($_SESSION['error']);
                header('Location: order-summary.php');
                exit();
            } else {
                $error = 'Failed to process order. Please try again.';
            }
        } else {
            $error = 'Failed to update shipping information. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout - NOX Clothing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php include 'includes/templates/header.php'; ?>

    <main>
        <section class="checkout-section">
            <h2>Checkout</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($cartDetails['items'])): ?>
                <div class="empty-cart">
                    <p>Your cart is empty</p>
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="checkout-container">
                    <div class="checkout-form">
                        <form method="POST" class="shipping-form">
                            <h3>Shipping Information</h3>
                            <div class="form-group">
                                <label for="island_group">Island Group</label>
                                <select id="island_group" name="island_group" required>
                                    <option value="">Select Island Group</option>
                                    <option value="luzon">Luzon</option>
                                    <option value="visayas">Visayas</option>
                                    <option value="mindanao">Mindanao</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="region">Region</label>
                                <input type="text" id="region" name="region" required>
                            </div>
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" id="province" name="province" required>
                            </div>
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="barangay">Barangay</label>
                                <input type="text" id="barangay" name="barangay" required>
                            </div>
                            <div class="form-group">
                                <label for="street">Street Address</label>
                                <input type="text" id="street" name="street" required>
                            </div>

                            <h3>Payment Method</h3>
                            <div class="form-group">
                                <label for="payment_method">Select Payment Method</label>
                                <select id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cod">Cash on Delivery</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="e_money">E-Money</option>
                                </select>
                            </div>

                            <div id="e_money_fields" style="display: none;">
                                <div class="form-group">
                                    <label for="e_money_type">E-Money Type</label>
                                    <select id="e_money_type" name="e_money_type">
                                        <option value="">Select E-Money Type</option>
                                        <option value="gcash">GCash</option>
                                        <option value="paymaya">PayMaya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="account_number">Account Number</label>
                                    <input type="text" id="account_number" name="account_number">
                                </div>
                            </div>

                            <div id="debit_card_fields" style="display: none;">
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" maxlength="19"
                                        onkeyup="formatCardNumber(this)">
                                </div>
                                <div class="form-group">
                                    <label for="card_holder_name">Card Holder Name</label>
                                    <input type="text" id="card_holder_name" name="card_holder_name">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY"
                                            maxlength="5">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" name="cvv" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="shipping_type">Shipping Type</label>
                                <select id="shipping_type" name="shipping_type" required>
                                    <option value="">Select Shipping Type</option>
                                    <option value="standard">Standard Shipping</option>
                                    <option value="express">Express Shipping</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </form>
                    </div>

                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-items">
                            <?php foreach ($cartDetails['items'] as $item): ?>
                                <div class="summary-item">
                                    <span class="item-name"><?php echo htmlspecialchars($item['productName']); ?></span>
                                    <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                    <span class="item-price">₱<?php echo number_format($item['itemTotal'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($cartDetails['subtotal'], 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>₱50.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($cartDetails['subtotal'] + 50, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/templates/footer.php'; ?>

    <script>
        document.getElementById('payment_method').addEventListener('change', function () {
            const eMoneyFields = document.getElementById('e_money_fields');
            const debitCardFields = document.getElementById('debit_card_fields');
            eMoneyFields.style.display = this.value === 'e_money' ? 'block' : 'none';
            debitCardFields.style.display = this.value === 'debit_card' ? 'block' : 'none';
        });
    </script>
</body>

</html>