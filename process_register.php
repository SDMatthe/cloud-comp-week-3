<?php
<<<<<<< HEAD
// User Registration Handler - MySQL Version
require_once 'config.php';
=======
// User Registration Handler - XAMPP MySQL Version
$host = "localhost";
$db = "shopsphere";  // Change this to your database name
$user = "root";      // Default XAMPP MySQL user
$pass = "";          // Default XAMPP MySQL password (empty)
>>>>>>> 78f724664e60dff9e23efa2fd7f713212c4f3694

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	// Basic validation
<<<<<<< HEAD
	if (empty($name) || empty($email) || empty($password)) {
		header("Location: register.php?error=" . urlencode("Please fill all fields"));
		exit();
	}
	
	// Email validation
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		header("Location: register.php?error=" . urlencode("Invalid email format"));
		exit();
	}
	
	// Password validation
	if (strlen($password) < PASSWORD_MIN_LENGTH) {
		header("Location: register.php?error=" . urlencode("Password must be at least " . PASSWORD_MIN_LENGTH . " characters"));
		exit();
	}
	
	// Name validation
	if (strlen($name) < 2 || strlen($name) > 100) {
		header("Location: register.php?error=" . urlencode("Name must be between 2 and 100 characters"));
		exit();
	}

	try {
		// Connect to database using PDO
		$pdo = getDBConnection();
		
		if (!$pdo) {
			header("Location: register.php?error=" . urlencode("Database connection failed"));
=======
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
>>>>>>> 78f724664e60dff9e23efa2fd7f713212c4f3694
			exit();
		}

		// Check if user exists
		$checkStmt = $pdo->prepare("SELECT id FROM shopusers WHERE email = ?");
		$checkStmt->execute([$email]);
		
		if ($checkStmt->fetch()) {
			// User already exists
			header("Location: register.php?error=" . urlencode("Email already registered"));
			exit();
		}

		// Hash password
		$hashed_password = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);

		// Insert new user into database
		$sql = "INSERT INTO shopusers (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$name, $email, $hashed_password]);

		// Redirect to success page
		header("Location: success.php");
		exit();

	} catch (PDOException $e) {
		// Database error - log it but don't expose details
		error_log('Registration error: ' . $e->getMessage());
		header("Location: register.php?error=" . urlencode("Registration failed. Please try again."));
		exit();
	}
} else {
	// If someone tries to access this page directly
	header("Location: register.php");
	exit();
}
?>