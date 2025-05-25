<?php
// Remove this line since it's now in config.php
// define('PURCHASES_FILE', 'data/purchases.json');

class Purchases
{
    private int $purchaseID;
    private int $orderID;
    private int $productID;
    private string $productName;
    private int $quantity;
    private float $unitCost;
    private float $total;
    private int $customerID;
    private string $paymentMethod;
    private string $shippingAddress;
    private string $status;

    public static function viewPurchaseHistory(string|int $customerID): array
    {
        $purchases = readJsonFile(PURCHASES_FILE);
        $history = [];

        // Debug: Print the contents of purchases file
        error_log('Purchases data: ' . print_r($purchases, true));
        error_log('Customer ID being searched: ' . $customerID);

        if (empty($purchases)) {
            return [];
        }

        foreach ($purchases as $purchase) {
            // Compare as strings to handle both string and int IDs
            if ((string) $purchase['customerID'] === (string) $customerID) {
                $history[] = new self([
                    'purchaseID' => $purchase['purchaseID'] ?? '',
                    'orderID' => $purchase['orderID'] ?? '',
                    'productID' => $purchase['productID'] ?? '',
                    'productName' => $purchase['productName'] ?? '',
                    'quantity' => $purchase['quantity'] ?? 0,
                    'unitCost' => $purchase['unitCost'] ?? 0.00,
                    'total' => $purchase['total'] ?? 0.00,
                    'customerID' => $purchase['customerID'] ?? '',
                    'paymentMethod' => $purchase['paymentMethod'] ?? '',
                    'status' => $purchase['status'] ?? 'pending'
                ]);
            }
        }

        // Debug: Print the history array
        error_log('Purchase history: ' . print_r($history, true));

        return $history;
    }

    // Add constructor if not already present
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Add getters if not already present
    public function getOrderID(): int
    {
        return $this->orderID;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitCost(): float
    {
        return $this->unitCost;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function recordPurchase(): bool
    {
        $purchases = readJsonFile(PURCHASES_FILE);

        $purchaseData = [
            'purchaseID' => 'PCH' . time(),
            'orderID' => $this->orderID,
            'productID' => $this->productID,
            'productName' => $this->productName,
            'quantity' => $this->quantity,
            'unitCost' => $this->unitCost,
            'total' => $this->quantity * $this->unitCost,
            'customerID' => $this->customerID,
            'paymentMethod' => $this->paymentMethod,
            'shippingAddress' => $this->shippingAddress,
            'status' => 'completed',
            'purchaseDate' => date('Y-m-d H:i:s')
        ];

        $purchases[] = $purchaseData;

        // Debug purchase recording
        error_log('Recording purchase: ' . print_r($purchaseData, true));

        return writeJsonFile(PURCHASES_FILE, $purchases);
    }

    public static function createFromOrder(array $orderData): self
    {
        return new self([
            'orderID' => $orderData['orderID'],
            'productID' => $orderData['productID'],
            'productName' => $orderData['productName'],
            'quantity' => $orderData['quantity'],
            'unitCost' => $orderData['unitCost'],
            'customerID' => $orderData['customerID'],
            'paymentMethod' => $orderData['paymentMethod'],
            'shippingAddress' => $orderData['shippingAddress']
        ]);
    }
}
