<?php
require_once 'config.php';
require_once 'WishlistController.php';

// Session already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=wishlist.php');
    exit();
}

$wishlistItems = [];
$error = '';

try {
    $pdo = getDBConnection();
    if ($pdo) {
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
        $wishlistItems = $wishlistController->getWishlist();
    }
} catch (Exception $e) {
    $error = 'Failed to load wishlist: ' . $e->getMessage();
    error_log('Wishlist page error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .wishlist-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .wishlist-item:hover {
            transform: translateY(-5px);
        }
        .item-image {
            width: 100%;
            height: 200px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        .item-content {
            padding: 15px;
        }
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
        }
        .item-price {
            color: #2c3e50;
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .item-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-add-cart {
            flex: 1;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-add-cart:hover {
            background-color: #218838;
        }
        .btn-remove {
            flex: 0 0 auto;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .empty-wishlist {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 8px;
        }
        .empty-wishlist p {
            color: #666;
            margin-bottom: 20px;
        }
        .btn-shop {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-shop:hover {
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
                    <a href="cart.php" class="nav-link">Cart</a>
                </li>
                <li class="nav-item">
                    <a href="wishlist.php" class="nav-link active">Wishlist</a>
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

    <div class="container">
        <h1 class="page-title">My Wishlist</h1>

        <?php if ($error): ?>
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="wishlist-grid">
            <?php
                if (count($wishlistItems) > 0) {
                    foreach ($wishlistItems as $item) {
                        echo '<div class="wishlist-item">';
                        echo '  <div class="item-image">' . ($item['image_url'] ? htmlspecialchars($item['image_url']) : '📦') . '</div>';
                        echo '  <div class="item-content">';
                        echo '    <div class="item-name">' . htmlspecialchars($item['name']) . '</div>';
                        echo '    <div class="item-price">$' . number_format($item['price'], 2) . '</div>';
                        echo '    <div style="font-size: 12px; color: ' . ($item['stock'] > 0 ? '#28a745' : '#dc3545') . ';">' . ($item['stock'] > 0 ? 'In Stock (' . $item['stock'] . ')' : 'Out of Stock') . '</div>';
                        echo '    <div class="item-actions">';
                        
                        if ($item['stock'] > 0) {
                            echo '      <button class="btn-add-cart" onclick="addToCart(' . $item['id'] . ', \'' . htmlspecialchars($item['name']) . '\')">Add to Cart</button>';
                        }
                        
                        echo '      <button class="btn-remove" onclick="removeFromWishlist(' . $item['id'] . ')">✕</button>';
                        echo '    </div>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="empty-wishlist">';
                    echo '<p>Your wishlist is empty</p>';
                    echo '<p>Start adding your favorite items to your wishlist!</p>';
                    echo '<a href="products.php" class="btn-shop">Explore Products</a>';
                    echo '</div>';
                }
            ?>
        </div>
    </div>

    <script>
        function addToCart(productId, productName) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + productName + ' added to cart!');
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function removeFromWishlist(productId) {
            if (confirm('Remove from wishlist?')) {
                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('wishlist-action.php', {
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
        }
    </script>
</body>
</html>