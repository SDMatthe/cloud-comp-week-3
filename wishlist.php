<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - ShopSphere</title>
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
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
        }

        .empty-message {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
        }

        .empty-message p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .btn-shop {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
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
                        echo '<a href="logout.php" style="background-color: #e74c3c; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Logout</a>';
                    } else {
                        echo '<a href="login.php" style="color: white; text-decoration: none;">Login</a>';
                        echo '<a href="register.php" style="background-color: #27ae60; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Sign Up</a>';
                    }
                ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">My Wishlist</h1>

        <div class="empty-message">
            <p>Your wishlist is empty</p>
            <p>Start adding your favorite items to your wishlist!</p>
            <a href="products.php" class="btn-shop">Explore Products</a>
        </div>
    </div>
</body>
</html>