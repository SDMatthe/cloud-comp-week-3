<?php
require_once 'config.php';
require_once 'cartcontroller.php';

// Session already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit();
}

$cartItems = [];
$total = 0;
$error = '';

try {
    $pdo = getDBConnection();
    if ($pdo) {
        $cartController = new CartController($pdo, $_SESSION['user_id']);
        $cartData = $cartController->getCart();
        $cartItems = $cartData['items'];
        $total = $cartData['total'];
    }
} catch (Exception $e) {
    $error = 'Failed to load cart items';
    error_log('Cart error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .cart-table th {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .cart-table tr:hover {
            background-color: #f5f5f5;
        }
        .remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: right;
        }
        .summary-row {
            display: flex;
            justify-content: flex-end;
            padding: 10px 0;
            font-size: 16px;
        }
        .summary-row strong {
            margin-left: 20px;
            min-width: 100px;
            text-align: right;
        }
        .total-row {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #ddd;
            padding-top: 15px;
        }
        .checkout-btn {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .checkout-btn:hover {
            background-color: #218838;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
        }
        .btn-continue {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 15px;
        }
        .btn-continue:hover {
            background-color: #2980b9;
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
                    <a href="cart.php" class="nav-link active">Cart</a>
                </li>
                <li class="nav-item">
                    <a href="wishlist.php" class="nav-link">Wishlist</a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link">Orders</a>
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

    <div class="container cart-container">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if (!empty($error)): ?>
            <div style="color: #dc3545; padding: 15px; background: #f8d7da; border-radius: 4px; margin: 20px 0;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <h2>Your shopping cart is empty</h2>
                <p>Start adding products to your cart!</p>
                <a href="products.php" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo intval($item['quantity']); ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <button class="remove-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong>$<?php echo number_format($total, 2); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <strong>$0.00</strong>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <strong>$0.00</strong>
                </div>
                <div class="summary-row total-row">
                    <span>Total:</span>
                    <strong>$<?php echo number_format($total, 2); ?></strong>
                </div>
                <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                <br>
                <a href="products.php" class="btn-continue" style="display: inline-block;">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function removeFromCart(productId) {
            if (!confirm('Remove this item from your cart?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);

            fetch('cart-action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>