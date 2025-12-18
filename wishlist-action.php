<?php
/**
 * Wishlist Action Handler
 * Handles AJAX requests to add/remove items from wishlist
 */

require_once 'config.php';

// Load WishlistController - must handle namespaces
require_once 'WishlistController.php';

// Session already started in config.php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Initialize Redis (optional)
    $redis = null;
    try {
        if (class_exists('Redis')) {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
            $redis->select(REDIS_DB);
        }
    } catch (Exception $e) {
        // Redis not available
    }

    $wishlistController = new \App\Controllers\WishlistController($pdo, $redis, $_SESSION['user_id']);
    
    // Check if already in wishlist
    $isInWishlist = $wishlistController->isInWishlist($productId);
    
    if ($isInWishlist) {
        // Remove from wishlist
        $result = $wishlistController->removeFromWishlist($productId);
    } else {
        // Add to wishlist
        $result = $wishlistController->addToWishlist($productId);
    }

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $isInWishlist ? 'Removed from wishlist' : 'Added to wishlist',
            'product_id' => $productId,
            'inWishlist' => !$isInWishlist
        ]);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log('Wishlist action error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
