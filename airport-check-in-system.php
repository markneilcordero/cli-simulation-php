<?php
class PriorityQueue
{
    private $heap;
    private $filename;

    public function __construct($filename = "passengers.json")
    {
        $this->heap = [];
        $this->filename = $filename;
        $this->loadData();
    }

    public function enqueue($passenger)
    {
        $this->heap[] = $passenger;
        $this->heapifyUp(count($this->heap) - 1);
        $this->saveData();
    }

    public function dequeue()
    {
        if (empty($this->heap)) {
            return null;
        }

        $top = $this->heap[0];
        $last = array_pop($this->heap);
        if (!empty($this->heap)) {
            $this->heap[0] = $last;
            $this->heapifyDown(0);
        }
        $this->saveData();
        return $top;
    }

    public function displayQueue()
    {
        if (empty($this->heap)) {
            echo "No passengers in the queue.\n";
            return;
        }
        echo "Current Check-in Queue (sorted by priority):\n";
        foreach ($this->heap as $p) {
            echo "[Priority: {$p['priority']}] {$p['name']} - {$p['ticket_class']}\n";
        }
    }

    private function heapifyUp($index)
    {
        while ($index > 0) {
            $parent = floor(($index - 1) / 2);
            if ($this->heap[$index]['priority'] < $this->heap[$parent]['priority']) {
                $this->swap($index, $parent);
                $index = $parent;
            } else {
                break;
            }
        }
    }

    private function heapifyDown($index)
    {
        $size = count($this->heap);
        while (true) {
            $left = 2 * $index + 1;
            $right = 2 * $index + 2;
            $smallest = $index;

            if ($left < $size && $this->heap[$left]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $left;
            }
            if ($right < $size && $this->heap[$right]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $right;
            }
            if ($smallest !== $index) {
                $this->swap($index, $smallest);
                $index = $smallest;
            } else {
                break;
            }
        }
    }

    private function swap($i, $j)
    {
        $temp = $this->heap[$i];
        $this->heap[$i] = $this->heap[$j];
        $this->heap[$j] = $temp;
    }

    private function saveData()
    {
        file_put_contents($this->filename, json_encode($this->heap, JSON_PRETTY_PRINT));
    }

    private function loadData()
    {
        if (file_exists($this->filename)) {
            $data = json_decode(file_get_contents($this->filename), true);
            if ($data) {
                $this->heap = $data;
            }
        }
    }
}

function main()
{
    $queue = new PriorityQueue();

    while (true) {
        echo "\nAirport Check-in System\n";
        echo "1. Add Passenger\n";
        echo "2. Process Check-in (Next Passenger)\n";
        echo "3. View Check-in Queue\n";
        echo "4. Exit\n";
        echo "Choose an option: ";
        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case '1':
                echo "Enter Passenger Name: ";
                $name = trim(fgets(STDIN));

                echo "Enter Ticket Class (Economy/Business/First/Special Needs): ";
                $ticket_class = trim(fgets(STDIN));

                $priority = match (strtolower($ticket_class)) {
                    "special needs" => 1,
                    "first" => 2,
                    "business" => 3,
                    "economy" => 4,
                    default => 5,
                };

                $queue->enqueue(['name' => $name, 'ticket_class' => ucfirst($ticket_class), 'priority' => $priority]);
                echo "Passenger Added to Queue!\n";
                break;
            case '2':
                $processed = $queue->dequeue();
                if ($processed) {
                    echo "Processing Check-in: {$processed['name']} ({$processed['ticket_class']})\n";
                } else {
                    echo "No passengers to process.\n";
                }
                break;
            case '3':
                $queue->displayQueue();
                break;
            case '4':
                echo "Exiting system...\n";
                exit;
            default:
                echo "Invalid choice. Please try again.\n";
        }
    }
}

main();
?>