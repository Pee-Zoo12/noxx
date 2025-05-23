<?php
class Customer extends User {
    private $customerID;
    protected $purchaseHistory;
    protected $orders = [];
    protected $purchases = [];
    
    public function __construct($userData = null) {
        parent::__construct($userData);
        
        if ($userData) {
            $this->customerID = $userData['customerID'] ?? $this->userID;
            $this->purchaseHistory = $userData['purchaseHistory'] ?? [];
            $this->loyaltyStatus = $userData['loyaltyStatus'] ?? 'regular';
            $this->orders = $userData['orders'] ?? [];
            $this->purchases = $userData['purchases'] ?? [];
        } else {
            $this->customerID = $this->userID;
            $this->purchaseHistory = [];
            $this->loyaltyStatus = 'regular';
            $this->orders = [];
            $this->purchases = [];
        }
        
        $this->role = 'customer';
    }
    
    public function register() {
        // Validate input data
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Check if email already exists
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }
        }
        
        
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
    
        $userData = $this->toArray();
        
        
        $users['customers'][] = $userData;
        writeJsonFile(USERS_FILE, $users);
        
        return true;
    }
    
    
    public function browse($category = null, $search = null) {
        $products = readJsonFile(PRODUCTS_FILE);
        $results = [];
        
        foreach ($products['products'] as $product) {
            $matchCategory = $category === null || $product['category'] === $category;
            $matchSearch = $search === null || stripos($product['productName'], $search) !== false;
            
            if ($matchCategory && $matchSearch) {
                $results[] = $product;
            }
        }
        
        return $results;
    }
    
    
    public function purchase($productID, $quantity) {
        // Check if product exists and has enough stock
        $products = readJsonFile(PRODUCTS_FILE);
        $productIndex = -1;
        
        foreach ($products['products'] as $index => $product) {
            if ($product['productID'] === $productID) {
                $productIndex = $index;
                break;
            }
        }
        
        if ($productIndex === -1 || $products['products'][$productIndex]['stockQuantity'] < $quantity) {
            return false;
        }
        
        // Update product stock
        $products['products'][$productIndex]['stockQuantity'] -= $quantity;
        writeJsonFile(PRODUCTS_FILE, $products);
        
        // Add to purchase history
        $purchase = [
            'purchaseID' => generateUniqueId(),
            'productID' => $productID,
            'productName' => $products['products'][$productIndex]['productName'],
            'quantity' => $quantity,
            'unitCost' => $products['products'][$productIndex]['unitCost'],
            'total' => $quantity * $products['products'][$productIndex]['unitCost'],
            'date' => date('Y-m-d H:i:s')
        ];
        
        // Update purchase history
        $this->purchaseHistory[] = $purchase;
        $this->updateProfile();
        
        return true;
    }
    
    // Verify product authenticity
    public function verifyProduct($serialNumber) {
        $products = readJsonFile(PRODUCTS_FILE);
        
        foreach ($products as $product) {
            if (isset($product['serialNumber']) && $product['serialNumber'] === $serialNumber) {
                return [
                    'verified' => true,
                    'product' => $product
                ];
            }
        }
        
        return [
            'verified' => false
        ];
    }
    
    // Add order
    public function addOrder(Orders $order) {
        $this->orders[] = $order->getOrderID();
        $this->updateProfile();
    }
    
    // Add purchase
    public function addPurchase(Purchases $purchase) {
        $this->purchases[] = $purchase->getPurchaseID();
        $this->updateProfile();
    }
    
    // Get customer orders
    public function getOrders() {
        $ordersData = readJsonFile(ORDERS_FILE);
        $customerOrders = [];
        
        foreach ($ordersData as $order) {
            if ($order['customer'] === $this->customerID) {
                // Get order details
                $orderDetails = OrderDetails::getByOrderID($order['orderID']);
                $items = [];
                
                foreach ($orderDetails as $detail) {
                    $items[] = [
                        'productID' => $detail->getProductID(),
                        'quantity' => $detail->getQuantity(),
                        'unitPrice' => $detail->getUnitPrice(),
                        'subtotal' => $detail->getSubtotal()
                    ];
                }
                
                $customerOrders[] = [
                    'orderID' => $order['orderID'],
                    'orderDate' => $order['orderDate'],
                    'status' => $order['status'],
                    'paymentStatus' => $order['paymentStatus'],
                    'paymentMethod' => $order['paymentMethod'],
                    'subtotal' => $order['subtotal'],
                    'shippingCost' => $order['shippingCost'],
                    'total' => $order['total'],
                    'items' => $items
                ];
            }
        }
        
        return $customerOrders;
    }
    
    // Convert customer object to array
    public function toArray() {
        $userData = parent::toArray();
        
        return array_merge($userData, [
            'customerID' => $this->customerID,
            'purchaseHistory' => $this->purchaseHistory,
            'loyaltyStatus' => $this->loyaltyStatus,
            'orders' => $this->orders,
            'purchases' => $this->purchases
        ]);
    }
    
    // Getters and setters
    public function getCustomerID() {
        return $this->customerID;
    }
    
    public function getPurchaseHistory() {
        return $this->purchaseHistory;
    }
    
    public function getLoyaltyStatus() {
        return $this->loyaltyStatus;
    }
    
    public function setLoyaltyStatus($loyaltyStatus) {
        $this->loyaltyStatus = $loyaltyStatus;
    }
}