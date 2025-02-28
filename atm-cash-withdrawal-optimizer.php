<?php
class ATM {
    private $dataFile = 'atm_data.json';
    private $users = [];
    private $atmCash = [];

    public function clearScreen() {
        for ($x = 0; $x < 100; $x++) {
            echo "\n";
        }
    }

    public function __construct() {
        $this->loadData();
    }

    private function loadData() {
        if (file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            $this->users = $data['users'] ?? [];
            $this->atmCash = [];
            
            if (empty($data['atmCash'])) {
                $this->atmCash = [1000 => 10, 500 => 10, 200 => 20, 100 => 50];
            } else {
                foreach ($data['atmCash'] as $key => $value) {
                    $this->atmCash[(int)$key] = $value;
                }
            }
        }
    }

    private function saveData() {
        file_put_contents($this->dataFile, json_encode(['users' => $this->users, 'atmCash' => $this->atmCash], JSON_PRETTY_PRINT));
    }

    public function createAccount($name, $balance) {
        $this->clearScreen();
        $userId = uniqid();
        $this->users[$userId] = ['name' => $name, 'balance' => $balance];
        $this->saveData();
        echo "Account created successfully! Your User ID: $userId\n";
    }

    public function deposit($userId, $amount) {
        $this->clearScreen();
        if (!isset($this->users[$userId])) {
            echo "User not found!\n";
            return;
        }
        $this->users[$userId]['balance'] += $amount;
        $this->saveData();
        echo "Deposit successful! New Balance: {$this->users[$userId]['balance']}\n";
    }

    public function checkBalance($userId) {
        $this->clearScreen();
        if (!isset($this->users[$userId])) {
            echo "User not found!\n";
            return;
        }
        echo "Your balance: {$this->users[$userId]['balance']}\n";
    }

    public function withdraw($userId, $amount) {
        $this->clearScreen();
        if (!isset($this->users[$userId])) {
            echo "User not found!\n";
            return;
        }
        if ($amount > $this->users[$userId]['balance']) {
            echo "Insufficient balance!\n";
            return;
        }
        if ($amount > $this->getTotalCashInATM()) {
            echo "ATM does not have enough cash!\n";
            return;
        }

        $result = $this->optimizeWithdrawal($amount);
        if ($result === false) {
            echo "Unable to dispense the requested amount with available denominations.\n";
            return;
        }

        $this->users[$userId]['balance'] -= $amount;
        foreach ($result as $denomination => $count) {
            $this->atmCash[$denomination] -= $count;
        }
        $this->saveData();

        echo "Withdrawal successful! You received:\n";
        foreach ($result as $denomination => $count) {
            echo "$denomination x $count\n";
        }
    }

    private function optimizeWithdrawal($amount) {
        $denominations = array_keys($this->atmCash);
        rsort($denominations);
        $queue = [[0, [], $amount]];

        while (!empty($queue)) {
            list($billCount, $chosenBills, $remainingAmount) = array_shift($queue);
            if ($remainingAmount == 0) return $chosenBills;

            foreach ($denominations as $denom) {
                if ($remainingAmount >= $denom && ($this->atmCash[$denom] - ($chosenBills[$denom] ?? 0)) > 0) {
                    $newBills = $chosenBills;
                    $newBills[$denom] = ($newBills[$denom] ?? 0) + 1;
                    array_push($queue, [$billCount + 1, $newBills, $remainingAmount - $denom]);
                }
            }
            usort($queue, fn($a, $b) => $a[0] - $b[0]);
        }

        return false;
    }

    private function getTotalCashInATM() {
        $total = 0;
        foreach ($this->atmCash as $denom => $count) {
            $total += $denom * $count;
        }
        return $total;
    }

    public function refillATM($denomination, $count) {
        $this->clearScreen();
        if (!isset($this->atmCash[$denomination])) {
            echo "Invalid denomination!\n";
            return;
        }
        $this->atmCash[$denomination] += $count;
        $this->saveData();
        echo "ATM refilled successfully!\n";
    }

    public function showATMCash() {
        $this->clearScreen();
        echo "ATM Cash Inventory:\n";
        foreach ($this->atmCash as $denomination => $count) {
            echo "$denomination x $count\n";
        }
    }
}

$atm = new ATM();

while (true) {
    echo "\n1. Create Account";
    echo "\n2. Deposit";
    echo "\n3. Check Balance";
    echo "\n4. Withdraw";
    echo "\n5. Refill ATM";
    echo "\n6. Show ATM Cash";
    echo "\n7. Exit";
    echo "\n Choose an option: ";
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            echo "Enter name: ";
            $name = trim(fgets(STDIN));
            echo "Enter initial deposit: ";
            $balance = (int)trim(fgets(STDIN));
            $atm->createAccount($name, $balance);
            break;
        case '2':
            echo "Enter User ID: ";
            $userId = trim(fgets(STDIN));
            echo "Enter deposit amount: ";
            $amount = (int)trim(fgets(STDIN));
            $atm->deposit($userId, $amount);
            break;
        case '3':
            echo "Enter User ID: ";
            $userId = trim(fgets(STDIN));
            $atm->checkBalance($userId);
            break;
        case '4':
            echo "Enter User ID: ";
            $userId = trim(fgets(STDIN));
            echo "Enter withdrawal amount: ";
            $amount = (int)trim(fgets(STDIN));
            $atm->withdraw($userId, $amount);
            break;
        case '5':
            echo "Enter denomination: ";
            $denomination = (int)trim(fgets(STDIN));
            echo "Enter count: ";
            $count = (int)trim(fgets(STDIN));
            $atm->refillATM($denomination, $count);
            break;
        case '6':
            $atm->showATMCash();
            break;
        case '7':
            exit ("Goodbye!\n");
    }
}
?>