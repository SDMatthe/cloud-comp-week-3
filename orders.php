<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - MyShop</title>
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

        .login-prompt {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
        }

        .login-prompt p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .btn-login {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-login:hover {
            background-color: #2980b9;
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
                    <a href="products.php" class="nav-link">Shop</a>
                </li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">🛒 Cart</a>
                </li>
                <li class="nav-item">
                    <a href="wishlist.php" class="nav-link">❤️ Wishlist</a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link active">📦 Orders</a>
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
        <h1 class="page-title">My Orders</h1>

        <?php
            if (!isset($_SESSION['user_id'])) {
                echo '<div class="login-prompt">';
                echo '<p>📦 Please log in to view your orders</p>';
                echo '<a href="login.php" class="btn-login">Log In Now</a>';
                echo '</div>';
            } else {
                echo '<div class="login-prompt">';
                echo '<p>You have no orders yet</p>';
                echo '<p><a href="products.php" style="color: #3498db;">Start shopping today</a></p>';
                echo '</div>';
            }
        ?>
    </div>
</body>
</html>