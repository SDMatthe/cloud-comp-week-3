<?php
namespace App\Controllers;

use PDO;

class PaymentController {
    private $db;
    private $cache;

    public function __construct(PDO $db, $cache = null) {
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

            // Get order total
            $orderStmt = $this->db->prepare("SELECT total_amount FROM orders WHERE id = ? AND user_id = ?");
            $orderStmt->execute([$orderId, $userId]);
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Validate payment based on method
            $isValid = $this->validatePayment($paymentMethod, $paymentDetails);
            if (!$isValid) {
                throw new \Exception('Payment validation failed');
            }

            // Create payment record
            $stmt = $this->db->prepare("
                INSERT INTO payments (order_id, user_id, method, amount, status, created_at)
                VALUES (?, ?, ?, ?, 'processing', NOW())
            ");
            $stmt->execute([$orderId, $userId, $paymentMethod, $order['total_amount']]);
            $paymentId = $this->db->lastInsertId();

            // Generate transaction ID
            $transactionId = $this->generateTransactionId();

            // Update payment status to completed
            $stmt = $this->db->prepare("
                UPDATE payments SET status = 'completed', transaction_id = ? WHERE id = ?
            ");
            $stmt->execute([$transactionId, $paymentId]);

            // Update order status
            $stmt = $this->db->prepare("UPDATE orders SET status = 'confirmed', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$orderId]);

            $this->db->commit();

            // Cache successful payment if cache available
            if ($this->cache) {
                try {
                    $this->cache->setex("payment_{$paymentId}", CACHE_TIMEOUT, json_encode([
                        'transaction_id' => $transactionId,
                        'status' => 'completed'
                    ]));
                } catch (\Exception $e) {
                    // Cache write failed, continue
                }
            }

            return ['success' => true, 'transaction_id' => $transactionId, 'payment_id' => $paymentId];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Payment processing error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment failed'];
        }
    }

    // Add payment method
    public function addPaymentMethod($userId, $method, $details) {
        if (!in_array($method, $this->paymentMethods)) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_payment_methods (user_id, method, details, is_default, created_at)
                VALUES (?, ?, ?, FALSE, NOW())
            ");
            
            $result = $stmt->execute([$userId, $method, json_encode($details)]);

            return ['success' => $result, 'method_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('Add payment method error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add payment method'];
        }
    }

    // Get user's payment methods
    public function getPaymentMethods($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, method, is_default, created_at FROM user_payment_methods WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get payment methods error: ' . $e->getMessage());
            return [];
        }
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
        if (!isset($details['card_number'])) {
            return false;
        }
        $cardNumber = preg_replace('/\D/', '', $details['card_number']);
        return strlen($cardNumber) === 16 && $this->luhnCheck($cardNumber);
    }

    private function validateWallet($details) {
        return isset($details['wallet_id']) && isset($details['amount']) && $details['amount'] > 0;
    }

    private function validateBankDetails($details) {
        return isset($details['account_number']) && isset($details['routing_number']);
    }

    private function validateGiftCard($details) {
        if (!isset($details['gift_card_code'])) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT balance FROM gift_cards WHERE code = ?");
            $stmt->execute([$details['gift_card_code']]);
            $card = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $card && $card['balance'] > 0;
        } catch (\Exception $e) {
            error_log('Gift card validation error: ' . $e->getMessage());
            return false;
        }
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

    private function generateTransactionId() {
        return 'TXN-' . time() . '-' . bin2hex(random_bytes(8));
    }

    // Refund payment
    public function refundPayment($paymentId) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            if ($payment['status'] !== 'completed') {
                throw new \Exception('Only completed payments can be refunded');
            }

            // Create refund record
            $stmt = $this->db->prepare("
                INSERT INTO refunds (payment_id, amount, status, created_at)
                VALUES (?, ?, 'completed', NOW())
            ");
            $stmt->execute([$paymentId, $payment['amount']]);

            // Update payment status
            $stmt = $this->db->prepare("UPDATE payments SET status = 'refunded', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$paymentId]);

            $this->db->commit();

            return ['success' => true, 'refund_id' => $this->db->lastInsertId()];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Refund error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Refund failed'];
        }
    }
}