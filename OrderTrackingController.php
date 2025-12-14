<?php
namespace App\Controllers;

use PDO;

class OrderTrackingController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Get order status
    public function getOrder($orderId, $userId) {
        if (!is_numeric($orderId) || !is_numeric($userId)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $userId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            // Get order items
            $stmt = $this->db->prepare("
                SELECT oi.*, p.name, p.image_url FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'order' => $order];
        } catch (\Exception $e) {
            error_log('Get order error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to fetch order'];
        }
    }

    // Get all orders for user
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        if (!is_numeric($userId) || !is_numeric($page) || !is_numeric($limit)) {
            return [];
        }

        try {
            $offset = ($page - 1) * $limit;
            $stmt = $this->db->prepare("
                SELECT id, total_amount, status, created_at, updated_at
                FROM orders WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get user orders error: ' . $e->getMessage());
            return [];
        }
    }

    // Update order status (Admin/System)
    public function updateOrderStatus($orderId, $status, $notes = '') {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?
            ");
            $result = $stmt->execute([$status, $orderId]);

            // Log status change
            if (!empty($notes)) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_status_log (order_id, status, notes, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$orderId, $status, $notes]);
            }

            return ['success' => $result];
        } catch (\Exception $e) {
            error_log('Update order status error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update order'];
        }
    }

    // Get order tracking history
    public function getTrackingHistory($orderId) {
        if (!is_numeric($orderId)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT status, notes, created_at FROM order_status_log
                WHERE order_id = ?
                ORDER BY created_at ASC
            ");
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get tracking history error: ' . $e->getMessage());
            return [];
        }
    }

    // Get order count by status
    public function getOrderCountByStatus($userId) {
        if (!is_numeric($userId)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count FROM orders
                WHERE user_id = ?
                GROUP BY status
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get order count error: ' . $e->getMessage());
            return [];
        }
    }
}