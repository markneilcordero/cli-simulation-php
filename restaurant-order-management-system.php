<?php
class MinHeap
{
    private $heap = [];

    public function insert($order)
    {
        $this->heap[] = $order;
        $this->heapifyUp();
    }

    public function extractMin()
    {
        if (empty($this->heap)) {
            return null;
        }

        $min = $this->heap[0];
        $last = array_pop($this->heap);
        
        if (!empty($this->heap)) {
            $this->heap[0] = $last;
            $this->heapifyDown();
        }

        return $min;
    }

    public function isEmpty()
    {
        return empty($this->heap);
    }

    public function getHeap()
    {
        return $this->heap;
    }

    private function heapifyUp()
    {
        $index = 0;
        $count = count($this->heap);

        while (2 * $index + 1 < $count) {
            $smallest = $index;
            $left = 2 * $index + 1;
            $right = 2 * $index + 2;

            if ($left < $count && $this->heap[$left]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $left;
            }

            if ($right < $count && $this->heap[$right]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $right;
            }

            if ($smallest === $index) {
                break;
            }

            $this->swap($index, $smallest);
            $index = $smallest;
        }
    }

    private function heapifyDown()
    {
        $index = 0;
        $count = count($this->heap);

        while (2 * $index + 1 < $count) {
            $smallest = $index;
            $left = 2 * $index + 1;
            $right = 2 * $index + 2;

            if ($left < $count && $this->heap[$left]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $left;
            }

            if ($right < $count && $this->heap[$right]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $right;
            }

            if ($smallest === $index) {
                break;
            }

            $this->swap($index, $smallest);
            $index = $smallest;
        }
    }

    private function swap($i, $j)
    {
        $temp = $this->heap[$i];
        $this->heap[$i] = $this->heap[$j];
        $this->heap[$j] = $temp;
    }
}

class RestaurantOrderSystem
{
    private $heap;
    private $ordersFile = "orders.json";
    private $completedOrders = [];

    public function __construct()
    {
        $this->heap = new MinHeap();
        $this->loadOrders();
    }

    public function addOrder($customer, $orderDetails, $priority)
    {
        $order = [
            'id' => uniqid(),
            'customer' => $customer,
            'details' => $orderDetails,
            'priority' => $priority
        ];
        $this->heap->insert($order);
        $this->saveOrders();
        echo "Order added successfully!\n";
    }

    public function processOrder()
    {
        $order = $this->heap->extractMin();
        if ($order) {
            echo "Processing Order: {$order['customer']} - {$order['details']} (Priority: {$order['priority']})\n";
            $this->completedOrders[] = $order;
            $this->saveOrders();
        } else {
            echo "No orders to process.\n";
        }
    }

    public function viewPendingOrders()
    {
        $orders = $this->heap->getHeap();
        if (empty($orders)) {
            echo "No pending orders.\n";
            return;
        }

        echo "Pending Orders:\n";
        foreach ($orders as $order) {
            echo "{$order['customer']} - {$order['details']} (Priority: {$order['priority']})\n";
        }
    }

    public function viewCompletedOrders()
    {
        if (empty($this->completedOrders)) {
            echo "No completed orders.\n";
            return;
        }

        echo "Completed Orders:\n";
        foreach ($this->completedOrders as $order) {
            echo "{$order['customer']} - {$order['details']} (Priority: {$order['priority']})\n";
        }
    }

    private function saveOrders()
    {
        file_put_contents($this->ordersFile, json_encode([
            'pending' => $this->heap->getHeap(),
            'completed' => $this->completedOrders
        ]));
    }

    private function loadOrders()
    {
        if (file_exists($this->ordersFile)) {
            $data = json_decode(file_get_contents($this->ordersFile), true);
            foreach ($data['pending'] ?? [] as $order) {
                $this->heap->insert($order);
            }
            $this->completedOrders = $data['completed'] ?? [];
        }
    }
}

function runCLI()
{
    $system = new RestaurantOrderSystem();

    while (true) {
        echo "\nRestaurant Order System\n";
        echo "1. Add Order\n";
        echo "2. Process Order\n";
        echo "3. View Pending Orders\n";
        echo "4. View Completed Orders\n";
        echo "5. Exit\n";
        $choice = readline("Choose an option: ");

        switch ($choice) {
            case "1":
                $customer = readline("Enter customer name: ");
                $details = readline("Enter order details: ");
                $priority = (int)readline("Enter priority (1=High, 2=Medium, 3=Low): ");
                $system->addOrder($customer, $details, $priority);
                break;
            case "2":
                $system->processOrder();
                break;
            case "3":
                $system->viewPendingOrders();
                break;
            case "4":
                $system->viewCompletedOrders();
                break;
            case "5":
                echo "Exiting...\n";
                exit;
            default:
                echo "Invalid option. Please try again.\n";
        }
    }
}

runCLI();
?>