<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Success - ShopSphere</title>
	<link rel="stylesheet" href="styles.css">
	<style>
		body {
			font-family: Arial, sans-serif;
			background-color: #f4f4f4;
		}
		.success-container {
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			padding: 20px;
		}
		.success-message {
			background: white;
			padding: 40px;
			border-radius: 8px;
			box-shadow: 0 0 10px rgba(0,0,0,0.1);
			max-width: 500px;
			text-align: center;
		}
		.success-icon {
			font-size: 64px;
			color: #28a745;
			margin-bottom: 20px;
		}
		.success-title {
			font-size: 24px;
			color: #2c3e50;
			margin-bottom: 15px;
		}
		.success-text {
			color: #666;
			margin-bottom: 30px;
			line-height: 1.6;
		}
		.order-details {
			background: #f9f9f9;
			padding: 15px;
			border-radius: 4px;
			margin-bottom: 20px;
			text-align: left;
		}
		.detail-row {
			display: flex;
			justify-content: space-between;
			padding: 8px 0;
			border-bottom: 1px solid #ddd;
		}
		.detail-row:last-child {
			border-bottom: none;
		}
		.detail-label {
			font-weight: bold;
			color: #333;
		}
		.detail-value {
			color: #666;
		}
		.btn {
			display: inline-block;
			background-color: #3498db;
			color: white;
			padding: 12px 25px;
			text-decoration: none;
			border-radius: 4px;
			margin: 10px;
			border: none;
			cursor: pointer;
		}
		.btn:hover { background-color: #2980b9; }
		.btn-primary { background-color: #28a745; }
		.btn-primary:hover { background-color: #218838; }
	</style>
</head>
<body>
	<!-- Navigation -->
	<?php session_start(); ?>
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
					} else {
						echo '<a href="login.php" class="btn-login">Login</a>';
						echo '<a href="register.php" class="btn-signup">Sign Up</a>';
					}
				?>
			</div>
		</div>
	</nav>

	<div class="success-container">
		<div class="success-message">
			<div class="success-icon">✅</div>
			
			<?php
				$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
				
				if ($orderId) {
					// Order success page
					echo '<div class="success-title">Order Placed Successfully!</div>';
					echo '<div class="success-text">Your order has been confirmed and is being processed.</div>';
					echo '<div class="order-details">';
					echo '  <div class="detail-row">';
					echo '    <span class="detail-label">Order ID:</span>';
					echo '    <span class="detail-value">#' . htmlspecialchars($orderId) . '</span>';
					echo '  </div>';
					echo '  <div class="detail-row">';
					echo '    <span class="detail-label">Status:</span>';
					echo '    <span class="detail-value">Confirmed</span>';
					echo '  </div>';
					echo '  <div class="detail-row">';
					echo '    <span class="detail-label">Next Step:</span>';
					echo '    <span class="detail-value">Processing</span>';
					echo '  </div>';
					echo '</div>';
					echo '<p class="success-text">You will receive a confirmation email shortly with tracking information.</p>';
					echo '<a href="order-details.php?order_id=' . urlencode($orderId) . '" class="btn btn-primary">View Order Details</a>';
				} else {
					// Registration success page
					echo '<div class="success-title">Registration Successful!</div>';
					echo '<div class="success-text">Your account has been created successfully. You can now login and start shopping!</div>';
					echo '<a href="login.php" class="btn btn-primary">Login Now</a>';
				}
			?>
			
			<a href="index.php" class="btn">Back to Home</a>
		</div>
	</div>
</body>
</html>
		.btn-success:hover { background-color: #218838; }
	</style>
</head>
<body>
	<div class="header">
		<h1>ShopSphere</h1>
		<p>Registration Successful</p>
	</div>

	<div class="success-message">
		<div class="success-icon">✓</div>
		<h2>Registration Successful!</h2>
		<p>Your account has been created successfully. Welcome to ShopSphere!</p>

		<div style="margin-top:30px;">
			<a href="index.php" class="btn">Go Back to Home</a>
			<a href="register.php" class="btn btn-success">Register Another User</a>
		</div>
	</div>
</body>
</html>