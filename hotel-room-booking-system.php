<?php
class MinHeap {
    private array $heap = [];

    public function insert(array $reservation) {
        $this->heap[] = $reservation;
        $this->heapifyUp();
    }

    public function extractMin() {
        if (empty($this->heap)) return null;
        $min = $this->heap[0];
        $this->heap[0] = array_pop($this->heap);
        $this->heapifyDown();
        return $min;
    }

    public function peek() {
        return $this->heap[0] ?? null;
    }

    public function isEmpty() {
        return empty($this->heap);
    }

    private function heapifyUp() {
        $index = count($this->heap) - 1;
        while ($index > 0) {
            $parentIndex = (int)(($index - 1) / 2);
            if ($this->heap[$index]['priority'] >= $this->heap[$parentIndex]['priority']) break;
            $this->swap($index, $parentIndex);
            $index = $parentIndex;
        }
    }

    private function heapifyDown() {
        $index = 0;
        while (2 * $index + 1 < count($this->heap)) {
            $smallest = 2 * $index + 1;
            if ($smallest + 1 < count($this->heap) && $this->heap[$smallest + 1]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest++;
            }
            if ($this->heap[$index]['priority'] <= $this->heap[$smallest]['priority']) break;
            $this->swap($index, $smallest);
            $index = $smallest;
        }
    }

    private function swap($i, $j) {
        [$this->heap[$i], $this->heap[$j]] = [$this->heap[$j], $this->heap[$i]];
    }

    public function getHeap() {
        return $this->heap;
    }
}

function saveData($heap) {
    file_put_contents("reservations.json", json_encode($heap->getHeap(), JSON_PRETTY_PRINT));
}

function loadData($heap) {
    if (file_exists("reservations.json")) {
        $data = json_decode(file_get_contents("reservations.json"), true);
        if ($data) {
            foreach ($data as $reservation) {
                $heap->insert($reservation);
            }
        }
    }
}

function menu() {
    $heap = new MinHeap();
    loadData($heap);

    while (true) {
        echo "\nHotel Booking System (Priority Queue)";
        echo "\n1. Book a Room";
        echo "\n2. View Reservations";
        echo "\n3. Check-In Guest";
        echo "\n4. Cancel Reservation";
        echo "\n5. Exit";
        echo "\nChoose an option: ";
        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case '1':
                echo "Enter Customer Name: ";
                $name = trim(fgets(STDIN));
                echo "Enter Urgency Level (1 = VIP, 2 = Business, 3 = Regular): ";
                $priority = (int) trim(fgets(STDIN));

                $reservation = [
                    'name' => $name,
                    'priority' => $priority,
                    'timestamp' => time()
                ];
                $heap->insert($reservation);
                saveData($heap);
                echo "Reservation added successfully!\n";
                break;
            case '2':
                $reservations = $heap->getHeap();
                if (empty($reservations)) {
                    echo "No reservations available.\n";
                } else {
                    echo "Upcoming Reservations (Priority Order):\n";
                    foreach ($reservations as $r) {
                        echo "{$r['name']} (Priority: {$r['priority']})\n";
                    }
                }
                break;
            case '3':
                $reservation = $heap->extractMin();
                if ($reservation) {
                    echo "Checked in: {$reservation['name']} (Priority: {$reservation['priority']})\n";
                    saveData($heap);
                } else {
                    echo "No reservations available.\n";
                }
                break;
            case '4':
                echo "Enter Customer Name to Cancel: ";
                $name = trim(fgets(STDIN));
                $reservations = $heap->getHeap();
                $newHeap = new MinHeap();
                $found = false;

                foreach ($reservations as $r) {
                    if ($r['name'] !== $name) {
                        $newHeap->insert($r);
                    } else {
                        $found = true;
                    }
                }

                if ($found) {
                    saveData($newHeap);
                    echo "Reservation for $name cancelled successfully!\n";
                } else {
                    echo "No reservation found for $name.\n";
                }

                $heap = $newHeap;
                break;
            case '5':
                saveData($heap);
                echo "Exiting... Thank you!\n";
                exit;
            default:
                echo "Invalid choice! Please enter a valid option.\n";
        }
    }
}
menu();
?>