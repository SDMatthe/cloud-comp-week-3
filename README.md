# ShopSphere - E-Commerce Application

A fully functional e-commerce platform built with PHP and MySQL that supports product catalog management, shopping cart, wishlist, order tracking, and virtual payment processing.

## Features Implemented

### 1. **Product Catalog Management** ✅
- Real-time product listing from database
- Product categories and descriptions
- Stock management and availability display
- Redis caching for optimized performance
- ProductController with pagination support

### 2. **Shopping Cart and Checkout** ✅
- Add/remove items from cart
- Real-time cart updates
- Checkout process with shipping information
- Complete order creation workflow
- Cart items stored in database

### 3. **User Authentication** ✅
- User registration with email validation
- Secure login/logout functionality
- Password hashing with bcrypt
- Session management
- CSRF token protection
- Protected pages requiring authentication

### 4. **Wishlist and Favourites** ✅
- Add/remove products to/from wishlist
- View all wishlist items
- AJAX-based wishlist operations
- Quick add to cart from wishlist
- Wishlist persistence in database

### 5. **Order Tracking** ✅
- View order history with status
- Detailed order information display
- Order item breakdown
- Shipping address tracking
- Order status timeline and history
- Order status logging

### 6. **Virtual Payments Management** ✅
- Multiple payment method support (Credit Card, Virtual Wallet, Bank Transfer)
- Credit card validation using Luhn algorithm
- Payment processing workflow
- Transaction ID generation
- Payment status tracking
- Simulated payment processing

### 7. **Database Architecture** ✅
- Relational database design with proper foreign keys
- Tables: shopusers, products, cart_items, orders, order_items, wishlist, payments, user_payment_methods, order_status_log
- Optimized indexes for performance
- Transaction support for atomic operations

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP or similar PHP environment
- Composer (optional, for dependencies)

### Step 1: Database Setup

1. Open MySQL and create a database:
   ```sql
   CREATE DATABASE `cloudcomp-db`;
   ```

2. Initialize the database schema by visiting:
   ```
   http://localhost/dashboard/test/init_db.php
   ```
   
   This will automatically create all necessary tables and insert sample products.

### Step 2: Configuration

