<?php
require_once 'config.php';

// Load ProductController
require_once 'productcontroller.php';

// Session already started in config.php

$products = [];
$error = '';

try {
    $pdo = getDBConnection();
    if ($pdo) {
        // For Redis, we'll just create a mock cache if Redis is not available
        $redis = null;
        try {
            if (class_exists('Redis')) {
                $redis = new Redis();
                $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
                $redis->select(REDIS_DB);
            }
        } catch (Exception $e) {
            // Redis not available, will work without cache
        }

        // Create controller
        $productController = new \App\Controllers\ProductController($pdo, $redis);
        $products = $productController->getProducts(1, 20);
    }
} catch (Exception $e) {
    $error = 'Failed to load products: ' . $e->getMessage();
    error_log('Products page error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
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
                    <a href="products.php" class="nav-link active">Shop</a>
                </li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">Cart</a>
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

    <div class="container">
        <h1 class="page-title">Shop Our Products</h1>

        <?php if ($error): ?>
            <div class="error-message" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="products-grid">
            <?php
                if (count($products) > 0) {
                    foreach ($products as $product) {
                        $inWishlist = false;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $pdo = getDBConnection();
                                $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                                $stmt->execute([$_SESSION['user_id'], $product['id']]);
                                $inWishlist = $stmt->fetch() !== false;
                            } catch (Exception $e) {
                                // Continue without wishlist check
                            }
                        }
                        
                        echo '<div class="product-card">';
                        
                        // Get appropriate emoji based on product name
                        $productEmoji = '📦'; // default
                        $productLower = strtolower($product['name']);
                        
                        if (strpos($productLower, 'laptop') !== false) {
                            $productEmoji = '💻';
                        } elseif (strpos($productLower, 'smartphone') !== false || strpos($productLower, 'phone') !== false) {
                            $productEmoji = '📱';
                        } elseif (strpos($productLower, 'headphones') !== false) {
                            $productEmoji = '🎧';
                        } elseif (strpos($productLower, 'tablet') !== false) {
                            $productEmoji = '📱';
                        } elseif (strpos($productLower, 'watch') !== false) {
                            $productEmoji = '⌚';
                        } elseif (strpos($productLower, 'camera') !== false) {
                            $productEmoji = '📷';
                        } elseif (strpos($productLower, 'hub') !== false || strpos($productLower, 'cable') !== false) {
                            $productEmoji = '🔌';
                        } elseif (strpos($productLower, 'case') !== false) {
                            $productEmoji = '🛡️';
                        } elseif (strpos($productLower, 'protector') !== false) {
                            $productEmoji = '🛡️';
                        }
                        
                        echo '  <div class="product-image">' . $productEmoji . '</div>';
                        echo '  <div class="product-info">';
                        echo '    <div class="product-name">' . htmlspecialchars($product['name']) . '</div>';
                        echo '    <div class="product-description" style="font-size: 12px; color: #666; margin: 5px 0;">' . htmlspecialchars(substr($product['description'], 0, 50)) . '...</div>';
                        echo '    <div class="product-price">$' . number_format($product['price'], 2) . '</div>';
                        echo '    <div class="product-stock" style="font-size: 12px; color: ' . ($product['stock'] > 0 ? '#28a745' : '#dc3545') . ';">' . ($product['stock'] > 0 ? 'Stock: ' . $product['stock'] . ' available' : 'Out of Stock') . '</div>';
                        
                        if (isset($_SESSION['user_id'])) {
                            echo '    <div style="display: flex; gap: 5px; margin-top: 10px;">';
                            if ($product['stock'] > 0) {
                                echo '      <button class="btn-add-cart" onclick="addToCart(' . $product['id'] . ', \'' . htmlspecialchars($product['name']) . '\')" style="flex: 1;">Add to Cart</button>';
                            }
                            echo '      <button class="btn-wishlist" onclick="toggleWishlist(' . $product['id'] . ')" style="flex: 0 0 auto; background-color: ' . ($inWishlist ? '#ff6b6b' : '#f0f0f0') . '; color: ' . ($inWishlist ? 'white' : '#333') . ';">♥</button>';
                            echo '    </div>';
                        } else {
                            echo '    <button class="btn-add-cart" onclick="requireLogin()" style="width: 100%; margin-top: 10px;">Add to Cart</button>';
                        }
                        
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products" style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 8px;">No products available at the moment. <a href="init_db.php">Initialize Database</a></div>';
                }
            ?>
        </div>
    </div>

    <script>
        function requireLogin() {
            alert('Please login to add items to your cart');
            window.location.href = 'login.php?redirect=products.php';
        }

        function addToCart(productId, productName) {
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            // Send request
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
                alert('❌ An error occurred while adding to cart');
            });
        }

        function toggleWishlist(productId) {
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
    </script>
</body>
</html>