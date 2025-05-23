<?php
class Orders {
    private $orderID;
    private $customerID;
    private $items;
    private $subtotal;
    private $shippingCost;
    private $total;
    private $orderDate;
    private $status;
    private $paymentStatus;
    private $paymentMethod;
    private $shippingAddress;
    private $trackingNumber;
    private $estimatedDelivery;
    private $notes;

    public function __construct($orderData = null) {
        if ($orderData) {
            $this->orderID = $orderData['orderID'] ?? null;
            $this->customerID = $orderData['customerID'] ?? null;
            $this->items = $orderData['items'] ?? [];
            $this->subtotal = $orderData['subtotal'] ?? 0;
            $this->shippingCost = $orderData['shippingCost'] ?? 0;
            $this->total = $orderData['total'] ?? 0;
            $this->orderDate = $orderData['orderDate'] ?? date('Y-m-d H:i:s');
            $this->status = $orderData['status'] ?? 'pending';
            $this->paymentStatus = $orderData['paymentStatus'] ?? 'pending';
            $this->paymentMethod = $orderData['paymentMethod'] ?? '';
            $this->shippingAddress = $orderData['shippingAddress'] ?? '';
            $this->trackingNumber = $orderData['trackingNumber'] ?? '';
            $this->estimatedDelivery = $orderData['estimatedDelivery'] ?? '';
            $this->notes = $orderData['notes'] ?? '';
        } else {
            $this->orderID = 'ORD' . time();
            $this->orderDate = date('Y-m-d H:i:s');
            $this->status = 'pending';
            $this->paymentStatus = 'pending';
        }
    }

    public function updateStatus($status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (in_array($status, $validStatuses)) {
            $this->status = $status;
            return $this->save();
        }
        return false;
    }

    public function updatePaymentStatus($status) {
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
        if (in_array($status, $validStatuses)) {
            $this->paymentStatus = $status;
            return $this->save();
        }
        return false;
    }

    public function cancelOrder() {
        if ($this->status === 'pending' || $this->status === 'processing') {
            $this->status = 'cancelled';
            $this->paymentStatus = 'refunded';
            return $this->save();
        }
        return false;
    }

    private function DisplayOrderConfirmation() {
        $orderDetails = [
            'orderID' => $this->orderID,
            'orderDate' => $this->orderDate,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'shippingCost' => $this->shippingCost,
            'total' => $this->total,
            'shippingAddress' => $this->shippingAddress,
            'paymentMethod' => $this->paymentMethod,
            'estimatedDelivery' => $this->estimatedDelivery
        ];
        return $orderDetails;
    }

    public function addItem($productID, $quantity, $price) {
        // Get product details
        $product = Product::getById($productID);
        if (!$product) {
            return false;
        }

        $this->items[] = [
            'productID' => $productID,
            'productName' => $product->getProductName(),
            'quantity' => $quantity,
            'price' => $price,
            'itemTotal' => $quantity * $price,
            'imageUrl' => $product->getImageUrl()
        ];
        
        $this->calculateTotals();
        return true;
    }

    private function calculateTotals() {
        $this->subtotal = 0;
        foreach ($this->items as $item) {
            $this->subtotal += $item['itemTotal'];
        }
        $this->total = $this->subtotal + $this->shippingCost;
    }

    public function save() {
        $orders = readJsonFile(ORDERS_FILE);
        if (!isset($orders['orders'])) {
            $orders['orders'] = [];
        }
        
        $orders['orders'][$this->orderID] = $this->toArray();
        return writeJsonFile(ORDERS_FILE, $orders);
    }

    public function toArray() {
        return [
            'orderID' => $this->orderID,
            'customerID' => $this->customerID,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'shippingCost' => $this->shippingCost,
            'total' => $this->total,
            'orderDate' => $this->orderDate,
            'status' => $this->status,
            'paymentStatus' => $this->paymentStatus,
            'paymentMethod' => $this->paymentMethod,
            'shippingAddress' => $this->shippingAddress,
            'trackingNumber' => $this->trackingNumber,
            'estimatedDelivery' => $this->estimatedDelivery,
            'notes' => $this->notes
        ];
    }

    // Getters and setters
    public function getOrderID() {
        return $this->orderID;
    }

    public function getCustomerID() {
        return $this->customerID;
    }

    public function setCustomerID($customerID) {
        $this->customerID = $customerID;
    }

    public function getItems() {
        return $this->items;
    }

    public function getSubtotal() {
        return $this->subtotal;
    }

    public function getShippingCost() {
        return $this->shippingCost;
    }

    public function setShippingCost($shippingCost) {
        $this->shippingCost = $shippingCost;
        $this->calculateTotals();
    }

    public function getTotal() {
        return $this->total;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getPaymentStatus() {
        return $this->paymentStatus;
    }

    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    public function getShippingAddress() {
        return $this->shippingAddress;
    }

    public function setShippingAddress($shippingAddress) {
        $this->shippingAddress = $shippingAddress;
    }

    public function getTrackingNumber() {
        return $this->trackingNumber;
    }

    public function setTrackingNumber($trackingNumber) {
        $this->trackingNumber = $trackingNumber;
    }

    public function getEstimatedDelivery() {
        return $this->estimatedDelivery;
    }

    public function setEstimatedDelivery($estimatedDelivery) {
        $this->estimatedDelivery = $estimatedDelivery;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    // Get order by ID
    public static function getByID($orderID) {
        try {
            $orders = readJsonFile(ORDERS_FILE);
            if ($orders === false || !isset($orders['orders'])) {
                return null;
            }
            
            // Search through all orders
            foreach ($orders['orders'] as $order) {
                if (isset($order['orderID']) && $order['orderID'] === $orderID) {
                    return new Orders($order);
                }
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting order by ID: " . $e->getMessage());
            return null;
        }
    }

    // Get orders by customer ID
    public static function getByCustomerID($customerID) {
        try {
            $orders = readJsonFile(ORDERS_FILE);
            if (!isset($orders['orders'])) {
                return [];
            }

            $customerOrders = [];
            foreach ($orders['orders'] as $orderData) {
                if (isset($orderData['customerID']) && $orderData['customerID'] === $customerID) {
                    $customerOrders[] = new Orders($orderData);
                }
            }
            return $customerOrders;
        } catch (Exception $e) {
            error_log("Error getting orders by customer ID: " . $e->getMessage());
            return [];
        }
    }
}
?>