The application uses `config.php` for configuration. Default settings:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cloudcomp-db');
```

Update these values if your database credentials differ.

### Step 3: File Structure

```
test/
├── index.php                 # Home page
├── config.php               # Database and app configuration
├── init_db.php              # Database initialization script
│
├── Authentication
├── login.php                # Login page
├── register.php             # Registration form
├── process_register.php     # Registration handler
├── logout.php               # Logout handler
│
├── Products
├── products.php             # Product listing page
├── productcontroller.php    # Product management logic
│
├── Shopping Cart
├── cart.php                 # Shopping cart page
├── add-to-cart.php          # Add to cart handler
├── cart-action.php          # Cart operations handler
├── cartcontroller.php       # Cart management logic
│
├── Wishlist
├── wishlist.php             # Wishlist page
├── wishlist-action.php      # Wishlist operations handler
├── WishlistController.php   # Wishlist management logic
│
├── Orders & Checkout
├── checkout.php             # Checkout page with payment form
├── orders.php               # Order history page
├── order-details.php        # Order details and tracking
├── OrderTrackingController.php  # Order tracking logic
├── PaymentController.php    # Payment processing logic
│
├── Pages
├── success.php              # Success page (registration & orders)
│
└── Styling
└── styles.css               # Main stylesheet
```

## Usage

### For Users

1. **Registration & Login**
   - Visit the home page and click "Sign Up"
   - Create account with name, email, and password
   - Login with credentials

2. **Shopping**
   - Browse products on the Shop page
   - Add products to cart or wishlist
   - View and manage your wishlist
   - Proceed to checkout from cart

3. **Checkout & Payment**
   - Fill shipping address information
   - Select payment method
   - Enter payment details (test cards accepted)
   - Complete order

4. **Order Tracking**
   - View all your orders
   - Click on order to see detailed information
   - Track order status and delivery progress

### Test Data

**Sample Products** (automatically added):
- Laptop Pro - $999.99
- Smartphone X - $599.99
- Wireless Headphones - $149.99
- Tablet Plus - $399.99
- Smart Watch - $299.99
- Action Camera - $799.99
- USB-C Hub - $49.99
- Phone Case - $19.99
- Screen Protector - $9.99
- Charging Cable - $14.99

**Test Payment Cards** (for development):
- Card Number: 4532 1111 1111 1111
- Expiry: Any future date (MM/YY)
- CVV: Any 3 digits

## Key Features

### Product Management
- **Controllers**: `ProductController` handles CRUD operations
- **Caching**: Redis caching (optional) for product data
- **Pagination**: Support for paginated product listings
- **Search**: Filter by category and availability

### Cart Operations
- **AJAX Operations**: Add/remove items without page reload
- **Real-time Totals**: Automatic calculation of totals
- **Stock Validation**: Prevents overselling
- **Session Management**: Cart data persists in database

### Order Processing
- **Atomic Transactions**: Database transactions ensure data consistency
- **Status Tracking**: Complete order lifecycle tracking
- **Order Items**: Detailed breakdown of products in each order
- **Status Logging**: Audit trail of order status changes

### Payment System
- **Multiple Methods**: Credit Card, Virtual Wallet, Bank Transfer
- **Validation**: Luhn algorithm for card validation
- **Security**: No sensitive data storage (simulated payment)
- **Transaction IDs**: Unique identifiers for each payment

## Security Features

1. **Password Security**
   - Bcrypt hashing with cost factor 10
   - Minimum 8 character requirement
   - Secure password storage

2. **Input Validation**
   - Email format validation
   - CSRF token protection
   - Prepared statements for SQL injection prevention
   - Input sanitization and escaping

3. **Session Management**
   - Session regeneration after login
   - Automatic session timeout (1 hour default)
   - Secure session storage

4. **Data Protection**
   - Foreign key constraints
   - Data type validation
   - Transaction support for data integrity

## Database Schema

### shopusers
- Stores user account information
- Password hashing for security

### products
- Product catalog
- Stock management
- Category organization

### cart_items
- Shopping cart items per user
- Unique constraint on user-product combination

### orders
- Order records with total amount
- Payment and shipping information
- Order status tracking

### order_items
- Line items for each order
- Product and pricing snapshot

### wishlist
- User's saved products
- Unique constraint on user-product combination

### payments
- Payment transaction records
- Transaction ID tracking
- Payment status

### order_status_log
- Audit trail of order status changes
- Notes and timestamps

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure database exists: `cloudcomp-db`

### Products Not Showing
- Visit `init_db.php` to initialize database
- Check database tables exist
- Verify sample data was inserted

### Payment Validation Error
- Use test card number: 4532 1111 1111 1111
- Ensure expiry date is in future (MM/YY format)
- CVV must be 3 digits

### Session Issues
- Clear browser cookies if session errors occur
- Check session_start() is called in required files
- Verify PHP session.save_path is writable

## API Reference

### ProductController
```php
getProducts($page, $limit)     // Get paginated products
getProduct($id)                 // Get single product details
addProduct($data)               // Admin: Add new product
editProduct($id, $data)         // Admin: Edit product
deleteProduct($id)              // Admin: Delete product
```

### CartController
```php
getCart()                        // Get user's cart
addItem($productId, $quantity)  // Add item to cart
removeItem($productId)          // Remove item from cart
checkout($paymentMethod, $address) // Process checkout
```

### WishlistController
```php
getWishlist()                   // Get user's wishlist
addToWishlist($productId)       // Add to wishlist
removeFromWishlist($productId)  // Remove from wishlist
isInWishlist($productId)        // Check if in wishlist
getWishlistCount()              // Get wishlist item count
```

### OrderTrackingController
```php
getOrder($orderId, $userId)     // Get order details
getUserOrders($userId, $page)   // Get user's orders
updateOrderStatus($orderId, $status) // Update order status
getTrackingHistory($orderId)    // Get order status history
getOrderCountByStatus($userId)  // Get orders grouped by status
```

### PaymentController
```php
processPayment($orderId, $userId, $method, $details) // Process payment
addPaymentMethod($userId, $method, $details)         // Save payment method
getPaymentMethods($userId)      // Get user's payment methods
refundPayment($paymentId)       // Refund a payment
```

## Performance Optimization

1. **Caching**: Redis integration for product caching (30-60 minute TTL)
2. **Database Indexing**: Indexes on frequently queried columns
3. **Pagination**: Product listings paginated (20 items per page default)
4. **Query Optimization**: Prepared statements and efficient JOINs
5. **Session Caching**: Optional Redis session storage

## Future Enhancements

- [ ] Admin dashboard for product management
- [ ] Real payment gateway integration (Stripe, PayPal)
- [ ] Email notifications for orders
- [ ] Two-factor authentication
- [ ] Product reviews and ratings
- [ ] Advanced search and filtering
- [ ] Promotional codes and coupons
- [ ] Inventory management system
- [ ] Customer support chat
- [ ] Analytics dashboard

## Support

For issues or questions:
1. Check the Troubleshooting section
2. Review database schema in `database_setup.sql`
3. Check error logs in `/logs` or system error logs
4. Verify all dependencies are properly configured

## License

This project is provided as-is for educational and development purposes.

## Version

**Current Version**: 1.0.0
**Last Updated**: December 2025
