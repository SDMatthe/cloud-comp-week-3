<?php
require_once 'config.php';
require_once 'cartcontroller.php';
require_once 'OrderTrackingController.php';
require_once 'PaymentController.php';

// Session already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$cartItems = [];
$total = 0;
$error = '';
$success = '';
$orderId = null;

try {
    $pdo = getDBConnection();
    if ($pdo) {
        $cartController = new CartController($pdo, $_SESSION['user_id']);
        $cartData = $cartController->getCart();
        $cartItems = $cartData['items'];
        $total = $cartData['total'];
        
        // Get user info
        $stmt = $pdo->prepare("SELECT * FROM shopusers WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = 'Failed to load cart items';
    error_log('Checkout error: ' . $e->getMessage());
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zipCode'] ?? '');
    $paymentMethod = $_POST['paymentMethod'] ?? 'credit_card';
    $cardNumber = trim($_POST['cardNumber'] ?? '');
    $cardExpiry = trim($_POST['cardExpiry'] ?? '');
    $cardCVV = trim($_POST['cardCVV'] ?? '');
    
    // Validate inputs
    if (empty($cartItems)) {
        $error = 'Your cart is empty';
    } elseif (empty($fullName) || empty($email) || empty($address) || empty($city) || empty($state) || empty($zipCode)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            $pdo = getDBConnection();
            if ($pdo) {
                // Create order with shipping address
                // Note: Shipping address is stored in orders table (JSON format)
                // User profile columns (address, city, etc.) are not in the schema
                $cartController = new CartController($pdo, $_SESSION['user_id']);
                $shippingAddress = [
                    'fullName' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'zipCode' => $zipCode
                ];
                
                $result = $cartController->checkout($paymentMethod, $shippingAddress);
                
                if ($result['success']) {
                    $orderId = $result['order_id'];
                    
                    // Process payment
                    $paymentController = new \App\Controllers\PaymentController($pdo, null);
                    $paymentDetails = [
                        'card_number' => $cardNumber,
                        'expiry' => $cardExpiry,
                        'cvv' => $cardCVV
                    ];
                    
                    $paymentResult = $paymentController->processPayment(
                        $orderId,
                        $_SESSION['user_id'],
                        $paymentMethod,
                        $paymentDetails
                    );
                    
                    if ($paymentResult['success']) {
                        // Update order status to confirmed
                        $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'completed' WHERE id = ?");
                        $stmt->execute([$orderId]);
                        
                        // Log order status
                        $stmt = $pdo->prepare("
                            INSERT INTO order_status_log (order_id, status, notes, created_at)
                            VALUES (?, ?, ?, NOW())
                        ");
                        $stmt->execute([$orderId, 'confirmed', 'Payment processed successfully']);
                        
                        $success = '✅ Order Received! Order ID: #' . $orderId . ' - Redirecting to order details...';
                        // Redirect after 2 seconds
                        echo '<meta http-equiv="refresh" content="2;url=order-details.php?order_id=' . $orderId . '">';
                        exit();
                    } else {
                        $error = 'Payment failed: ' . $paymentResult['message'];
                        // Delete the order if payment failed
                        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                        $stmt->execute([$orderId]);
                    }
                } else {
                    $error = $result['message'];
                }
            }
        } catch (Exception $e) {
            $error = 'Checkout failed: ' . $e->getMessage();
            error_log('Checkout error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }
        .checkout-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .checkout-section h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .order-summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-total {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #3498db;
        }
        .btn-place-order {
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-place-order:hover {
            background-color: #218838;
        }
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
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

    <div class="checkout-container">
        <h1 class="page-title">Checkout</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="checkout-section">
                <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
            </div>
        <?php else: ?>

            <form method="POST" class="checkout-form">
                <div class="checkout-grid">
                    <!-- Left Column: Shipping & Payment -->
                    <div>
                        <div class="checkout-section">
                            <h3>Shipping Address</h3>
                            
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="fullName" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Street Address *</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>State *</label>
                                    <input type="text" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Zip Code *</label>
                                <input type="text" name="zipCode" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="checkout-section">
                            <h3>Payment Method</h3>

                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="paymentMethod" required>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="virtual_wallet">Virtual Wallet</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Card Number *</label>
                                <input type="text" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiry Date *</label>
                                    <input type="text" name="cardExpiry" placeholder="MM/YY" maxlength="5" required>
                                </div>
                                <div class="form-group">
                                    <label>CVV *</label>
                                    <input type="text" name="cardCVV" placeholder="123" maxlength="3" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div>
                        <div class="checkout-section">
                            <h3>Order Summary</h3>

                            <div class="order-summary">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="summary-item">
                                        <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo intval($item['quantity']); ?></span>
                                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-summary">
                                <div class="summary-item">
                                    <span>Subtotal</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span>Shipping</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="summary-item">
                                    <span>Tax</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="summary-total">
                                    <span>Total</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <button type="submit" name="checkout" class="btn-place-order">Place Order</button>
                            <a href="cart.php" style="display: block; text-align: center; margin-top: 10px; color: #3498db;">Back to Cart</a>
                        </div>
                    </div>
                </div>
            </form>

        <?php endif; ?>
    </div>

    <script>
        // Format card number input
        document.querySelector('input[name="cardNumber"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^\d]/g, '');
            let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formatted;
        });

        // Format expiry date
        document.querySelector('input[name="cardExpiry"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        // Only allow numbers in CVV
        document.querySelector('input[name="cardCVV"]')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^\d]/g, '');
        });
    </script>
</body>
</html>
