<?php
define('ORDER_DETAILS_FILE', 'data/order_details.json');

class OrderDetails {
    private $orderDetailID;
    private $orderID;
    private $productID;
    private $quantity;
    private $unitPrice;
    private $subtotal;
    
    public function __construct($orderDetailData = null) {
        if ($orderDetailData) {
            $this->orderDetailID = $orderDetailData['orderDetailID'] ?? null;
            $this->orderID = $orderDetailData['orderID'] ?? null;
            $this->productID = $orderDetailData['productID'] ?? null;
            $this->quantity = $orderDetailData['quantity'] ?? 0;
            $this->unitPrice = $orderDetailData['unitPrice'] ?? 0;
            $this->subtotal = $orderDetailData['subtotal'] ?? 0;
        } else {
            $this->orderDetailID = 'OD' . time();
        }
    }
    
    public function calculateSubtotal() {
        $this->subtotal = $this->quantity * $this->unitPrice;
        return $this->subtotal;
    }
    
    public function save() {
        $orderDetails = readJsonFile(ORDER_DETAILS_FILE);
        if (!isset($orderDetails['orderDetails'])) {
            $orderDetails['orderDetails'] = [];
        }
        
        $orderDetails['orderDetails'][$this->orderDetailID] = $this->toArray();
        return writeJsonFile(ORDER_DETAILS_FILE, $orderDetails);
    }
    
    
    
    public function toArray() {
        return [
            'orderDetailID' => $this->orderDetailID,
            'orderID' => $this->orderID,
            'productID' => $this->productID,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'subtotal' => $this->subtotal
        ];
    }
    
    // Getters and setters
    public function getOrderDetailID() {
        return $this->orderDetailID;
    }
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function setOrderID($orderID) {
        $this->orderID = $orderID;
        return $this->save();
    }
    
    public function getProductID() {
        return $this->productID;
    }
    
    public function setProductID($productID) {
        $this->productID = $productID;
        return $this->save();
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
    
    public function setQuantity($quantity) {
        if ($quantity > 0) {
            $this->quantity = $quantity;
            $this->calculateSubtotal();
            return $this->save();
        }
        return false;
    }
    
    public function getUnitPrice() {
        return $this->unitPrice;
    }
    
    public function setUnitPrice($unitPrice) {
        if ($unitPrice >= 0) {
            $this->unitPrice = $unitPrice;
            $this->calculateSubtotal();
            return $this->save();
        }
        return false;
    }
    
    public function getSubtotal() {
        return $this->subtotal;
    }
    
  
    public static function getByOrderID($orderID) {
        try {
            $orderDetails = readJsonFile(ORDER_DETAILS_FILE);
            if (!isset($orderDetails['orderDetails'])) {
                return [];
            }

            $details = [];
            foreach ($orderDetails['orderDetails'] as $detailData) {
                if ($detailData['orderID'] === $orderID) {
                    $details[] = new OrderDetails($detailData);
                }
            }
            return $details;
        } catch (Exception $e) {
            error_log("Error getting order details by order ID: " . $e->getMessage());
            return [];
        }
    }
    

    public static function getByProductID($productID) {
        try {
            $orderDetails = readJsonFile(ORDER_DETAILS_FILE);
            if (!isset($orderDetails['orderDetails'])) {
                return [];
            }

            $details = [];
            foreach ($orderDetails['orderDetails'] as $detailData) {
                if ($detailData['productID'] === $productID) {
                    $details[] = new OrderDetails($detailData);
                }
            }
            return $details;
        } catch (Exception $e) {
            error_log("Error getting order details by product ID: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getAllOrderDetails() {
        try {
            $orderDetails = readJsonFile(ORDER_DETAILS_FILE);
            if (!isset($orderDetails['orderDetails'])) {
                return [];
            }

            $details = [];
            foreach ($orderDetails['orderDetails'] as $detailData) {
                $details[] = new OrderDetails($detailData);
            }
            return $details;
        } catch (Exception $e) {
            error_log("Error getting all order details: " . $e->getMessage());
            return [];
        }
    }
}
