<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization - ShopSphere</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
/**
 * Database Initialization Script
 * Inserts sample products into existing database
 * 
 * Your database already exists with the following tables:
 * - shopusers, products, cart_items, wishlist
 * - orders, order_items, payments, user_payment_methods
 * - gift_cards, refunds (additional features)
 * 
 * This script only populates sample data.
 */

require_once 'config.php';

try {
    $db = getDBConnection();
    
    if (!$db) {
        die('<div class="init-container"><div class="status-message status-error"><span class="emoji">❌</span>Database connection failed</div></div></body></html>');
    }
    
    // Check if products already exist
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $productCount = $result['count'];
    
    if ($productCount > 0) {
        echo <<<HTML
    <div class="init-container">
        <div class="init-header">
            <h1><span class="emoji">✅</span>Database Ready</h1>
            <p>Sample products already loaded</p>
        </div>
        
        <div class="status-message status-info">
            <span class="emoji">📝</span>
            The database already has <strong>$productCount products</strong> loaded and ready to use.
        </div>
        
        <div class="summary-stats">
            <div class="stat-card">
                <h3>$productCount</h3>
                <p>Products Available</p>
            </div>
        </div>
        
        <div class="action-links">
            <a href="products.php" class="btn btn-primary">🛍️ View Products</a>
            <a href="register.php" class="btn btn-success">📝 Register Account</a>
            <a href="index.php" class="btn btn-secondary">🏠 Go to Home</a>
        </div>
    </div>
HTML;
        exit(0);
    }
    
    // Sample products
    $products = [
        ['Laptop Pro', 'High-performance laptop with 16GB RAM and 512GB SSD', 999.99, 5, 'Electronics'],
        ['Smartphone X', 'Latest flagship smartphone with advanced camera system', 599.99, 10, 'Electronics'],
        ['Wireless Headphones', 'Premium noise-cancelling wireless headphones', 149.99, 20, 'Electronics'],
        ['Tablet Plus', 'Lightweight tablet perfect for work and entertainment', 399.99, 8, 'Electronics'],
        ['Smart Watch', 'Fitness tracking and notifications on your wrist', 299.99, 15, 'Electronics'],
        ['Action Camera', '4K action camera for adventure and sports', 799.99, 3, 'Electronics'],
        ['USB-C Hub', 'Multi-port USB-C hub with HDMI and charging', 49.99, 30, 'Accessories'],
        ['Phone Case', 'Durable protective phone case', 19.99, 50, 'Accessories'],
    ];
    
    // Insert products
    $stmt = $db->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    
    // Build product rows for table
    $productRows = '';
    foreach ($products as $product) {
        $name = htmlspecialchars(substr($product[0], 0, 35));
        $price = number_format($product[2], 2);
        $stock = $product[3];
        $productRows .= "<tr><td>$name</td><td>$price</td><td>$stock</td></tr>";
    }
    
    echo <<<HTML
    <div class="init-container">
        <div class="init-header">
            <h1><span class="emoji">✅</span>Database Initialized</h1>
            <p>Sample products have been successfully loaded</p>
        </div>
        
        <div class="status-message status-success">
            <span class="emoji">✨</span>
            <strong>Success!</strong> {$count($products)} sample products have been inserted into the database.
        </div>
        
        <div class="summary-stats">
            <div class="stat-card">
                <h3>{$count($products)}</h3>
                <p>Products Loaded</p>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <h2 style="color: #2c3e50; margin-bottom: 15px;"><span class="emoji">📦</span>Sample Products</h2>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                $productRows
            </tbody>
        </table>
        
        <div class="divider"></div>
        
        <h2 style="color: #2c3e50; margin-bottom: 15px;"><span class="emoji">🚀</span>Next Steps</h2>
        
        <div class="status-message status-info">
            <strong>1. Register an account</strong> - Create your user account<br>
            <strong>2. Browse products</strong> - View the product catalog<br>
            <strong>3. Add to cart</strong> - Start shopping<br>
            <strong>4. Checkout</strong> - Complete your purchase
        </div>
        
        <div class="status-message status-warning" style="margin-top: 20px;">
            <strong><span class="emoji">💳</span>Test Payment Information</strong><br>
            Card Number: <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">4532 1111 1111 1111</code><br>
            Expiry: Any future date (MM/YY)<br>
            CVV: Any 3 digits
        </div>
        
        <div class="action-links">
            <a href="register.php" class="btn btn-success">📝 Register Account</a>
            <a href="products.php" class="btn btn-primary">🛍️ View Products</a>
            <a href="login.php" class="btn btn-secondary">🔐 Login</a>
            <a href="index.php" class="btn btn-secondary">🏠 Go to Home</a>
        </div>
    </div>
HTML;

} catch (PDOException $e) {
    $error = htmlspecialchars($e->getMessage());
    echo <<<HTML
    <div class="init-container">
        <div class="status-message status-error">
            <span class="emoji">❌</span>
            <strong>Database Error:</strong> $error
        </div>
        <div class="action-links">
            <a href="index.php" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
HTML;
    exit(1);
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
    echo <<<HTML
    <div class="init-container">
        <div class="status-message status-error">
            <span class="emoji">❌</span>
            <strong>Error:</strong> $error
        </div>
        <div class="action-links">
            <a href="index.php" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
HTML;
    exit(1);
}
?>
</body>
</html>
