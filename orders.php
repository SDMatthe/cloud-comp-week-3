<?php
require_once 'config.php';
require_once 'OrderTrackingController.php';

// Session already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders.php');
    exit();
}

$orders = [];
$error = '';

try {
    $pdo = getDBConnection();
    if ($pdo) {
        $orderController = new \App\Controllers\OrderTrackingController($pdo);
        $orders = $orderController->getUserOrders($_SESSION['user_id']);
    }
} catch (Exception $e) {
    $error = 'Failed to load orders: ' . $e->getMessage();
    error_log('Orders page error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .order-id {
            font-weight: bold;
            color: #2c3e50;
            font-size: 16px;
        }
        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .status-pending {
            background-color: #f39c12;
        }
        .status-processing {
            background-color: #3498db;
        }
        .status-shipped {
            background-color: #9b59b6;
        }
        .status-delivered {
            background-color: #27ae60;
        }
        .status-cancelled {
            background-color: #e74c3c;
        }
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .info-item {
            font-size: 14px;
        }
        .info-label {
            color: #999;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .info-value {
            color: #333;
        }
        .order-total {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .btn-view-details {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view-details:hover {
            background-color: #2980b9;
        }
        .empty-orders {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
        }
        .btn-shop {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 15px;
        }
        .btn-shop:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">ShopSphere</a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">Shop</a>
                </li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">Cart</a>
                </li>
                <li class="nav-item">
                    <a href="wishlist.php" class="nav-link">Wishlist</a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link active">Orders</a>
                </li>
            </ul>

            <div class="nav-auth">
                <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<span style="color: white;">' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . '</span>';
                        echo '<a href="logout.php" class="btn-logout">Logout</a>';
                    } else {
                        echo '<a href="login.php" class="btn-login">Login</a>';
                        echo '<a href="register.php" class="btn-signup">Sign Up</a>';
                    }
                ?>
            </div>
        </div>
    </nav>

    <div class="orders-container">
        <h1 class="page-title">My Orders</h1>

        <?php if ($error): ?>
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (count($orders) === 0): ?>
            <div class="empty-orders">
                <h2>You have no orders yet</h2>
                <p>Start shopping today and track your orders here!</p>
                <a href="products.php" class="btn-shop">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                            <div style="color: #999; font-size: 12px;">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <span class="order-status status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </div>

                    <div class="order-info">
                        <div class="info-item">
                            <div class="info-label">Amount</div>
                            <div class="info-value order-total">$<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Payment Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?></div>
                        </div>
                        <div class="info-item" style="text-align: right;">
                            <a href="order-details.php?order_id=<?php echo urlencode($order['id']); ?>" class="btn-view-details">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>