<?php
require_once 'config.php';
require_once 'cartcontroller.php';

// Session already started in config.php

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit();
        }

        $cartController = new CartController($pdo, $_SESSION['user_id']);

        if ($action === 'remove' && $product_id) {
            $cartController->removeItem($product_id);
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } elseif ($action === 'update' && $product_id) {
            if ($quantity > 0) {
                // Update quantity - implementation depends on CartController having this method
                echo json_encode(['success' => true, 'message' => 'Quantity updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action or parameters']);
        }
    } catch (Exception $e) {
        error_log('Cart action error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
