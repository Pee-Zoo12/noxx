<?php
define('INVENTORY_FILE', 'data/inventory.json');

class Inventory
{
    private $inventoryID;
    private $productID;
    private $quantity;
    private $reorderLevel;
    private $lastRestockDate;
    private $location;
    private $supplierID;
    private $purchasePrice;
    private $notes;

    public function __construct($inventoryData = null)
    {
        if ($inventoryData) {
            $this->inventoryID = $inventoryData['inventoryID'] ?? null;
            $this->productID = $inventoryData['productID'] ?? null;
            $this->quantity = $inventoryData['quantity'] ?? 0;
            $this->reorderLevel = $inventoryData['reorderLevel'] ?? 0;
            $this->lastRestockDate = $inventoryData['lastRestockDate'] ?? null;
            $this->location = $inventoryData['location'] ?? '';
            $this->supplierID = $inventoryData['supplierID'] ?? null;
            $this->purchasePrice = $inventoryData['purchasePrice'] ?? 0;
            $this->notes = $inventoryData['notes'] ?? '';
        } else {
            $this->inventoryID = 'INV' . time();
            $this->lastRestockDate = date('Y-m-d H:i:s');
        }
    }

    public function save()
    {
        $inventory = readJsonFile(INVENTORY_FILE);
        if (!isset($inventory['inventory'])) {
            $inventory['inventory'] = [];
        }

        $inventory['inventory'][$this->inventoryID] = $this->toArray();
        return writeJsonFile(INVENTORY_FILE, $inventory);
    }

    public function delete()
    {
        try {
            $inventory = readJsonFile(INVENTORY_FILE);
            if (!isset($inventory['inventory'])) {
                return false;
            }

            foreach ($inventory['inventory'] as $key => $item) {
                if ($item['inventoryID'] == $this->inventoryID) {
                    unset($inventory['inventory'][$key]);
                    $inventory['inventory'] = array_values($inventory['inventory']);
                    return writeJsonFile(INVENTORY_FILE, $inventory);
                }
            }
            return false;

        } catch (Exception $e) {
            error_log("Error deleting inventory: " . $e->getMessage());
            return false;
        }
    }

    public function updateQuantity($quantity)
    {
        if ($quantity >= 0) {
            $this->quantity = $quantity;
            return $this->save();
        }
        return false;
    }

    public function addStock($quantity)
    {
        if ($quantity > 0) {
            $this->quantity += $quantity;
            $this->lastRestockDate = date('Y-m-d H:i:s');
            return $this->save();
        }
        return false;
    }

    public function removeStock($quantity)
    {
        if ($quantity > 0 && $this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            return $this->save();
        }
        return false;
    }

    public function checkReorderLevel()
    {
        return $this->quantity <= $this->reorderLevel;
    }

    public function toArray()
    {
        return [
            'inventoryID' => $this->inventoryID,
            'productID' => $this->productID,
            'quantity' => $this->quantity,
            'reorderLevel' => $this->reorderLevel,
            'lastRestockDate' => $this->lastRestockDate,
            'location' => $this->location,
            'supplierID' => $this->supplierID,
            'purchasePrice' => $this->purchasePrice,
            'notes' => $this->notes
        ];
    }

    // Getters and setters
    public function getInventoryID()
    {
        return $this->inventoryID;
    }

    public function setInventoryID($inventoryID)
    {
        $this->inventoryID = $inventoryID;
    }

    public function getProductID()
    {
        return $this->productID;
    }

    public function setProductID($productID)
    {
        $this->productID = $productID;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this->save();
    }

    public function getReorderLevel()
    {
        return $this->reorderLevel;
    }

    public function setReorderLevel($reorderLevel)
    {
        $this->reorderLevel = $reorderLevel;
        return $this->save();
    }

    public function getLastRestockDate()
    {
        return $this->lastRestockDate;
    }

    public function setLastRestockDate($lastRestockDate)
    {
        $this->lastRestockDate = $lastRestockDate;
        return $this->save();
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
        return $this->save();
    }

    public function getSupplierID()
    {
        return $this->supplierID;
    }

    public function setSupplierID($supplierID)
    {
        $this->supplierID = $supplierID;
        return $this->save();
    }

    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;
        return $this->save();
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this->save();
    }

    // Static methods
    public static function getByProductID($productID)
    {
        try {
            $inventory = readJsonFile(INVENTORY_FILE);
            if (!isset($inventory['inventory'])) {
                return null;
            }

            foreach ($inventory['inventory'] as $inventoryData) {
                if ($inventoryData['productID'] === $productID) {
                    return new Inventory($inventoryData);
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting inventory by product ID: " . $e->getMessage());
            return null;
        }
    }

    public static function getAllInventory()
    {
        try {
            $inventory = readJsonFile(INVENTORY_FILE);
            if (!isset($inventory['inventory'])) {
                return [];
            }

            $inventoryList = [];
            foreach ($inventory['inventory'] as $inventoryData) {
                $inventoryList[] = new Inventory($inventoryData);
            }
            return $inventoryList;
        } catch (Exception $e) {
            error_log("Error getting all inventory: " . $e->getMessage());
            return [];
        }
    }

    public static function getLowStockItems()
    {
        try {
            $inventory = readJsonFile(INVENTORY_FILE);
            if (!isset($inventory['inventory'])) {
                return [];
            }

            $lowStockItems = [];
            foreach ($inventory['inventory'] as $inventoryData) {
                if ($inventoryData['quantity'] <= $inventoryData['reorderLevel']) {
                    $lowStockItems[] = new Inventory($inventoryData);
                }
            }
            return $lowStockItems;
        } catch (Exception $e) {
            error_log("Error getting low stock items: " . $e->getMessage());
            return [];
        }
    }

    public static function getByLocation($location)
    {
        try {
            $inventory = readJsonFile(INVENTORY_FILE);
            if (!isset($inventory['inventory'])) {
                return [];
            }

            $items = [];
            foreach ($inventory['inventory'] as $itemData) {
                if ($itemData['location'] == $location) {
                    $item = new Inventory();
                    $item->setInventoryID($itemData['inventoryID']);
                    $item->setProductID($itemData['productID']);
                    $item->setQuantity($itemData['quantity']);
                    $item->setReorderLevel($itemData['reorderLevel']);
                    $item->setLastRestockDate($itemData['lastRestockDate']);
                    $item->setLocation($itemData['location']);
                    $item->setSupplierID($itemData['supplierID']);
                    $item->setPurchasePrice($itemData['purchasePrice']);
                    $item->setNotes($itemData['notes']);
                    $items[] = $item;
                }
            }
            return $items;

        } catch (Exception $e) {
            error_log("Error getting inventory by location: " . $e->getMessage());
            return [];
        }
    }
}
