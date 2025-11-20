<?php
namespace App\Controllers;

class OrderTrackingController {
    private $db;
    private $cosmosDb; // Azure Cosmos DB for real-time updates

    public function __construct(PDO $db, $cosmosDb = null) {
        $this->db = $db;
        $this->cosmosDb = $cosmosDb;
    }

    // Get order status
    public function getOrder($orderId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Get order items
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, p.image_url FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id=?
        ");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get tracking updates (if available via Cosmos DB)
        if ($this->cosmosDb) {
            $order['tracking'] = $this->getTrackingUpdates($orderId);
        }

        return ['success' => true, 'order' => $order];
    }

    // Get all orders for user
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT id, total_amount, status, created_at, updated_at
            FROM orders WHERE user_id=?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order status (Admin/System)
    public function updateOrderStatus($orderId, $status, $notes = '') {
        $stmt = $this->db->prepare("
            UPDATE orders SET status=?, updated_at=NOW() WHERE id=?
        ");
        $stmt->execute([$status, $orderId]);

        // Store tracking event in Cosmos DB for real-time updates
        if ($this->cosmosDb) {
            $this->logTrackingEvent($orderId, $status, $notes);
        }

        return ['success' => true];
    }

    // Get real-time tracking updates
    private function getTrackingUpdates($orderId) {
        // Query Cosmos DB for tracking events
        // Returns: pending → processing → shipped → delivered
        return [];
    }

    private function logTrackingEvent($orderId, $status, $notes) {
        // Save to Cosmos DB for real-time updates via WebSocket
    }
}