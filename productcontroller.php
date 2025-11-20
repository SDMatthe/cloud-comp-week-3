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
        $this->cache->setex($cacheKey, 3600, json_encode($products));
        
        return $products;
    }

    // Get single product
    public function getProduct($id) {
        $cacheKey = "product_{$id}";
        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }

        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->cache->setex($cacheKey, 3600, json_encode($product));
        return $product;
    }

    // Add product (Admin)
    public function addProduct($data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, description, price, stock, image_url, category, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image_url'],
            $data['category']
        ]);

        // Clear cache
        $this->cache->flushdb();
        
        return ['success' => $result, 'product_id' => $this->db->lastInsertId()];
    }

    // Update stock (Real-time)
    public function updateStock($productId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE products SET stock = stock - ? WHERE id = ?
        ");
        $result = $stmt->execute([$quantity, $productId]);
        
        // Clear product cache
        $this->cache->delete("product_{$productId}");
        
        return $result;
    }

    // Edit product
    public function editProduct($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE products SET name=?, description=?, price=?, stock=?, category=?, updated_at=NOW()
            WHERE id=?
        ");
        
        $result = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['category'],
            $id
        ]);

        $this->cache->delete("product_{$id}");
        $this->cache->flushdb();
        
        return $result;
    }
}
