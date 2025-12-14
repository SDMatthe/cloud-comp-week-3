<?php
// User Registration Handler - XAMPP MySQL Version
$host = "localhost";
$db = "shopsphere";  // Change this to your database name
$user = "root";      // Default XAMPP MySQL user
$pass = "";          // Default XAMPP MySQL password (empty)

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = $_POST['name'] ?? '';
	$email = $_POST['email'] ?? '';
	$password = $_POST['password'] ?? '';

	// Basic validation
	if (!empty($name) && !empty($email) && !empty($password)) {
		// Connect to database using PDO
		try {
			$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Check if user exists
			$checkStmt = $pdo->prepare("SELECT id FROM shopusers WHERE email = ?");
			$checkStmt->execute([$email]);
			
			if ($checkStmt->fetch()) {
				// User already exists
				header("Location:  register.php?error=" . urlencode("Email already registered"));
				exit();
			}

			// Hash password
			$hashed_password = password_hash($password, PASSWORD_BCRYPT);

			// Insert new user into database
			$sql = "INSERT INTO shopusers (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$name, $email, $hashed_password]);

			// Redirect to success page
			header("Location: success.php");
			exit();

		} catch (PDOException $e) {
			// Database error
			$error_message = "Registration failed: " . htmlspecialchars($e->getMessage());
			header("Location: register.php?error=" . urlencode($error_message));
			exit();
		}
	} else {
		header("Location: register.php?error=" . urlencode("Please fill all fields"));
		exit();
	}
} else {
	// If someone tries to access this page directly
	header("Location: register.php");
	exit();
}
?>