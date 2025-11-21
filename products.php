<?php
session_start();
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

        <div class="products-grid">
            <!-- Sample Products - Replace with database query -->
            <?php
                // For now, displaying sample products
                // In production, you would query the database using ProductController
                
                $sampleProducts = [
                    ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'stock' => 5, 'emoji' => '💻'],
                    ['id' => 2, 'name' => 'Smartphone', 'price' => 599.99, 'stock' => 10, 'emoji' => '📱'],
                    ['id' => 3, 'name' => 'Headphones', 'price' => 149.99, 'stock' => 20, 'emoji' => '🎧'],
                    ['id' => 4, 'name' => 'Tablet', 'price' => 399.99, 'stock' => 8, 'emoji' => '📱'],
                    ['id' => 5, 'name' => 'Smart Watch', 'price' => 299.99, 'stock' => 15, 'emoji' => '⌚'],
                    ['id' => 6, 'name' => 'Camera', 'price' => 799.99, 'stock' => 3, 'emoji' => '📷'],
                ];

                if (count($sampleProducts) > 0) {
                    foreach ($sampleProducts as $product) {
                        echo '<div class="product-card">';
                        echo '  <div class="product-image">' . $product['emoji'] . '</div>';
                        echo '  <div class="product-info">';
                        echo '    <div class="product-name">' . htmlspecialchars($product['name']) . '</div>';
                        echo '    <div class="product-price">$' . number_format($product['price'], 2) . '</div>';
                        echo '    <div class="product-stock">Stock: ' . $product['stock'] . ' available</div>';
                        echo '    <button class="btn-add-cart" onclick="addToCart(' . $product['id'] . ')">Add to Cart</button>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products">No products available at the moment.</div>';
                }
            ?>
        </div>
    </div>

    <script>
        function addToCart(productId) {
            alert('Product ' + productId + ' added to cart!');
            // This would call your cart controller
        }
    </script>
</body>
</html>