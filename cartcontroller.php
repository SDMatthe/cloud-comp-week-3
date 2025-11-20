<?php
namespace App\Controllers;

class CartController {
    private $db;
    private $cache;
    private $userId;

    public function __construct(PDO $db, \Redis $cache, $userId) {
        $this->db = $db;
        $this->cache = $cache;
        $this->userId = $userId;
    }

    // Get cart from Redis
    public function getCart() {
        $cartKey = "cart_{$this->userId}";
        $cart = $this->cache->get($cartKey);
        return $cart ? json_decode($cart, true) : ['items' => [], 'total' => 0];
    }

    // Add to cart
    public function addItem($productId, $quantity) {
        $cartKey = "cart_{$this->userId}";
        $cart = $this->getCart();

        // Get product details
        $stmt = $this->db->prepare("SELECT id, name, price, stock FROM products WHERE id=?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }

        // Add or update item
        $found = false;
        foreach ($cart['items'] as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart['items'][] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        $this->calculateTotal($cart);
        $this->cache->setex($cartKey, 86400, json_encode($cart)); // 24 hours

        return ['success' => true, 'cart' => $cart];
    }

    // Remove from cart
    public function removeItem($productId) {
        $cartKey = "cart_{$this->userId}";
        $cart = $this->getCart();
        
        $cart['items'] = array_filter($cart['items'], function($item) use ($productId) {
            return $item['product_id'] !== $productId;
        });

        $this->calculateTotal($cart);
        $this->cache->setex($cartKey, 86400, json_encode($cart));

        return ['success' => true, 'cart' => $cart];
    }

    // Checkout
    public function checkout($paymentMethod, $shippingAddress) {
        $cart = $this->getCart();

        if (empty($cart['items'])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        try {
            $this->db->beginTransaction();

            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$this->userId, $cart['total'], $paymentMethod, json_encode($shippingAddress)]);
            $orderId = $this->db->lastInsertId();

            // Add order items
            foreach ($cart['items'] as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);

                // Update stock
                $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $this->db->commit();

            // Clear cart
            $this->cache->delete("cart_{$this->userId}");

            // Send to Service Bus queue for processing
            $this->queueOrder($orderId);

            return ['success' => true, 'order_id' => $orderId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function calculateTotal(&$cart) {
        $total = 0;
        foreach ($cart['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $cart['total'] = round($total, 2);
    }

    private function queueOrder($orderId) {
        // Queue to Azure Service Bus for async processing
        // This would be implemented with Azure SDK
    }
}
