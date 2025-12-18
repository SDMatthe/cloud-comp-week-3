# 🛍️ ShopSphere - Getting Started Guide

Welcome to ShopSphere! A fully functional e-commerce platform built with PHP and MySQL.

## 🚀 Quick Start (3 Steps)

### Step 1: Initialize Database
Visit this URL in your browser:
```
http://localhost/dashboard/test/init_db.php
```

**What happens**:
- ✅ Creates database tables
- ✅ Inserts 10 sample products
- ✅ Sets up indexes and constraints

You'll see: **"Database initialized successfully!"**

### Step 2: Create Your Account
Go to: `http://localhost/dashboard/test/register.php`

Fill in:
- **Name**: Your name (e.g., "John Doe")
- **Email**: Your email (e.g., "john@example.com")  
- **Password**: Min 8 characters (e.g., "password123")

Click **Register** → Success page appears

### Step 3: Start Shopping!
Go to: `http://localhost/dashboard/test/products.php`

- Browse 10 sample products
- Click ♥ to save favorites
- Click "Add to Cart" to purchase

## 📋 Main Pages

| Page | URL | Purpose |
|------|-----|---------|
| **Home** | `index.php` | Welcome & overview |
| **Shop** | `products.php` | Browse all products |
| **Cart** | `cart.php` | View shopping cart |
| **Wishlist** | `wishlist.php` | View saved items |
| **Orders** | `orders.php` | Order history |
| **Checkout** | `checkout.php` | Payment page |
| **Login** | `login.php` | Sign in |
| **Register** | `register.php` | Create account |

## 🛒 Complete User Flow

```
1. REGISTER/LOGIN
   └─ register.php or login.php
   
2. BROWSE PRODUCTS
   └─ products.php
   
3. ADD TO CART OR WISHLIST
   └─ Click buttons on product
   
4. VIEW WISHLIST (Optional)
   └─ wishlist.php
   
5. VIEW CART
   └─ cart.php
   
6. CHECKOUT
   └─ checkout.php
   
7. ENTER SHIPPING INFO
   └─ Fill address form
   
8. ENTER PAYMENT INFO
   └─ Fill credit card form
   
9. COMPLETE PURCHASE
   └─ Click "Place Order"
   
10. VIEW ORDER
    └─ orders.php → order-details.php
```

## 💳 Test Payment

When you reach checkout, use these test details:

**Credit Card**:
- Number: `4532 1111 1111 1111`
- Expiry: `12/25` (any future date)
- CVV: `123` (any 3 digits)

✅ This card is valid and will be accepted

## 📦 Sample Products

After setup, you'll have these products available:

1. **Laptop Pro** - $999.99 (5 in stock)
2. **Smartphone X** - $599.99 (10 in stock)
3. **Wireless Headphones** - $149.99 (20 in stock)
4. **Tablet Plus** - $399.99 (8 in stock)
5. **Smart Watch** - $299.99 (15 in stock)
6. **Action Camera** - $799.99 (3 in stock)
7. **USB-C Hub** - $49.99 (30 in stock)
8. **Phone Case** - $19.99 (50 in stock)
9. **Screen Protector** - $9.99 (100 in stock)
10. **Charging Cable** - $14.99 (40 in stock)

## 🎯 Features to Try

### 1. Product Browsing
- See real products from database
- Check stock availability
- View descriptions and prices

### 2. Shopping Cart
- Add items with quantity
- View total price
- Remove items
- Proceed to checkout

### 3. Wishlist
- Click ♥ heart to save
- View saved items
- Add from wishlist to cart
- Remove from wishlist

### 4. Checkout
- Enter shipping address
- Choose payment method
- Enter card details
- Complete order

### 5. Order Tracking
- View order history
- See order details
- Track order status
- View order items

### 6. User Account
- Register with email
- Login/logout
- Update profile info during checkout

## ⚙️ Configuration

**Database** (`config.php`):
```php
Database: cloudcomp-db
Host: localhost
User: root
Password: (empty)
```

If you have different credentials, edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change this
define('DB_PASS', '');          // Change this
define('DB_NAME', 'cloudcomp-db');
```

## 🐛 Troubleshooting

### "Database connection failed"
**Solution**:
1. Make sure MySQL is running
2. Check username/password in `config.php`
3. Verify database `cloudcomp-db` exists

**Quick Fix**:
```sql
CREATE DATABASE `cloudcomp-db`;
```

### "No products showing"
**Solution**:
1. Visit `http://localhost/dashboard/test/init_db.php`
2. It will add sample products
3. Refresh products.php

### "Login doesn't work"
**Solution**:
1. Make sure you registered first
2. Use exact email and password
3. Check PHP session is enabled
4. Clear browser cookies if needed

### "Payment validation error"
**Solution**:
- Use test card: `4532111111111111`
- Expiry must be future date (format: MM/YY)
- CVV must be exactly 3 digits

## 📚 Learn More

For detailed information, see:
- **README.md** - Full documentation
- **SETUP.md** - Technical setup
- **IMPLEMENTATION.md** - Architecture details

## 🎓 What You'll Learn

This application demonstrates:
- ✅ User authentication
- ✅ Database design
- ✅ Shopping cart system
- ✅ Payment processing
- ✅ Order management
- ✅ Inventory tracking
- ✅ Session handling
- ✅ Form validation
- ✅ AJAX updates
- ✅ Security best practices

## 🔒 Security Notes

- Passwords are securely hashed (bcrypt)
- Credit cards are NOT stored (simulated payment)
- SQL injection is prevented (prepared statements)
- Session timeout: 1 hour
- CSRF protection enabled

## 📞 Common Questions

**Q: Is this a real payment system?**
A: No, it's simulated. Cards are validated but no real charges occur.

**Q: Will my data be saved?**
A: Yes! Everything is stored in the MySQL database until you delete it.

**Q: Can I reset the database?**
A: Yes, visit `init_db.php` to reinitialize everything.

**Q: How do I add more products?**
A: You'd need admin functionality (not yet implemented). Currently, use the database directly.

**Q: Can multiple users shop?**
A: Yes! Each user has their own account, cart, wishlist, and orders.

## 🎉 You're Ready!

1. ✅ Initialize database: `init_db.php`
2. ✅ Register account: `register.php`
3. ✅ Start shopping: `products.php`
4. ✅ Check orders: `orders.php`

**Enjoy your virtual shopping experience!** 🛍️

---

**Questions?** Check README.md or SETUP.md for more details.

*ShopSphere v1.0 - December 2025*
