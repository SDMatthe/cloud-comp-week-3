<?php
/**
 * Unified Authentication Handler
 * Handles both login and logout in a single file
 * 
 * GET Parameters:
 *  - action=login  (default) - Display login form or process login
 *  - action=logout - Logout user
 * 
 * POST Parameters:
 *  - email
 *  - password
 */

session_start();

// Database configuration
$serverName = "tcp:matth-cloud-comp-assignment.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "mydatabase",
    "Uid" => "myadmin",
    "PWD" => "C*uldronLake10",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Determine action
$action = $_GET['action'] ?? 'login';

// logout handler
if ($action === 'logout') {
    session_destroy();
    header("Location: index.php?logout=success");
    exit();
}

//login handler
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Connect to database
        $conn = sqlsrv_connect($serverName, $connectionOptions);

        if ($conn) {
            // Query user
            $sql = "SELECT id, name, email, password FROM shopusers WHERE email = ?";
            $params = array($email);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                $error_message = "Database error: " . print_r(sqlsrv_errors(), true);
            } else {
                $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Login successful - create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['login_time'] = time();

                    // Redirect to dashboard or previous page
                    $redirect = $_GET['redirect'] ?? 'products.php';
                    header("Location: " . $redirect);
                    exit();
                } else {
                    $error_message = "Invalid email or password.";
                }

                sqlsrv_free_stmt($stmt);
            }

            sqlsrv_close($conn);
        } else {
            $conn_errors = sqlsrv_errors();
            $error_message = "Database connection failed: ";
            if ($conn_errors != null) {
                foreach ($conn_errors as $error) {
                    $error_message .= $error['message'] . " ";
                }
            }
        }
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && $action === 'login') {
    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.1);
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-container button:hover {
            background-color: #2980b9;
        }

        .login-container button:active {
            transform: scale(0.98);
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Alert Styles */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: 500;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input {
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-me label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: normal;
        }

        @media screen and (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to ShopSphere</h2>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit">Login</button>
        </form>

        <!-- Links -->
        <div class="signup-link">
            Don't have an account? <a href="register.php">Sign Up Here</a>
        </div>

        <div class="back-link">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>