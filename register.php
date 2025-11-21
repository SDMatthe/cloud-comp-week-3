<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register - ShopSphere</title>
	<link rel="stylesheet" href="styles.css">
	<style>
		body {
			background-color: #f4f4f4;
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			padding: 20px;
		}

		.header {
			background-color: #2c3e50;
			color: white;
			padding: 20px 0;
			text-align: center;
			position: fixed;
			top: 0;
			width: 100%;
			z-index: 999;
		}

		.register-form {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 0 10px rgba(0,0,0,0.1);
			width: 100%;
			max-width: 400px;
			margin-top: 100px;
		}

		.error {
			color: red;
			padding: 10px;
			margin: 10px 0;
			background-color: #f8d7da;
			border: 1px solid #f5c6cb;
			border-radius: 4px;
			text-align: center;
		}

		.register-form input[type="submit"] {
			background-color: #007bff;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			width: 100%;
			font-size: 16px;
			transition: background-color 0.3s;
		}

		.register-form input[type="submit"]:hover {
			background-color: #0056b3;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>ShopSphere</h1>
		<p>Create Your Account</p>
	</div>

	<div class="register-form">
		<h2>Register</h2>

		<!-- Display error message if present -->
		<?php if (isset($_GET['error'])): ?>
			<div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
		<?php endif; ?>

		<!-- Form submits to process_register.php -->
		<form method="post" action="process_register.php">
			<div class="form-group">
				<label>Full Name *</label>
				<input type="text" name="name" placeholder="Enter your full name" required>
			</div>

			<div class="form-group">
				<label>Email *</label>
				<input type="email" name="email" placeholder="Enter your email" required>
			</div>

			<div class="form-group">
				<label>Password *</label>
				<input type="password" name="password" placeholder="Enter your password" required>
			</div>

			<div class="form-group">
				<input type="submit" class="btn" value="Register">
			</div>
		</form>

		<p style="text-align:center;"><a href="index.php">Back to Home</a></p>
	</div>
</body>
</html>
