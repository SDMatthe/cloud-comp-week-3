# Quick Setup Guide

## 1. Create Database

Open phpMyAdmin or MySQL CLI and run:

```sql
CREATE DATABASE `cloudcomp-db`;
USE `cloudcomp-db`;
```

## 2. Initialize Application

Visit this URL in your browser:
```
http://localhost/dashboard/test/init_db.php
```

This will:
- Create all database tables
- Insert 10 sample products
- Set up indexes and constraints

You should see: "Database initialized successfully!"

## 3. Test the Application

### Create a Test Account
1. Go to: `http://localhost/dashboard/test/register.php`
2. Fill in details:
   - Name: Test User
   - Email: test@example.com
   - Password: test1234 (minimum 8 characters)
3. Click Register

### Browse Products
1. Go to: `http://localhost/dashboard/test/products.php`
2. You should see 10 sample products with prices
3. Click "Add to Cart" to add items

### Try Shopping Cart
1. Go to: `http://localhost/dashboard/test/cart.php`
2. View items you added
3. Click "Proceed to Checkout"

### Complete a Purchase
1. Fill in shipping address
2. Enter test credit card:
   - Number: 4532111111111111
   - Expiry: 12/25 (any future date)
   - CVV: 123
3. Click "Place Order"

### View Orders
1. Go to: `http://localhost/dashboard/test/orders.php`
2. See your order history
3. Click order to see details and tracking

### Try Wishlist
1. Go back to: `http://localhost/dashboard/test/products.php`
2. Click the heart icon (♥) on any product
3. Go to: `http://localhost/dashboard/test/wishlist.php`
4. See saved products

## Database Configuration

Edit `config.php` if needed:

```php
define('DB_HOST', 'localhost');  // Your host
define('DB_USER', 'root');       // Your username
define('DB_PASS', '');           // Your password
define('DB_NAME', 'cloudcomp-db'); // Your database name
```

## Troubleshooting

### "Database connection failed"
- Verify MySQL is running
- Check credentials in config.php
- Ensure cloudcomp-db exists

### "Table doesn't exist"
- Visit `init_db.php` to recreate tables
- Old data will be cleared

### "No products showing"
- Visit `init_db.php` to insert sample products
- Products will be added to database

### Payment validation errors
- Use test card: 4532111111111111
- Expiry must be future date (MM/YY)
- CVV must be 3 digits

## File Structure

```
test/
├── init_db.php          ← Run this first!
├── config.php           ← Database settings
├── products.php         ← Shop page
├── cart.php             ← Shopping cart
├── checkout.php         ← Payment page
├── orders.php           ← Order history
├── order-details.php    ← Order tracking
├── wishlist.php         ← Saved items
├── login.php            ← Login page
├── register.php         ← Sign up
└── README.md            ← Full documentation
```

## Sample Products Available

After initialization, these products are available:

| Product | Price | Stock |
|---------|-------|-------|
| Laptop Pro | $999.99 | 5 |
| Smartphone X | $599.99 | 10 |
| Wireless Headphones | $149.99 | 20 |
| Tablet Plus | $399.99 | 8 |
| Smart Watch | $299.99 | 15 |
| Action Camera | $799.99 | 3 |
| USB-C Hub | $49.99 | 30 |
| Phone Case | $19.99 | 50 |
| Screen Protector | $9.99 | 100 |
| Charging Cable | $14.99 | 40 |

## Key URLs

| Page | URL |
|------|-----|
| Database Init | `/init_db.php` |
| Home | `/index.php` |
| Products | `/products.php` |
| Cart | `/cart.php` |
| Checkout | `/checkout.php` |
| Orders | `/orders.php` |
| Wishlist | `/wishlist.php` |
| Login | `/login.php` |
| Register | `/register.php` |

## Test User Flow

1. **Register** → `register.php`
2. **Login** → `login.php`
3. **Browse** → `products.php`
4. **Add to Cart** → Click "Add to Cart"
5. **View Cart** → `cart.php`
6. **Checkout** → `checkout.php`
7. **View Orders** → `orders.php`
8. **Track Order** → `order-details.php?order_id=1`

## Features Tested

✅ User Registration & Login
✅ Product Listing from Database
✅ Shopping Cart Management
✅ Wishlist Operations
✅ Order Creation & Checkout
✅ Payment Processing
✅ Order Tracking
✅ Session Management
✅ Database Integrity

## Need Help?

Refer to README.md for detailed documentation and troubleshooting.

**Happy Shopping! 🛍️**
