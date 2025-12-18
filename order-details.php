<?php
require_once 'config.php';
require_once 'OrderTrackingController.php';

// Session already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders.php');
    exit();
}

$order = null;
$trackingHistory = [];
$error = '';
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($orderId <= 0) {
    $error = 'Invalid order ID';
} else {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $orderController = new \App\Controllers\OrderTrackingController($pdo);
            $result = $orderController->getOrder($orderId, $_SESSION['user_id']);
            
            if ($result['success']) {
                $order = $result['order'];
                $trackingHistory = $orderController->getTrackingHistory($orderId);
            } else {
                $error = $result['message'];
            }
        }
    } catch (Exception $e) {
        $error = 'Failed to load order: ' . $e->getMessage();
        error_log('Order details error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .order-details-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-details-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .order-details-section h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            font-size: 14px;
        }
        .detail-label {
            color: #999;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #333;
            font-weight: bold;
        }
        .order-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
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
        .status-confirmed {
            background-color: #3498db;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items-table th {
            background-color: #f5f5f5;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .order-items-table tr:hover {
            background-color: #f9f9f9;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            display: flex;
            margin-bottom: 30px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 2px;
            height: 100%;
            background-color: #ddd;
        }
        .timeline-item:first-child:before {
            background-color: #27ae60;
        }
        .timeline-dot {
            width: 12px;
            height: 12px;
            background-color: #ddd;
            border-radius: 50%;
            margin-top: 5px;
            margin-right: 20px;
            position: relative;
            z-index: 1;
        }
        .timeline-item:first-child .timeline-dot {
            background-color: #27ae60;
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-status {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .timeline-date {
            font-size: 12px;
            color: #999;
        }
        .timeline-notes {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .back-link {
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">ShopSphere</a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Shop</a></li>
                <li class="nav-item"><a href="cart.php" class="nav-link">Cart</a></li>
                <li class="nav-item"><a href="wishlist.php" class="nav-link">Wishlist</a></li>
                <li class="nav-item"><a href="orders.php" class="nav-link">Orders</a></li>
            </ul>
            <div class="nav-auth">
                <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<span style="color: white;">' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . '</span>';
                        echo '<a href="logout.php" class="btn-logout">Logout</a>';
                    }
                ?>
            </div>
        </div>
    </nav>

    <div class="order-details-container">
        <a href="orders.php" class="back-link">← Back to Orders</a>

        <h1 class="page-title">Order Details</h1>

        <?php if ($error): ?>
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($order): ?>

            <!-- Order Header -->
            <div class="order-details-section">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Order ID</div>
                        <div class="detail-value">#<?php echo htmlspecialchars($order['id']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Order Date</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <span class="order-status status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="order-details-section">
                <h3>Shipping Address</h3>
                <?php
                    $shipping = json_decode($order['shipping_address'], true);
                    if ($shipping):
                ?>
                <p>
                    <strong><?php echo htmlspecialchars($shipping['fullName'] ?? 'N/A'); ?></strong><br>
                    <?php echo htmlspecialchars($shipping['address'] ?? 'N/A'); ?><br>
                    <?php echo htmlspecialchars($shipping['city'] ?? '') . ', ' . htmlspecialchars($shipping['state'] ?? '') . ' ' . htmlspecialchars($shipping['zipCode'] ?? ''); ?><br>
                    Phone: <?php echo htmlspecialchars($shipping['phone'] ?? 'N/A'); ?><br>
                    Email: <?php echo htmlspecialchars($shipping['email'] ?? 'N/A'); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Order Items -->
            <div class="order-details-section">
                <h3>Order Items</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo intval($item['quantity']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="text-align: right; margin-top: 15px; font-size: 18px; font-weight: bold;">
                    Total: $<?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>

            <!-- Tracking History -->
            <?php if (count($trackingHistory) > 0): ?>
                <div class="order-details-section">
                    <h3>Order Tracking</h3>
                    <div class="timeline">
                        <?php foreach ($trackingHistory as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-status"><?php echo ucfirst(htmlspecialchars($history['status'])); ?></div>
                                    <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($history['created_at'])); ?></div>
                                    <?php if ($history['notes']): ?>
                                        <div class="timeline-notes"><?php echo htmlspecialchars($history['notes']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <p>Order not found.</p>
                <a href="orders.php">Back to Orders</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
