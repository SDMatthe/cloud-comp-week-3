<?php
namespace App\Controllers;

class PaymentController {
    private $db;
    private $cache;

    public function __construct(PDO $db, \Redis $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Supported payment methods
    private $paymentMethods = ['credit_card', 'virtual_wallet', 'bank_transfer', 'gift_card'];

    // Process payment
    public function processPayment($orderId, $userId, $paymentMethod, $paymentDetails) {
        if (!in_array($paymentMethod, $this->paymentMethods)) {
            return ['success' => false, 'message' => 'Invalid payment method'];
        }

        try {
            $this->db->beginTransaction();

            // Create payment record
            $stmt = $this->db->prepare("
                INSERT INTO payments (order_id, user_id, method, amount, status, created_at)
                VALUES (?, ?, ?, ?, 'processing', NOW())
            ");

            // Get order total
            $orderStmt = $this->db->prepare("SELECT total_amount FROM orders WHERE id=?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

            $stmt->execute([$orderId, $userId, $paymentMethod, $order['total_amount']]);
            $paymentId = $this->db->lastInsertId();

            // Validate payment based on method
            $isValid = $this->validatePayment($paymentMethod, $paymentDetails);

            if (!$isValid) {
                throw new \Exception('Payment validation failed');
            }

            // Simulate payment processing (in real app, integrate with payment gateway)
            $transactionId = $this->generateTransactionId();

            // Update payment status
            $stmt = $this->db->prepare("
                UPDATE payments SET status='completed', transaction_id=? WHERE id=?
            ");
            $stmt->execute([$transactionId, $paymentId]);

            // Update order status
            $stmt = $this->db->prepare("UPDATE orders SET status='confirmed' WHERE id=?");
            $stmt->execute([$orderId]);

            $this->db->commit();

            // Cache successful payment
            $this->cache->setex("payment_{$paymentId}", 86400, json_encode([
                'transaction_id' => $transactionId,
                'status' => 'completed'
            ]));

            return ['success' => true, 'transaction_id' => $transactionId, 'payment_id' => $paymentId];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Add payment method
    public function addPaymentMethod($userId, $method, $details) {
        if (!in_array($method, $this->paymentMethods)) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        // Encrypt sensitive details
        $encryptedDetails = $this->encryptPaymentDetails($details);

        $stmt = $this->db->prepare("
            INSERT INTO user_payment_methods (user_id, method, details, is_default, created_at)
            VALUES (?, ?, ?, FALSE, NOW())
        ");
        
        $result = $stmt->execute([$userId, $method, $encryptedDetails]);

        return ['success' => $result, 'method_id' => $this->db->lastInsertId()];
    }

    // Get user's payment methods
    public function getPaymentMethods($userId) {
        $stmt = $this->db->prepare("
            SELECT id, method, is_default, created_at FROM user_payment_methods WHERE user_id=?
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Validate payment
    private function validatePayment($method, $details) {
        switch ($method) {
            case 'credit_card':
                return $this->validateCreditCard($details);
            case 'virtual_wallet':
                return $this->validateWallet($details);
            case 'bank_transfer':
                return $this->validateBankDetails($details);
            case 'gift_card':
                return $this->validateGiftCard($details);
            default:
                return false;
        }
    }

    private function validateCreditCard($details) {
        // Validate card number (Luhn algorithm)
        $cardNumber = preg_replace('/\D/', '', $details['card_number']);
        return strlen($cardNumber) === 16 && $this->luhnCheck($cardNumber);
    }

    private function validateWallet($details) {
        // Check wallet balance
        return isset($details['wallet_id']) && isset($details['amount']);
    }

    private function validateBankDetails($details) {
        return isset($details['account_number']) && isset($details['routing_number']);
    }

    private function validateGiftCard($details) {
        $stmt = $this->db->prepare("SELECT balance FROM gift_cards WHERE code=?");
        $stmt->execute([$details['gift_card_code']]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $card && $card['balance'] > 0;
    }

    private function luhnCheck($number) {
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return $sum % 10 === 0;
    }

    private function encryptPaymentDetails($details) {
        // Use Azure Key Vault for encryption
        $key = getenv('ENCRYPTION_KEY');
        return openssl_encrypt(json_encode($details), 'AES-256-CBC', $key, true);
    }

    private function generateTransactionId() {
        return 'TXN-' . time() . '-' . bin2hex(random_bytes(8));
    }

    // Refund payment
    public function refundPayment($paymentId) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id=?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payment['status'] !== 'completed') {
                throw new \Exception('Only completed payments can be refunded');
            }

            // Create refund record
            $stmt = $this->db->prepare("
                INSERT INTO refunds (payment_id, amount, status, created_at)
                VALUES (?, ?, 'processing', NOW())
            ");
            $stmt->execute([$paymentId, $payment['amount']]);

            // Update payment status
            $stmt = $this->db->prepare("UPDATE payments SET status='refunded' WHERE id=?");
            $stmt->execute([$paymentId]);

            $this->db->commit();

            return ['success' => true, 'refund_id' => $this->db->lastInsertId()];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}