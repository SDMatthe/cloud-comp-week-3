<?php
class CartController {
    private $db;
    private $userId;

    public function __construct(PDO $db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function getCart() {
        $stmt = $this->db->prepare("
            SELECT c.id, c.product_id, p.name, p.price, c.quantity
            FROM cart_items c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $items));
        
        return ['items' => $items, 'total' => $total];
    }

    public function addItem($productId, $quantity) {
        // Validate inputs
        if (!is_numeric($productId) || !is_numeric($quantity) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid product or quantity'];
        }

        // Validate stock first
        $stmt = $this->db->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }

        // Add or update cart
        $stmt = $this->db->prepare("
            INSERT INTO cart_items (user_id, product_id, quantity) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        $stmt->execute([$this->userId, $productId, $quantity, $quantity]);

        return ['success' => true, 'message' => 'Added to cart'];
    }

    public function removeItem($productId) {
        if (!is_numeric($productId)) {
            return ['success' => false, 'message' => 'Invalid product'];
        }

        $stmt = $this->db->prepare("
            DELETE FROM cart_items 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$this->userId, $productId]);

        return ['success' => true];
    }

    public function checkout($paymentMethod, $shippingAddress) {
        if (empty($paymentMethod) || empty($shippingAddress)) {
            return ['success' => false, 'message' => 'Missing payment or shipping info'];
        }

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

            // Move items from cart to order_items
            foreach ($cart['items'] as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            // Clear cart
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$this->userId]);

            $this->db->commit();

            return ['success' => true, 'order_id' => $orderId];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Checkout error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Checkout failed'];
        }
    }
}
?>