<?php
namespace App\Controllers;

use PDO;
use Exception;

class ProductController {
    private $db;
    private $cache;

    public function __construct(PDO $db, \Redis $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Get all products with caching
    public function getProducts($page = 1, $limit = 20) {
        $cacheKey = "products_page_{$page}_{$limit}";
        
        try {
            // Check cache first
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return json_decode($cached, true);
            }

            $offset = ($page - 1) * $limit;
            $stmt = $this->db->prepare("
                SELECT id, name, description, price, stock, image_url, category 
                FROM products 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cache for 1 hour
            $this->cache->setex($cacheKey, CACHE_TIMEOUT, json_encode($products));
            
            return $products;
        } catch (Exception $e) {
            error_log('Get products error: ' . $e->getMessage());
            return [];
        }
    }

    // Get single product
    public function getProduct($id) {
        if (!is_numeric($id)) {
            return null;
        }

        try {
            $cacheKey = "product_{$id}";
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return json_decode($cached, true);
            }

            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $this->cache->setex($cacheKey, CACHE_TIMEOUT, json_encode($product));
            }
            
            return $product;
        } catch (Exception $e) {
            error_log('Get product error: ' . $e->getMessage());
            return null;
        }
    }

    // Add product (Admin)
    public function addProduct($data) {
        if (!isset($data['name'], $data['price'], $data['stock'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, price, stock, image_url, category, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['stock'],
                $data['image_url'] ?? '',
                $data['category'] ?? ''
            ]);
            
            // Clear cache
            $this->cache->flushdb();
            
            return ['success' => $result, 'product_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            error_log('Add product error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add product'];
        }
    }

    // Update stock (Real-time)
    public function updateStock($productId, $quantity) {
        if (!is_numeric($productId) || !is_numeric($quantity) || $quantity < 0) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
            $result = $stmt->execute([$quantity, $productId]);
            
            // Clear product cache
            $this->cache->delete("product_{$productId}");
            
            return $result;
        } catch (Exception $e) {
            error_log('Update stock error: ' . $e->getMessage());
            return false;
        }
    }

    // Edit product
    public function editProduct($id, $data) {
        if (!is_numeric($id)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE products SET name=?, description=?, price=?, stock=?, category=?, updated_at=NOW()
                WHERE id=?
            ");
            
            $result = $stmt->execute([
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? 0,
                $data['stock'] ?? 0,
                $data['category'] ?? '',
                $id
            ]);

            // Clear caches
            $this->cache->delete("product_{$id}");
            $this->cache->flushdb();
            
            return $result;
        } catch (Exception $e) {
            error_log('Edit product error: ' . $e->getMessage());
            return false;
        }
    }

    // Get total products count
    public function getTotalProducts() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Get total products error: ' . $e->getMessage());
            return 0;
        }
    }

    // Search products with caching
    public function searchProducts($query, $limit = 20) {
        if (empty($query)) {
            return [];
        }

        try {
            $cacheKey = "search_" . md5($query) . "_{$limit}";
            
            // Check cache first
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return json_decode($cached, true);
            }

            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT * FROM products 
                WHERE name LIKE ? OR description LIKE ? 
                LIMIT ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $limit]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cache results
            $this->cache->setex($cacheKey, CACHE_TIMEOUT, json_encode($results));
            
            return $results;
        } catch (Exception $e) {
            error_log('Search products error: ' . $e->getMessage());
            return [];
        }
    }
}
