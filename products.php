<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - MyShop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Navigation Styles */
        .navbar {
            background-color: #2c3e50;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .navbar-logo {
            color: white;
            text-decoration: none;
            font-size: 1.8em;
            font-weight: bold;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 0;
        }

        .nav-item {
            height: 70px;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0 20px;
            height: 70px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: #3498db;
            color: white;
        }

        .nav-auth {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-logout {
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .product-price {
            color: #27ae60;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-stock {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .btn-add-cart {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn-add-cart:hover {
            background-color: #2980b9;
        }

        .no-products {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }

        @media screen and (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">🛍️ MyShop</a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link active">Shop</a>
                </li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">🛒 Cart</a>
                </li>
                <li class="nav-item">
                    <a href="wishlist.php" class="nav-link">❤️ Wishlist</a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link">📦 Orders</a>
                </li>
            </ul>

            <div class="nav-auth">
                <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<span style="color: white;">' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . '</span>';
                        echo '<a href="logout.php" class="btn-logout">Logout</a>';
                    } else {
                        echo '<a href="login.php" style="color: white; text-decoration: none;">Login</a>';
                        echo '<a href="register.php" style="background-color: #27ae60; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Sign Up</a>';
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