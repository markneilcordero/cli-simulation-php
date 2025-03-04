<?php

class AVLNode {
    public $price, $quantity, $orderId, $left, $right, $height;

    public function __construct($price, $quantity, $orderId) {
        $this->price = $price;
        $this->quantity = $quantity;
        $this->orderId = $orderId;
        $this->left = null;
        $this->right = null;
        $this->height = 1;
    }
}

class AVLTree {
    public $root = null;

    private function height($node) {
        return $node ? $node->height : 0;
    }

    private function balanceFactor($node) {
        return $node ? $this->height($node->left) - $this->height($node->right) : 0;
    }

    private function rotateRight($y) {
        $x = $y->left;
        $T2 = $x->right;
        $x->right = $y;
        $y->left = $T2;
        $y->height = max($this->height($y->left), $this->height($y->right)) + 1;
        $x->height = max($this->height($x->left), $this->height($x->right)) + 1;
        return $x;
    }

    private function rotateLeft($x) {
        $y = $x->right;
        $T2 = $y->left;
        $y->left = $x;
        $x->right = $T2;
        $x->height = max($this->height($x->left), $this->height($x->right)) + 1;
        $y->height = max($this->height($y->left), $this->height($y->right)) + 1;
        return $y;
    }

    private function balance($node) {
        $node->height = max($this->height($node->left), $this->height($node->right)) + 1;
        $balance = $this->balanceFactor($node);

        if ($balance > 1 && $this->balanceFactor($node->left) >= 0) {
            return $this->rotateRight($node);
        }
        if ($balance > 1 && $this->balanceFactor($node->left) < 0) {
            $node->left = $this->rotateLeft($node->left);
            return $this->rotateRight($node);
        }
        if ($balance < -1 && $this->balanceFactor($node->right) <= 0) {
            return $this->rotateLeft($node);
        }
        if ($balance < -1 && $this->balanceFactor($node->right) > 0) {
            $node->right = $this->rotateRight($node->right);
            return $this->rotateLeft($node);
        }
        return $node;
    }

    public function insert($node, $price, $quantity, $orderId) {
        if (!$node) return new AVLNode($price, $quantity, $orderId);
        if ($price < $node->price) $node->left = $this->insert($node->left, $price, $quantity, $orderId);
        else $node->right = $this->insert($node->right, $price, $quantity, $orderId);
        return $this->balance($node);
    }

    public function inorder($node) {
        $orders = [];
        if ($node) {
            $orders = array_merge($orders, $this->inorder($node->left));
            $orders[] = ["orderId" => $node->orderId, "price" => $node->price, "quantity" => $node->quantity];
            $orders = array_merge($orders, $this->inorder($node->right));
        }
        return $orders;
    }
}

class OrderBook {
    private $buyOrders;
    private $sellOrders;
    private $storageFile = "stock-orders.json";

    public function __construct() {
        $this->buyOrders = new AVLTree();
        $this->sellOrders = new AVLTree();
        $this->loadOrders();
    }

    public function placeBuyOrder($price, $quantity) {
        $orderId = uniqid();
        $this->buyOrders->root = $this->buyOrders->insert($this->buyOrders->root, $price, $quantity, $orderId);
        echo "Buy Order Placed: ID {$orderId}, Price {$price}, Quantity {$quantity}\n";
        $this->saveOrders();
    }

    public function placeSellOrder($price, $quantity) {
        $orderId = uniqid();
        $this->sellOrders->root = $this->sellOrders->insert($this->sellOrders->root, $price, $quantity, $orderId);
        echo "Sell Order Placed: ID {$orderId}, Price {$price}, Quantity {$quantity}\n";
        $this->saveOrders();
    }

    public function showOrders() {
        echo "\n===== ORDER BOOK =====\n";
        echo "Buy Orders:\n";
        $buyOrders = $this->buyOrders->inorder($this->buyOrders->root);
        foreach ($buyOrders as $order) {
            echo "ID: {$order['orderId']}, Price: {$order['price']}, Quantity: {$order['quantity']}\n";
        }
        echo "Sell Orders:\n";
        $sellOrders = $this->sellOrders->inorder($this->sellOrders->root);
        foreach ($sellOrders as $order) {
            echo "ID: {$order['orderId']}, Price: {$order['price']}, Quantity: {$order['quantity']}\n";
        }
    }

    private function saveOrders() {
        $data = [
            "buyOrders" => $this->buyOrders->inorder($this->buyOrders->root),
            "sellOrders" => $this->sellOrders->inorder($this->sellOrders->root),
        ];
        file_put_contents($this->storageFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function loadOrders() {
        if (file_exists($this->storageFile)) {
            $data = json_decode(file_get_contents($this->storageFile), true);
            if ($data) {
                foreach ($data["buyOrders"] as $order) {
                    $this->buyOrders->root = $this->buyOrders->insert($this->buyOrders->root, $order["price"], $order["quantity"], $order["orderId"]);
                }
                foreach ($data["sellOrders"] as $order) {
                    $this->sellOrders->root = $this->sellOrders->insert($this->sellOrders->root, $order["price"], $order["quantity"], $order["orderId"]);
                }
            }
        }
    }
}

// ==================== CLI MENU ====================
$orderBook = new OrderBook();

while (true) {
    echo "\n====================================\n";
    echo "  Stock Market Order Book Simulator\n";
    echo "====================================\n";
    echo "1. Place Buy Order\n";
    echo "2. Place Sell Order\n";
    echo "3. View Order Book\n";
    echo "4. Exit\n";
    echo "Enter your choice: ";
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            echo "Enter Buy Order Price: ";
            $price = (float) trim(fgets(STDIN));
            echo "Enter Buy Order Quantity: ";
            $quantity = (int) trim(fgets(STDIN));
            $orderBook->placeBuyOrder($price, $quantity);
            break;

        case '2':
            echo "Enter Sell Order Price: ";
            $price = (float) trim(fgets(STDIN));
            echo "Enter Sell Order Quantity: ";
            $quantity = (int) trim(fgets(STDIN));
            $orderBook->placeSellOrder($price, $quantity);
            break;

        case '3':
            $orderBook->showOrders();
            break;

        case '4':
            echo "Exiting Stock Market Order Book Simulator. Goodbye!\n";
            exit();

        default:
            echo "Invalid choice. Please select a valid option.\n";
    }
}
?>
