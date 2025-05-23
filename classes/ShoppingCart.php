<?php
class ShoppingCart {
    private $cartID;
    private $userID;
    private $items = [];
    private $shippingID;
    private $shippingType;
    private $shippingCost;
    private $shippingAddress;
    private $paymentMethod;
    private $lastOrderID;
    
    private function generateUniqueId() {
        return uniqid();
    }
    
    public function __construct($userId = null) {
        $this->cartID = $this->generateUniqueId();
        $this->userID = $userId ?? (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
        
        if (!file_exists(CART_FILE)) {
            writeJsonFile(CART_FILE, ['shipping' => []]);
        }
        
        $this->loadCartItems();
    }
    
    // Load cart items for current user
    private function loadCartItems() {
        if (!$this->userID) {
            return;
        }
        
        $carts = readJsonFile(CART_FILE);
        if ($carts === false) {
            $carts = ['shipping' => []];
        }
        
        // Load user's cart items
        if (isset($carts[$this->userID])) {
            $this->items = $carts[$this->userID];
        } else {
            $this->items = [];
            $carts[$this->userID] = [];
            writeJsonFile(CART_FILE, $carts);
        }
        
        // Load shipping info if available
        if (isset($carts['shipping'][$this->userID])) {
            $shipping = $carts['shipping'][$this->userID];
            $this->shippingID = $shipping['shippingID'] ?? null;
            $this->shippingType = $shipping['shippingType'] ?? null;
            $this->shippingCost = $shipping['shippingCost'] ?? 0;
            $this->shippingAddress = $shipping['shippingAddress'] ?? null;
            $this->paymentMethod = $shipping['paymentMethod'] ?? null;
        }
    }
    
    // Save cart items
    private function saveCartItems() {
        if (!$this->userID) {
            return false;
        }
        
        $carts = readJsonFile(CART_FILE);
        if ($carts === false) {
            $carts = ['shipping' => []];
        }
        
        if (!isset($carts['shipping'])) {
            $carts['shipping'] = [];
        }
        
        $carts[$this->userID] = $this->items;
        
        $carts['shipping'][$this->userID] = [
            'shippingID' => $this->shippingID,
            'shippingType' => $this->shippingType,
            'shippingCost' => $this->shippingCost,
            'shippingAddress' => $this->shippingAddress,
            'paymentMethod' => $this->paymentMethod
        ];
        
        return writeJsonFile(CART_FILE, $carts);
    }
    
    // Add item to cart
    public function addCartItem($productId, $quantity = 1) {
        if (!$this->userID) return false;
        
        $products = readJsonFile(PRODUCTS_FILE)['products'];
        $product = null;
        
        foreach ($products as $p) {
            if ($p['productID'] === $productId) {
                $product = $p;
                break;
            }
        }
        
        if (!$product || $product['status'] !== 'active' || $product['stockQuantity'] < $quantity) {
            return false;
        }
        
        foreach ($this->items as &$item) {
            if ($item['productID'] === $productId) {
                $newQuantity = $item['quantity'] + $quantity;
                if ($product['stockQuantity'] >= $newQuantity) {
                    $item['quantity'] = $newQuantity;
                    return $this->saveCartItems();
                }
                return false;
            }
        }
        
        $newItem = [
            'cartItemID' => $this->generateUniqueId(),
            'productID' => $productId,
            'quantity' => $quantity,
            'dateAdded' => date('Y-m-d H:i:s')
        ];
        
        $this->items[] = $newItem;
        return $this->saveCartItems();
    }
    
    // Update quantity of cart item
    public function updateQuantity($cartItemId, $quantity) {
        foreach ($this->items as &$item) {
            if ($item['cartItemID'] === $cartItemId) {
                $product = Product::getById($item['productID']);
                if (!$product || !$product->isActive() || $product->getStockQuantity() < $quantity) {
                    return false;
                }
                
                $item['quantity'] = $quantity;
                return $this->saveCartItems();
            }
        }
        return false;
    }
    
    // Remove item from cart
    public function removeCartItem($cartItemId) {
        if (!$this->userID) {
            return false;
        }
        
        foreach ($this->items as $key => $item) {
            if ($item['cartItemID'] === $cartItemId) {
                unset($this->items[$key]);
                $this->items = array_values($this->items); // Re-index array
                return $this->saveCartItems();
            }
        }
        return false;
    }
    
    // Get cart details with optional item filtering
    public function viewCartDetails($items = null) {
        if (!$this->userID) {
            return [
                'items' => [],
                'subtotal' => 0,
                'shipping' => [
                    'type' => null,
                    'cost' => 0
                ],
                'total' => 0
            ];
        }
        
        $itemsToProcess = $items ?? $this->items;
        
        $cartDetails = [
            'items' => [],
            'subtotal' => 0,
            'shipping' => [
                'type' => $this->shippingType,
                'cost' => $this->shippingCost
            ],
            'total' => 0
        ];
        
        $products = readJsonFile(PRODUCTS_FILE)['products'] ?? [];
        if (empty($products)) {
            return $cartDetails;
        }
        
        foreach ($itemsToProcess as $item) {
            $productData = null;
            
            // Find product details
            foreach ($products as $product) {
                if ($product['productID'] === $item['productID']) {
                    $productData = $product;
                    break;
                }
            }
            
            if ($productData) {
                $itemTotal = $productData['unitCost'] * $item['quantity'];
                $cartDetails['subtotal'] += $itemTotal;
                
                // Create a temporary Product object to use its getImageUrl method
                $tempProduct = new Product();
                $tempProduct->setImageUrl($productData['imageUrl']);
                $tempProduct->setCategory($productData['category']);
                
                $cartDetails['items'][] = [
                    'cartItemID' => $item['cartItemID'],
                    'productID' => $item['productID'],
                    'productName' => $productData['productName'],
                    'unitCost' => $productData['unitCost'],
                    'quantity' => $item['quantity'],
                    'itemTotal' => $itemTotal,
                    'imageUrl' => $tempProduct->getImageUrl(),
                    'dateAdded' => $item['dateAdded']
                ];
            }
        }
        
        $cartDetails['total'] = $cartDetails['subtotal'] + $cartDetails['shipping']['cost'];
        
        return $cartDetails;
    }
    
    // Clear cart
    public function clearCart() {
        if (!$this->userID) {
            return false;
        }
        
        $this->items = [];
        return $this->saveCartItems();
    }
    
    // Update shipping info
    public function updateShippingInfo($shippingInfo) {
        if (!$this->userID) {
            return false;
        }
        
        $this->shippingID = $shippingInfo['shippingID'] ?? $this->generateUniqueId();
        $this->shippingType = $shippingInfo['shippingType'] ?? null;
        $this->shippingCost = $shippingInfo['shippingCost'] ?? 50.00;
        $this->shippingAddress = $shippingInfo['shippingAddress'] ?? null;
        $this->paymentMethod = $shippingInfo['paymentMethod'] ?? null;
        
        return $this->saveCartItems();
    }
    
    // Get selected items
    public function getSelectedItems($selectedItemIds) {
        if (!$this->userID || empty($selectedItemIds)) {
            return [];
        }
        
        $selectedItems = [];
        foreach ($this->items as $item) {
            if (in_array($item['cartItemID'], $selectedItemIds)) {
                $selectedItems[] = $item;
            }
        }
        
        return $selectedItems;
    }
    
    // Checkout process
    public function checkout($selectedItemIds = null) {
        if (!$this->userID) {
            return false;
        }
        
        // Get cart details
        $cartDetails = $this->viewCartDetails($selectedItemIds);
        if (empty($cartDetails['items'])) {
            return false;
        }
        
        // Create new order
        $order = new Orders();
        $order->setCustomerID($this->userID);
        $order->setShippingCost($this->shippingCost);
        $order->setPaymentMethod($this->paymentMethod);
        $order->setShippingAddress($this->shippingAddress);
        
        // Add items to order
        foreach ($cartDetails['items'] as $item) {
            $order->addItem($item['productID'], $item['quantity'], $item['unitCost']);
        }
        
        // Save order
        if ($order->save()) {
            // Update product stock
            foreach ($cartDetails['items'] as $item) {
                $product = Product::getById($item['productID']);
                if ($product) {
                    $product->sell($item['quantity']);
                }
            }
            
            // Clear cart
            $this->clearCart();
            
            // Store last order ID
            $this->lastOrderID = $order->getOrderID();
            
            return $this->lastOrderID;
        }
        
        return false;
    }
    
    // Get last order ID
    public function getLastOrderID() {
        return $this->lastOrderID;
    }
    
    // Getters
    public function getCartID() {
        return $this->cartID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function setUserID($userId) {
        $this->userID = $userId;
        $this->loadCartItems(); // Reload cart items for the new user
        return true;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    // Get item count
    public function getItemCount() {
        $count = 0;
        foreach ($this->items as $item) {
            $count += (int)$item['quantity'];
        }
        return $count;
    }
    
    public function getShippingType() {
        return $this->shippingType;
    }
    
    public function getShippingCost() {
        return $this->shippingCost;
    }
    
    public function getCartCount() {
        $count = 0;
        foreach ($this->items as $item) {
            $count += (int)$item['quantity'];
        }
        return $count;
    }
    
    public function getCartDetails() {
        $items = [];
        $subtotal = 0;

        foreach ($this->items as $productID => $quantity) {
            $product = Product::getById($productID);
            if (!$product) continue;
            
            $price = $product->getUnitCost();
            $itemTotal = $price * $quantity;
            $subtotal += $itemTotal;

            $items[] = [
                'productID' => $productID,
                'productName' => $product->getProductName(),
                'price' => $price,
                'quantity' => $quantity,
                'itemTotal' => $itemTotal,
                'imageUrl' => $product->getImageUrl()
            ];
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $this->shippingCost,
            'total' => $subtotal + $this->shippingCost
        ];
    }
}
?>