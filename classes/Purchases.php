<?php
define('PURCHASES_FILE', 'data/purchases.json');

class Purchases {
    protected $purchaseID;
    protected $orderID;
    protected $productID;
    protected $productName;
    protected $quantity;
    protected $unitCost;
    protected $total;
    protected $purchaseDate;
    protected $status;
    protected $customerID;
    protected $paymentMethod;
    protected $paymentStatus;
    protected $shippingAddress;
    protected $trackingNumber;
    
    public function __construct($purchaseData = null) {
        if ($purchaseData) {
            $this->purchaseID = $purchaseData['purchaseID'] ?? generateUniqueId();
            $this->orderID = $purchaseData['orderID'] ?? null;
            $this->productID = $purchaseData['productID'] ?? null;
            $this->productName = $purchaseData['productName'] ?? '';
            $this->quantity = $purchaseData['quantity'] ?? 0;
            $this->unitCost = $purchaseData['unitCost'] ?? 0;
            $this->total = $purchaseData['total'] ?? 0;
            $this->purchaseDate = $purchaseData['purchaseDate'] ?? date('Y-m-d H:i:s');
            $this->status = $purchaseData['status'] ?? 'pending';
            $this->customerID = $purchaseData['customerID'] ?? null;
            $this->paymentMethod = $purchaseData['paymentMethod'] ?? null;
            $this->paymentStatus = $purchaseData['paymentStatus'] ?? 'pending';
            $this->shippingAddress = $purchaseData['shippingAddress'] ?? null;
            $this->trackingNumber = $purchaseData['trackingNumber'] ?? null;
        } else {
            $this->purchaseID = generateUniqueId();
            $this->purchaseDate = date('Y-m-d H:i:s');
            $this->status = 'pending';
            $this->paymentStatus = 'pending';
        }
    }
    
    // Save purchase data
    public function save() {
        try {
            $purchases = readJsonFile(PURCHASES_FILE);
            if (!isset($purchases['purchases'])) {
                $purchases['purchases'] = [];
            }
            
            $purchaseData = $this->toArray();
            
            // Check if purchase already exists
            $found = false;
            foreach ($purchases['purchases'] as &$purchase) {
                if ($purchase['purchaseID'] === $this->purchaseID) {
                    $purchase = $purchaseData;
                    $found = true;
                    break;
                }
            }
            
            // Add new purchase if not found
            if (!$found) {
                $purchases['purchases'][] = $purchaseData;
            }
            
            return writeJsonFile(PURCHASES_FILE, $purchases);
        } catch (Exception $e) {
            error_log("Error saving purchase: " . $e->getMessage());
            return false;
        }
    }
    
    // Create purchase from order
    public static function createFromOrder($orderID, $productID, $quantity, $unitCost, $customerID, $paymentMethod, $shippingAddress) {
        try {
            // Get product details
            $product = Product::getById($productID);
            if (!$product) {
                return false;
            }
            
            $purchase = new Purchases([
                'orderID' => $orderID,
                'productID' => $productID,
                'productName' => $product->getProductName(),
                'quantity' => $quantity,
                'unitCost' => $unitCost,
                'total' => $quantity * $unitCost,
                'customerID' => $customerID,
                'paymentMethod' => $paymentMethod,
                'shippingAddress' => $shippingAddress
            ]);
            
            return $purchase->save() ? $purchase : false;
        } catch (Exception $e) {
            error_log("Error creating purchase from order: " . $e->getMessage());
            return false;
        }
    }
    
    // Update purchase status
    public function updateStatus($status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $this->status = $status;
        return $this->save();
    }
    
    // Update payment status
    public function updatePaymentStatus($status) {
        $validStatuses = ['pending', 'paid', 'failed'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $this->paymentStatus = $status;
        return $this->save();
    }
    
    // Add tracking number
    public function addTrackingNumber($trackingNumber) {
        $this->trackingNumber = $trackingNumber;
        return $this->save();
    }
    
    // Get purchase by ID
    public static function getById($purchaseID) {
        try {
            $purchases = readJsonFile(PURCHASES_FILE);
            if (!isset($purchases['purchases'])) {
                return null;
            }
            
            foreach ($purchases['purchases'] as $purchase) {
                if ($purchase['purchaseID'] === $purchaseID) {
                    return new Purchases($purchase);
                }
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting purchase by ID: " . $e->getMessage());
            return null;
        }
    }
    
    // Get purchases by customer ID
    public static function getByCustomerID($customerID) {
        try {
            $purchases = readJsonFile(PURCHASES_FILE);
            if (!isset($purchases['purchases'])) {
                return [];
            }
            
            $customerPurchases = [];
            foreach ($purchases['purchases'] as $purchase) {
                if ($purchase['customerID'] === $customerID) {
                    $customerPurchases[] = new Purchases($purchase);
                }
            }
            
            return $customerPurchases;
        } catch (Exception $e) {
            error_log("Error getting purchases by customer ID: " . $e->getMessage());
            return [];
        }
    }
    
    // Get purchases by order ID
    public static function getByOrderID($orderID) {
        try {
            $purchases = readJsonFile(PURCHASES_FILE);
            if (!isset($purchases['purchases'])) {
                return [];
            }
            
            $orderPurchases = [];
            foreach ($purchases['purchases'] as $purchase) {
                if ($purchase['orderID'] === $orderID) {
                    $orderPurchases[] = new Purchases($purchase);
                }
            }
            
            return $orderPurchases;
        } catch (Exception $e) {
            error_log("Error getting purchases by order ID: " . $e->getMessage());
            return [];
        }
    }
    
    // Convert purchase object to array
    public function toArray() {
        return [
            'purchaseID' => $this->purchaseID,
            'orderID' => $this->orderID,
            'productID' => $this->productID,
            'productName' => $this->productName,
            'quantity' => $this->quantity,
            'unitCost' => $this->unitCost,
            'total' => $this->total,
            'purchaseDate' => $this->purchaseDate,
            'status' => $this->status,
            'customerID' => $this->customerID,
            'paymentMethod' => $this->paymentMethod,
            'paymentStatus' => $this->paymentStatus,
            'shippingAddress' => $this->shippingAddress,
            'trackingNumber' => $this->trackingNumber
        ];
    }
    
    // Getters
    public function getPurchaseID() {
        return $this->purchaseID;
    }
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function getProductID() {
        return $this->productID;
    }
    
    public function getProductName() {
        return $this->productName;
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
    
    public function getUnitCost() {
        return $this->unitCost;
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function getPurchaseDate() {
        return $this->purchaseDate;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getCustomerID() {
        return $this->customerID;
    }
    
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }
    
    public function getPaymentStatus() {
        return $this->paymentStatus;
    }
    
    public function getShippingAddress() {
        return $this->shippingAddress;
    }
    
    public function getTrackingNumber() {
        return $this->trackingNumber;
    }
}
