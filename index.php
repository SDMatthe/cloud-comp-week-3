<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ShopShpere - Welcome</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<!-- Navigation Menu -->
	<nav class="navbar">
		<div class="navbar-container">
			<a href="index.php" class="navbar-logo">ShopSphere</a>

			<ul class="nav-menu">
				<li class="nav-item">
					<a href="index.php" class="nav-link active">Home</a>
				</li>
				<li class="nav-item">
					<a href="products.php" class="nav-link">Shop</a>
				</li>
				<li class="nav-item">
					<a href="cart.php" class="nav-link">Basket</a>
				</li>
				<li class="nav-item">
					<a href="wishlist.php" class="nav-link">Wishlist</a>
				</li>
				<li class="nav-item">
					<a href="orders.php" class="nav-link">Orders</a>
				</li>
			</ul>

			<!-- Authentication Links -->
			<div class="nav-auth">
                <?php
                    if (isset($_SESSION['user_id'])) {
                        // User is logged in
                        $user_initial = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1));
                        ?>
                        <div class="user-dropdown">
                            <div class="user-info">
                                <div class="user-avatar"><?php echo htmlspecialchars($user_initial); ?></div>
                                <div class="user-name">
                                    <span class="user-name-text"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                                    <span class="user-email-text"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        // User is not logged in
                        ?>
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-signup">Sign Up</a>
                        <?php
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