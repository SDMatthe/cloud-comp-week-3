<?php
namespace App\Controllers;

class WishlistController {
    private $db;
    private $cache;
    private $userId;

    public function __construct(PDO $db, \Redis $cache, $userId) {
        $this->db = $db;
        $this->cache = $cache;
        $this->userId = $userId;
    }

    // Get wishlist
    public function getWishlist() {
        $stmt = $this->db->prepare("
            SELECT p.*, w.added_at FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id=?
            ORDER BY w.added_at DESC
        ");
        $stmt->execute([$this->userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add to wishlist
    public function addToWishlist($productId) {
        // Check if already in wishlist
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
        $stmt->execute([$this->userId, $productId]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Already in wishlist'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO wishlist (user_id, product_id, added_at) VALUES (?, ?, NOW())
        ");
        $result = $stmt->execute([$this->userId, $productId]);

        // Clear cache
        $this->cache->delete("wishlist_{$this->userId}");

        return ['success' => $result];
    }

    // Remove from wishlist
    public function removeFromWishlist($productId) {
        $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?");
        $result = $stmt->execute([$this->userId, $productId]);

        $this->cache->delete("wishlist_{$this->userId}");

        return ['success' => $result];
    }

    // Check if product in wishlist
    public function isInWishlist($productId) {
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
        $stmt->execute([$this->userId, $productId]);
        
        return !!$stmt->fetch();
    }

    // Get wishlist count
    public function getWishlistCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id=?");
        $stmt->execute([$this->userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}