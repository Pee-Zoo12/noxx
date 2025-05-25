<?php
class Product
{
    protected $productID;
    protected $productName;
    protected $serialNumber;
    protected $stockQuantity;
    protected $type;
    protected $unitCost;
    protected $description;
    protected $imageUrl;
    protected $category;
    protected $orderDetails;
    protected $purchases;
    protected $shoppingCart;
    protected $status;


    private function generateUniqueId()
    {
        return uniqid();
    }

    // Constructor
    public function __construct($productData = null)
    {
        if ($productData) {
            $this->productID = $productData['productID'] ?? $this->generateUniqueId();
            $this->productName = $productData['productName'] ?? '';
            $this->serialNumber = $productData['serialNumber'] ?? $this->generateSerialNumber();
            $this->stockQuantity = $productData['stockQuantity'] ?? 0;
            $this->type = $productData['type'] ?? '';
            $this->unitCost = $productData['unitCost'] ?? 0;
            $this->description = $productData['description'] ?? '';
            $this->imageUrl = $productData['imageUrl'] ?? '';
            $this->category = $productData['category'] ?? '';
            $this->orderDetails = $productData['orderDetails'] ?? [];
            $this->purchases = $productData['purchases'] ?? [];
            $this->shoppingCart = $productData['shoppingCart'] ?? [];
            $this->status = $productData['status'] ?? 'active';
        } else {
            $this->productID = $this->generateUniqueId();
            $this->serialNumber = $this->generateSerialNumber();
            $this->stockQuantity = 0;
            $this->unitCost = 0;
            $this->orderDetails = [];
            $this->purchases = [];
            $this->shoppingCart = [];
            $this->status = 'active';
        }
    }

    // Generate a unique serial number
    private function generateSerialNumber()
    {
        return strtoupper(substr(md5(time() . rand()), 0, 12));
    }

