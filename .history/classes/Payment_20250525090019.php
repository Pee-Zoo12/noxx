<?php
if (!defined('PAYMENTS_FILE')) {
    define('PAYMENTS_FILE', __DIR__ . '/../data/payments.json');
}

class Payment {
    private $paymentID;
    private $orderID;
    private $amount;
    private $paymentMethod;
    private $paymentDate;
    private $status;
    private $transactionID;
    private $cardLast4;
    private $cardType;
    private $billingAddress;

    public function __construct() {
        $this->paymentDate = date('Y-m-d H:i:s');
        $this->status = 'pending';
    }

    // Getters and Setters
    public function getPaymentID() {
        return $this->paymentID;
    }

    public function setPaymentID($paymentID) {
        $this->paymentID = $paymentID;
    }

    public function getOrderID() {
        return $this->orderID;
    }

    public function setOrderID($orderID) {
        $this->orderID = $orderID;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentDate() {
        return $this->paymentDate;
    }

    public function setPaymentDate($paymentDate) {
        $this->paymentDate = $paymentDate;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getTransactionID() {
        return $this->transactionID;
    }

    public function setTransactionID($transactionID) {
        $this->transactionID = $transactionID;
    }

    public function getCardLast4() {
        return $this->cardLast4;
    }

    public function setCardLast4($cardLast4) {
        $this->cardLast4 = $cardLast4;
    }

    public function getCardType() {
        return $this->cardType;
    }

    public function setCardType($cardType) {
        $this->cardType = $cardType;
    }

    public function getBillingAddress() {
        return $this->billingAddress;
    }

    public function setBillingAddress($billingAddress) {
        $this->billingAddress = $billingAddress;
    }

    // Process payment
    public function processPayment() {
        try {
            // Validate payment data
            if (!$this->validatePaymentData()) {
                return false;
            }

            // Generate transaction ID
            $this->transactionID = generateUniqueId();

            // Process payment based on method
            switch ($this->paymentMethod) {
                case 'credit_card':
                    $result = $this->processCreditCardPayment();
                    break;
                case 'paypal':
                    $result = $this->processPayPalPayment();
                    break;
                default:
                    throw new Exception("Unsupported payment method");
            }

            if ($result) {
                $this->status = 'completed';
                $this->save();
                
                // Update order payment status
                $order = Orders::getByID($this->orderID);
                if ($order) {
                    $order->updatePaymentStatus('paid');
                }
                
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Error processing payment: " . $e->getMessage());
            $this->status = 'failed';
            $this->save();
            return false;
        }
    }

    // Process credit card payment
    private function processCreditCardPayment() {
        // In a real application, this would integrate with a payment gateway
        // For demo purposes, we'll simulate a successful payment
        return true;
    }

    // Process PayPal payment
    private function processPayPalPayment() {
        // In a real application, this would integrate with PayPal API
        // For demo purposes, we'll simulate a successful payment
        return true;
    }

    // Validate payment data
    private function validatePaymentData() {
        if (!$this->orderID || !$this->amount || !$this->paymentMethod) {
            return false;
        }

        if ($this->amount <= 0) {
            return false;
        }

        if ($this->paymentMethod === 'credit_card' && (!$this->cardLast4 || !$this->cardType)) {
            return false;
        }

        return true;
    }

    // Save payment
    public function save() {
        try {
            $payments = readJsonFile(PAYMENTS_FILE);
            if ($payments === false) {
                $payments = ['payments' => []];
            }

            if (!isset($payments['payments'])) {
                $payments['payments'] = [];
            }

            // Generate payment ID if not set
            if (!$this->paymentID) {
                $this->paymentID = generateUniqueId();
            }

            // Create payment record
            $paymentData = [
                'payment_id' => $this->paymentID,
                'order_id' => $this->orderID,
                'amount' => $this->amount,
                'payment_method' => $this->paymentMethod,
                'payment_date' => $this->paymentDate,
                'status' => $this->status,
                'transaction_id' => $this->transactionID,
                'card_last4' => $this->cardLast4,
                'card_type' => $this->cardType,
                'billing_address' => $this->billingAddress
            ];

            // Check if payment exists
            $found = false;
            foreach ($payments['payments'] as $key => $payment) {
                if ($payment['payment_id'] === $this->paymentID) {
                    $payments['payments'][$key] = $paymentData;
                    $found = true;
                    break;
                }
            }

            // Add new payment if not found
            if (!$found) {
                $payments['payments'][] = $paymentData;
            }

            return writeJsonFile(PAYMENTS_FILE, $payments);
        } catch (Exception $e) {
            error_log("Error saving payment: " . $e->getMessage());
            return false;
        }
    }

    // Get payment by ID
    public static function getByID($paymentID) {
        try {
            $payments = readJsonFile(PAYMENTS_FILE);
            if (!isset($payments['payments'])) {
                return null;
            }

            foreach ($payments['payments'] as $paymentData) {
                if ($paymentData['payment_id'] === $paymentID) {
                    $payment = new Payment();
                    $payment->setPaymentID($paymentData['payment_id']);
                    $payment->setOrderID($paymentData['order_id']);
                    $payment->setAmount($paymentData['amount']);
                    $payment->setPaymentMethod($paymentData['payment_method']);
                    $payment->setPaymentDate($paymentData['payment_date']);
                    $payment->setStatus($paymentData['status']);
                    $payment->setTransactionID($paymentData['transaction_id']);
                    $payment->setCardLast4($paymentData['card_last4']);
                    $payment->setCardType($paymentData['card_type']);
                    $payment->setBillingAddress($paymentData['billing_address']);
                    return $payment;
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting payment by ID: " . $e->getMessage());
            return null;
        }
    }

    // Get payment by order ID
    public static function getByOrderID($orderID) {
        try {
            $payments = readJsonFile(PAYMENTS_FILE);
            if (!isset($payments['payments'])) {
                return null;
            }

            foreach ($payments['payments'] as $paymentData) {
                if ($paymentData['order_id'] === $orderID) {
                    $payment = new Payment();
                    $payment->setPaymentID($paymentData['payment_id']);
                    $payment->setOrderID($paymentData['order_id']);
                    $payment->setAmount($paymentData['amount']);
                    $payment->setPaymentMethod($paymentData['payment_method']);
                    $payment->setPaymentDate($paymentData['payment_date']);
                    $payment->setStatus($paymentData['status']);
                    $payment->setTransactionID($paymentData['transaction_id']);
                    $payment->setCardLast4($paymentData['card_last4']);
                    $payment->setCardType($paymentData['card_type']);
                    $payment->setBillingAddress($paymentData['billing_address']);
                    return $payment;
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting payment by order ID: " . $e->getMessage());
            return null;
        }
    }
}
