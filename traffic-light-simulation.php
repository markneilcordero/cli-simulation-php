<?php
define('CONFIG_FILE', 'traffic_light_config.json');

class TrafficLightFSM {
    private $states = ['RED', 'GREEN', 'YELLOW'];
    private $currentState;
    private $config;

    public function __construct() {
        $this->loadConfig();
        $this->currentState = 'RED';
    }

    public function loadConfig() {
        if (file_exists(CONFIG_FILE)) {
            $this->config = json_decode(file_get_contents(CONFIG_FILE), true);
        } else {
            $this->config = ['RED' => 5, 'GREEN' => 7, 'YELLOW' => 3];
            $this->saveConfig();
        }
    }

    public function saveConfig() {
        file_put_contents(CONFIG_FILE, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    public function setDuration($state, $duration) {
        if (isset($this->config[$state])) {
            $this->config[$state] = $duration;
            $this->saveConfig();
            echo "Duration for $state updated to $duration seconds.\n";
        } else {
            echo "Invalid state: $state\n";
        }
    }

    public function getDuration($state) {
        return $this->config[$state] ?? 0;
    }

    public function runSimulation() {
        echo "Traffic Light Simulation Started. Press Ctrl+C to stop.\n";

        while (true) {
            echo "\nCurrent Light: {$this->currentState}\n";
            echo str_repeat("=", 20) . "\n";

            sleep($this->getDuration($this->currentState));

            $this->transitionState();
        }
    }

    private function transitionState() {
        switch ($this->currentState) {
            case 'RED':
                $this->currentState = 'GREEN';
                break;
            case 'GREEN':
                $this->currentState = 'YELLOW';
                break;
            case 'YELLOW':
                $this->currentState = 'RED';
                break;
        }
    }
}

function showMenu() {
    echo "\nTraffic Light Simulation - CLI Menu\n";
    echo "1. Start Simulation\n";
    echo "2. Set Light Durations\n";
    echo "3. View Current Settings\n";
    echo "4. Exit\n";
    echo "Enter choice: ";
}

$trafficLight = new TrafficLightFSM();

while (true) {
    showMenu();
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            $trafficLight->runSimulation();
            break;
        case '2':
            echo "Enter Light (RED, GREEN, YELLOW): ";
            $state = strtoupper(trim(fgets(STDIN)));
            echo "Enter Duration (seconds): ";
            $duration = (int) trim(fgets(STDIN));
            $trafficLight->setDuration($state, $duration);
            break;
        case '3':
            echo "Current Durations:\n";
            foreach (['RED', 'GREEN', 'YELLOW'] as $state) {
                echo "$state: " . $trafficLight->getDuration($state) . " seconds\n";
            }
            break;
        case '4':
            echo "Exiting...\n";
            exit;
        default:
            echo "Invalid choice! Please try again.\n";
    }
}
?>