    // Save product data
    public function save()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                $products = ['products' => []];
            }

            if (!isset($products['products'])) {
                $products['products'] = [];
            }

            $found = false;

            // Check if product already exists
            foreach ($products['products'] as $key => $product) {
                if ($product['productID'] === $this->productID) {
                    $products['products'][$key] = $this->toArray();
                    $found = true;
                    break;
                }
            }

            // Add new product if not found
            if (!$found) {
                $products['products'][] = $this->toArray();
            }

            return writeJsonFile(PRODUCTS_FILE, $products);
        } catch (Exception $e) {
            error_log("Error saving product: " . $e->getMessage());
            return false;
        }
    }

    // Delete product
    public function delete()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return false;
            }

            foreach ($products as $key => $product) {
                if ($product['productID'] === $this->productID) {
                    unset($products[$key]);
                    break;
                }
            }

            $products = array_values($products); // Re-index array
            return writeJsonFile(PRODUCTS_FILE, $products);
        } catch (Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    // Sell product
    public function sell($quantity)
    {
        if ($this->stockQuantity < $quantity || $this->status !== 'active') {
            return false;
        }

        try {
            $this->stockQuantity -= $quantity;
            if ($this->stockQuantity <= 0) {
                $this->status = 'out_of_stock';
            }
            return $this->save();
        } catch (Exception $e) {
            error_log("Error selling product: " . $e->getMessage());
            return false;
        }
    }

    // Restock product
    public function restock($quantity)
    {
        if ($quantity <= 0) {
            return false;
        }

        try {
            $this->stockQuantity += $quantity;
            if ($this->status === 'out_of_stock') {
                $this->status = 'active';
            }
            return $this->save();
        } catch (Exception $e) {
            error_log("Error restocking product: " . $e->getMessage());
            return false;
        }
    }

    // Get product details
    public function getProductDetails()
    {
        return $this->toArray();
    }

    // Update stock quantity
    public function updateStock($quantity)
    {
        if ($quantity < 0) {
            return false;
        }

        try {
            $this->stockQuantity = $quantity;
            $this->status = $quantity > 0 ? 'active' : 'out_of_stock';
            return $this->save();
        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            return false;
        }
    }


    // Add to order details
    public function addToOrderDetails(OrderDetails $orderDetail)
    {
        $this->orderDetails[] = $orderDetail;
        return $this->updateProduct();
    }

    // Add to purchases
    public function addToPurchases(Purchases $purchase)
    {
        $this->purchases[] = $purchase;
        return $this->updateProduct();
    }

    // Add to shopping cart
    public function addToShoppingCart(ShoppingCart $cart)
    {
        $this->shoppingCart[] = $cart;
        return $this->updateProduct();
    }

    // Load product by ID
    public static function getById($productId)
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return null;
            }

            foreach ($products['products'] as $product) {
                if ($product['productID'] === $productId) {
                    return new Product($product);
                }
            }

            return null;
        } catch (Exception $e) {
            error_log("Error getting product by ID: " . $e->getMessage());
            return null;
        }
    }

    // Get all products
    public static function getAll()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $productObjects = [];
            foreach ($products as $product) {
                $productObjects[] = new Product($product);
            }

            return $productObjects;
        } catch (Exception $e) {
            error_log("Error getting all products: " . $e->getMessage());
            return [];
        }
    }

    // Get active products
    public static function getActive()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $productObjects = [];
            foreach ($products as $product) {
                if ($product['status'] === 'active') {
                    $productObjects[] = new Product($product);
                }
            }

            return $productObjects;
        } catch (Exception $e) {
            error_log("Error getting active products: " . $e->getMessage());
            return [];
        }
    }

    // Get products by category
    public static function getByCategory($category)
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false || !isset($products['products'])) {
                return [];
            }

            $results = [];
            foreach ($products['products'] as $product) {
                if (isset($product['category']) && $product['category'] === $category) {
                    $results[] = new Product($product);
                }
            }

            return $results;
        } catch (Exception $e) {
            error_log("Error getting products by category: " . $e->getMessage());
            return [];
        }
    }

    // Get products by subcategory
    public static function getBySubcategory($subcategory)
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $results = [];
            foreach ($products as $product) {
                if ($product['subcategory'] === $subcategory) {
                    $results[] = new Product($product);
                }
            }

            return $results;
        } catch (Exception $e) {
            error_log("Error getting products by subcategory: " . $e->getMessage());
            return [];
        }
    }

    // Get all categories
    public static function getAllCategories()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $categories = [];
            foreach ($products as $product) {
                if (!empty($product['category']) && !in_array($product['category'], $categories)) {
                    $categories[] = $product['category'];
                }
            }

            return $categories;
        } catch (Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    // Get all subcategories
    public static function getAllSubcategories()
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $subcategories = [];
            foreach ($products as $product) {
                if (!empty($product['subcategory']) && !in_array($product['subcategory'], $subcategories)) {
                    $subcategories[] = $product['subcategory'];
                }
            }

            return $subcategories;
        } catch (Exception $e) {
            error_log("Error getting subcategories: " . $e->getMessage());
            return [];
        }
    }

    // Search products
    public static function search($keyword)
    {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            if ($products === false) {
                return [];
            }

            $results = [];
            $keyword = strtolower($keyword);

            foreach ($products as $product) {
                if (
                    strpos(strtolower($product['productName']), $keyword) !== false ||
                    strpos(strtolower($product['description']), $keyword) !== false ||
                    strpos(strtolower($product['category']), $keyword) !== false
                ) {
                    $results[] = new Product($product);
                }
            }

            return $results;
        } catch (Exception $e) {
            error_log("Error searching products: " . $e->getMessage());
            return [];
        }
    }

    // Convert product object to array
    public function toArray()
    {
        return [
            'productID' => $this->productID,
            'productName' => $this->productName,
            'serialNumber' => $this->serialNumber,
            'stockQuantity' => $this->stockQuantity,
            'type' => $this->type,
            'unitCost' => $this->unitCost,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'category' => $this->category,
            'orderDetails' => $this->orderDetails ?? [],
            'purchases' => $this->purchases ?? [],
            'shoppingCart' => $this->shoppingCart ?? [],
            'status' => $this->status
        ];
    }

    // Getters and setters
    public function getProductID()
    {
        return $this->productID;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;
    }

    public function getStockQuantity()
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity($stockQuantity)
    {
        $this->stockQuantity = $stockQuantity;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getUnitCost()
    {
        return $this->unitCost;
    }

    public function setUnitCost($unitCost)
    {
        $this->unitCost = $unitCost;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getImageUrl()
    {
        if (empty($this->imageUrl)) {
            return 'assets/images/noxlogo.png';
        }

        // Clean up the path
        $cleanPath = str_replace('\\', '/', $this->imageUrl);
        $cleanPath = ltrim($cleanPath, '/');

        // If the path already includes the full directory structure, return it
        if (strpos($cleanPath, 'assets/images/') === 0) {
            return $cleanPath;
        }

        // If not, construct the path based on type
        if (!empty($this->type)) {
            $typePath = 'assets/images/' . strtolower($this->type) . '/' . basename($cleanPath);
            if (file_exists($typePath)) {
                return $typePath;
            }
        }

        // If the direct path exists, return it
        if (file_exists($cleanPath)) {
            return $cleanPath;
        }

        // Return default image if nothing else works
        return 'assets/images/noxlogo.png';
    }

    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this->save();
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    public function getPurchases()
    {
        return $this->purchases;
    }

    public function getShoppingCart()
    {
        return $this->shoppingCart;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isOutOfStock()
    {
        return $this->status === 'out_of_stock';
    }

    private function updateProduct()
    {
        $products = readJsonFile(PRODUCTS_FILE);

        if (isset($products['products'][$this->productID])) {
            $products['products'][$this->productID] = $this->toArray();
            return writeJsonFile(PRODUCTS_FILE, $products);
        }

        return false;
    }
}
?>