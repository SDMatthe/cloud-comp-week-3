<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ShopShpere - Welcome</title>
	<link rel="stylesheet" href="styles.css">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			line-height: 1.6;
			color: #333;
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
			display: flex;
			align-items: center;
		}

		.navbar-logo:hover {
			color: #3498db;
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

		.nav-link:hover {
			background-color: #34495e;
			color: #3498db;
		}

		.nav-link.active {
			background-color: #3498db;
			color: white;
		}

		/* Auth Links */
		.nav-auth {
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.btn-login {
			color: white;
			text-decoration: none;
			padding: 10px 20px;
			border: 2px solid white;
			border-radius: 4px;
			transition: all 0.3s ease;
		}

		.btn-login:hover {
			background-color: white;
			color: #2c3e50;
		}

		.btn-signup {
			background-color: #27ae60;
			color: white;
			text-decoration: none;
			padding: 10px 20px;
			border-radius: 4px;
			transition: background-color 0.3s ease;
		}

		.btn-signup:hover {
			background-color: #229954;
		}

		.btn-logout {
			background-color: #e74c3c;
			color: white;
			text-decoration: none;
			padding: 10px 20px;
			border-radius: 4px;
			transition: background-color 0.3s ease;
		}

		.btn-logout:hover {
			background-color: #c0392b;
		}

		/* Header Styles */
		.header {
			background-color: #ecf0f1;
			padding: 50px 0;
			text-align: center;
			margin-bottom: 30px;
		}

		.header h1 {
			font-size: 2.5em;
			color: #2c3e50;
			margin-bottom: 10px;
		}

		.header p {
			font-size: 1.1em;
			color: #7f8c8d;
		}

		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 0 20px;
		}

		.welcome-text {
			text-align: center;
			margin-bottom: 30px;
			font-size: 1.2em;
			color: #555;
		}

		.welcome-text h2 {
			color: #2c3e50;
			margin-bottom: 15px;
		}

		.cta-buttons {
			display: flex;
			justify-content: center;
			gap: 15px;
			margin-top: 20px;
			flex-wrap: wrap;
		}

		.btn-primary {
			display: inline-block;
			background-color: #007bff;
			color: white;
			padding: 12px 30px;
			text-decoration: none;
			border-radius: 4px;
			font-size: 16px;
			transition: background-color 0.3s ease;
		}

		.btn-primary:hover {
			background-color: #0056b3;
		}

		.btn-secondary {
			display: inline-block;
			background-color: #6c757d;
			color: white;
			padding: 12px 30px;
			text-decoration: none;
			border-radius: 4px;
			font-size: 16px;
			transition: background-color 0.3s ease;
		}

		.btn-secondary:hover {
			background-color: #5a6268;
		}

		/* Features Section */
		.features {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 30px;
			margin: 50px 0;
		}

		.feature-card {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			text-align: center;
		}

		.feature-card h3 {
			color: #2c3e50;
			margin-bottom: 10px;
		}

		.feature-card p {
			color: #7f8c8d;
		}

		.user-info {
			color: white;
			display: flex;
			align-items: center;
			gap: 15px;
			padding: 0 20px;
		}

		.user-icon {
			width: 40px;
			height: 40px;
			background-color: #3498db;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
		}

		/* Responsive Design */
		@media screen and (max-width: 768px) {
			.hamburger {
				display: flex;
			}

			.nav-menu {
				position: absolute;
				left: -100%;
				top: 70px;
				flex-direction: column;
				background-color: #2c3e50;
				width: 100%;
				text-align: center;
				transition: 0.3s;
				gap: 0;
			}

			.nav-menu.active {
				left: 0;
			}

			.nav-item {
				height: auto;
			}

			.nav-link {
				padding: 20px;
				height: auto;
				width: 100%;
			}

			.nav-auth {
				flex-direction: column;
				width: 100%;
				padding: 20px;
				gap: 10px;
			}

			.btn-login,
			.btn-signup {
				width: 100%;
				text-align: center;
			}

			.header h1 {
				font-size: 1.8em;
			}

			.features {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>
<body>
	<!-- Navigation Menu -->
	<nav class="navbar">
		<div class="navbar-container">
			<a href="index.php" class="navbar-logo">🛍️ ShopShpere</a>

			<ul class="nav-menu">
				<li class="nav-item">
					<a href="index.php" class="nav-link active">Home</a>
				</li>
				<li class="nav-item">
					<a href="productcontroller.php" class="nav-link">Shop</a>
				</li>
				<li class="nav-item">
					<a href="cartcontroller.php" class="nav-link">🛒 Cart</a>
				</li>
				<li class="nav-item">
					<a href="WishlistController.php" class="nav-link">❤️ Wishlist</a>
				</li>
				<li class="nav-item">
					<a href="OrderTrackingController.php" class="nav-link">📦 Orders</a>
				</li>
			</ul>

			<!-- Authentication Links -->
			<div class="nav-auth">
				<?php
					session_start();
					if (isset($_SESSION['user_id'])) {
						// User is logged in
						echo '<div class="user-info">';
						echo '<span>' . htmlspecialchars($_SESSION['user_name'] ?? 'User') . '</span>';
						echo '<div class="user-icon">' . substr(htmlspecialchars($_SESSION['user_name'] ?? 'U'), 0, 1) . '</div>';
						echo '</div>';
						echo '<a href="logout.php" class="btn-logout">Logout</a>';
					} else {
						// User is not logged in
						echo '<a href="login.php" class="btn-login">Login</a>';
						echo '<a href="register.php" class="btn-signup">Sign Up</a>';
					}
				?>
			</div>

			<!-- Hamburger Menu for Mobile -->
			<div class="hamburger">
				<span></span>
				<span></span>
				<span></span>
			</div>
		</div>
	</nav>

	<!-- Header Section -->
	<div class="header">
		<div class="container">
			<h1>Welcome to ShopShpere</h1>
			<p>Your one-stop destination for amazing products</p>
		</div>
	</div>

	<!-- Main Content -->
	<div class="container">
		<div class="welcome-text">
			<h2>Shop Smart, Live Better</h2>
			<p>Discover a wide variety of quality products at unbeatable prices. Join thousands of satisfied customers today!</p>

			<div class="cta-buttons">
				<?php
					if (!isset($_SESSION['user_id'])) {
						echo '<a href="productcontroller.php" class="btn-primary">Browse Products</a>';
						echo '<a href="register.php" class="btn-secondary">Create Your Account</a>';
					} else {
						echo '<a href="productcontroller.php" class="btn-primary">Continue Shopping</a>';
						echo '<a href="cartcontroller.php" class="btn-secondary">View Your Cart</a>';
					}
				?>
			</div>
		</div>
	</div>
</body>
</html>