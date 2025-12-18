<?php
namespace App\Controllers;

use PDO;

class WishlistController {
    private $db;
    private $cache;
    private $userId;

    public function __construct(PDO $db, $cache, $userId) {
        $this->db = $db;
        $this->cache = $cache;
        $this->userId = $userId;
    }

    // Get wishlist
    public function getWishlist() {
        try {
            $cacheKey = "wishlist_{$this->userId}";
            
            // Check cache first if available
            if ($this->cache) {
                try {
                    $cached = $this->cache->get($cacheKey);
                    if ($cached) {
                        return json_decode($cached, true);
                    }
                } catch (\Exception $e) {
                    // Cache read failed, continue
                }
            }

            $stmt = $this->db->prepare("
                SELECT p.*, w.added_at FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC
            ");
            $stmt->execute([$this->userId]);
            $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cache wishlist if cache available
            if ($this->cache) {
                try {
                    $this->cache->setex($cacheKey, CACHE_TIMEOUT, json_encode($wishlist));
                } catch (\Exception $e) {
                    // Cache write failed, continue
                }
            }
            
            return $wishlist;
        } catch (\Exception $e) {
            error_log('Wishlist fetch error: ' . $e->getMessage());
            return [];
        }
    }

    // Add to wishlist
    public function addToWishlist($productId) {
        if (!is_numeric($productId)) {
            return ['success' => false, 'message' => 'Invalid product'];
        }

        try {
            // Check if already in wishlist
            $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->userId, $productId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Already in wishlist'];
            }

            // Verify product exists
            $stmt = $this->db->prepare("SELECT id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Product not found'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO wishlist (user_id, product_id, added_at) VALUES (?, ?, NOW())
            ");
            $result = $stmt->execute([$this->userId, $productId]);

            // Clear cache if available
            if ($this->cache) {
                try {
                    $this->cache->delete("wishlist_{$this->userId}");
                } catch (\Exception $e) {
                    // Cache delete failed, continue
                }
            }

            return ['success' => $result];
        } catch (\Exception $e) {
            error_log('Add to wishlist error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add to wishlist'];
        }
    }

    // Remove from wishlist
    public function removeFromWishlist($productId) {
        if (!is_numeric($productId)) {
            return ['success' => false, 'message' => 'Invalid product'];
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$this->userId, $productId]);

            // Clear cache if available
            if ($this->cache) {
                try {
                    $this->cache->delete("wishlist_{$this->userId}");
                } catch (\Exception $e) {
                    // Cache delete failed, continue
                }
            }

            return ['success' => $result];
        } catch (\Exception $e) {
            error_log('Remove from wishlist error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to remove from wishlist'];
        }
    }

    // Check if product in wishlist
    public function isInWishlist($productId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->userId, $productId]);
            
            return !!$stmt->fetch();
        } catch (\Exception $e) {
            error_log('Wishlist check error: ' . $e->getMessage());
            return false;
        }
    }

    // Get wishlist count
    public function getWishlistCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (\Exception $e) {
            error_log('Wishlist count error: ' . $e->getMessage());
            return 0;
        }
    }
}