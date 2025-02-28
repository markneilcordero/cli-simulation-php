<?php
class ATM {
    private float $balance;
    private array $transactionStack = [];

    public function __construct(float $initialBalance = 1000) {
        $this->balance = $initialBalance;
    }

    public function deposit(float $amount) {
        if ($amount <= 0) {
            echo "❌ Invalid deposit amount.\n";
            return;
        }

        $this->balance += $amount;
        $this->pushTransaction('deposit', $amount);
        echo "✅ Deposited: ₱$amount | New Balance: ₱{$this->balance}\n";
    }

    public function withdraw(float $amount) {
        if ($amount <= 0 || $amount > $this->balance) {
            echo "❌ Invalid withdrawal amount or insufficient balance.\n";
            return;
        }

        $this->balance -= $amount;
        $this->pushTransaction('withdraw', $amount);
        echo "✅ Withdrawn: ₱$amount | New Balance: ₱{$this->balance}\n";
    }

    public function transfer(float $amount) {
        if ($amount <= 0 || $amount > $this->balance) {
            echo "❌ Invalid transfer amount or insufficient balance.\n";
            return;
        }

        $this->balance -= $amount;
        $this->pushTransaction('transfer', $amount);
        echo "✅ Transferred: ₱$amount | New Balance: ₱{$this->balance}\n";
    }

    private function pushTransaction(string $type, float $amount) {
        $transaction = [
            'type' => $type,
            'amount' => $amount,
            'balance' => $this->balance
        ];
        array_push($this->transactionStack, $transaction);
    }

    public function undoTransaction() {
        if (empty($this->transactionStack)) {
            echo "⚠️ No transactions to undo.\n";
            return;
        }

        $lastTransaction = array_pop($this->transactionStack);
        $this->reverseTransaction($lastTransaction);
    }

    private function reverseTransaction(array $transaction) {
        switch ($transaction['type']) {
            case 'deposit':
                $this->balance -= $transaction['amount'];
                echo "🔄 Undo Deposit: ₱{$transaction['amount']} | New Balance: ₱{$this->balance}\n";
                break;
            case 'withdraw':
                $this->balance += $transaction['amount'];
                echo "🔄 Undo Withdrawal: ₱{$transaction['amount']} | New Balance: ₱{$this->balance}\n";
                break;
            case 'transfer':
                $this->balance += $transaction['amount'];
                echo "🔄 Undo Transfer: ₱{$transaction['amount']} | New Balance: ₱{$this->balance}\n";
                break;
        }
    }

    public function showTransactionHistory() {
        if (empty($this->transactionStack)) {
            echo "📜 No transaction yet.\n";
            return;
        }

        echo "📜 Transaction History (Last 5 Transactions):\n";
        $history = array_slice($this->transactionStack, -5);
        foreach (array_reverse($history) as $transaction) {
            echo "- {$transaction['type']} ₱{$transaction['amount']} (Balance: ₱{$transaction['balance']})\n";
        }
    }

    public function getBalance() {
        echo "💰 Current Balance: ₱{$this->balance}\n";
    }
}

function atmMenu() {
    $atm = new ATM(5000);

    while (true) {
        echo "\n=== 🏦 ATM Simulator ===\n";
        echo "[1] Deposit\n";
        echo "[2] Withdraw\n";
        echo "[3] Transfer\n";
        echo "[4] View Transaction History\n";
        echo "[5] Undo Last Transaction\n";
        echo "[6] Check Balance\n";
        echo "[7] Exit\n";
        echo "Choose an option: ";
        
        $choice = trim(fgets(STDIN));
        
        switch ($choice) {
            case '1':
                echo "Enter deposit amount: ";
                $amount = floatval(trim(fgets(STDIN)));
                $atm->deposit($amount);
                break;
            case '2':
                echo "Enter withdrawal amount: ";
                $amount = floatval(trim(fgets(STDIN)));
                $atm->withdraw($amount);
                break;
            case '3':
                echo "Enter transfer amount: ";
                $amount = floatval(trim(fgets(STDIN)));
                $atm->transfer($amount);
                break;
            case '4':
                $atm->showTransactionHistory();
                break;
            case '5':
                $atm->undoTransaction();
                break;
            case '6':
                $atm->getBalance();
                break;
            case '7':
                echo "👋 Thank you for using the ATM!\n";
                exit;
            default:
                echo "❌ Invalid option. Try again.\n";
        }
    }
}

atmMenu();
?>