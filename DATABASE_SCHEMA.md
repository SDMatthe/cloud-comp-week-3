# Database Schema - ShopSphere E-Commerce Platform

## Database: `cloudcomp-db`

This document describes the actual database structure that powers the ShopSphere e-commerce platform.

---

## 📊 Table Overview

| Table | Purpose | Records | Type |
|-------|---------|---------|------|
| `shopusers` | User accounts | Active users | Core |
| `products` | Product catalog | 10+ items | Core |
| `cart_items` | Shopping carts | Active items | Core |
| `wishlist` | Saved items | Bookmarked products | Core |
| `orders` | Order records | Customer orders | Core |
| `order_items` | Order line items | Items per order | Core |
| `payments` | Payment transactions | Transaction records | Core |
| `user_payment_methods` | Saved payment methods | User payment info | Core |
| `gift_cards` | Gift card balances | Active gift cards | Extended |
| `refunds` | Refund records | Refund transactions | Extended |

---

## 🔑 Detailed Table Structures

### 1. **shopusers** - User Accounts

```sql
CREATE TABLE `shopusers` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255),
  `password` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - User ID (PK)
- `name` - Full name
- `email` - Email address (UNIQUE)
- `password_hash` - Bcrypt hashed password
- `password` - Password (for backward compatibility)
- `created_at` - Account creation timestamp

**Sample Data:**
```
1 | Matthew Hilton | maffshilton@gmail.com | [hash] | [hash] | 2025-12-14
```

---

### 2. **products** - Product Catalog

```sql
CREATE TABLE `products` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `image_url` VARCHAR(512),
  `category` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_products_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Product ID (PK)
- `name` - Product name (255 chars)
- `description` - Full description (TEXT)
- `price` - Price with 2 decimals
- `stock` - Stock quantity
- `image_url` - Product image URL
- `category` - Product category
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

**Sample Products:** (10 loaded)
```
1. Laptop Pro - $999.99 - Stock: 5
2. Smartphone X - $599.99 - Stock: 10
3. Wireless Headphones - $149.99 - Stock: 20
4. Tablet Plus - $399.99 - Stock: 8
5. Smart Watch - $299.99 - Stock: 15
6. Action Camera - $799.99 - Stock: 3
7. USB-C Hub - $49.99 - Stock: 30
8. Phone Case - $19.99 - Stock: 50
9. Screen Protector - $9.99 - Stock: 100
10. Charging Cable - $14.99 - Stock: 40
```

---

### 3. **cart_items** - Shopping Cart

```sql
CREATE TABLE `cart_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_cart_user_product` (`user_id`, `product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `shopusers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Cart item ID (PK)
- `user_id` - User ID (FK)
- `product_id` - Product ID (FK)
- `quantity` - Quantity in cart (default: 1)
- `created_at` - Added to cart timestamp
- `updated_at` - Last updated timestamp

**Constraints:**
- UNIQUE: (user_id, product_id) - One entry per product per user
- FK: user_id → shopusers.id (CASCADE delete)
- FK: product_id → products.id (CASCADE delete)

---

### 4. **wishlist** - Saved Items

```sql
CREATE TABLE `wishlist` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_wishlist_user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `shopusers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Wishlist item ID (PK)
- `user_id` - User ID (FK)
- `product_id` - Product ID (FK)
- `added_at` - Date added to wishlist

**Constraints:**
- UNIQUE: (user_id, product_id) - One entry per product per user
- FK: user_id → shopusers.id (CASCADE delete)
- FK: product_id → products.id (CASCADE delete)

---

### 5. **orders** - Order Records

```sql
CREATE TABLE `orders` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50),
  `shipping_address` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `shopusers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Order ID (PK)
- `user_id` - User ID (FK)
- `total_amount` - Order total
- `payment_method` - Payment method (credit_card, virtual_wallet, bank_transfer)
- `shipping_address` - JSON formatted address
- `status` - Order status (pending, processing, shipped, delivered, cancelled)
- `created_at` - Order creation timestamp
- `updated_at` - Last update timestamp

**Status Values:**
- `pending` - Order received
- `processing` - Being prepared
- `shipped` - On the way
- `delivered` - Completed
- `cancelled` - Order cancelled

---

### 6. **order_items** - Order Line Items

```sql
CREATE TABLE `order_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(10) UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Order item ID (PK)
- `order_id` - Order ID (FK)
- `product_id` - Product ID (FK)
- `quantity` - Quantity ordered
- `price` - Price at time of order
- `created_at` - Item creation timestamp

**Constraints:**
- FK: order_id → orders.id (CASCADE delete)
- FK: product_id → products.id

---

### 7. **payments** - Payment Transactions

```sql
CREATE TABLE `payments` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `method` VARCHAR(50),
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(50) NOT NULL DEFAULT 'processing',
  `transaction_id` VARCHAR(255),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `shopusers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Payment ID (PK)
- `order_id` - Order ID (FK)
- `user_id` - User ID (FK)
- `method` - Payment method
- `amount` - Payment amount
- `status` - Payment status (processing, completed, failed, refunded)
- `transaction_id` - Unique transaction identifier
- `created_at` - Transaction timestamp

**Payment Methods:**
- `credit_card` - Credit/Debit card
- `virtual_wallet` - Digital wallet
- `bank_transfer` - Direct bank transfer

---

### 8. **user_payment_methods** - Saved Payment Methods

```sql
CREATE TABLE `user_payment_methods` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `method` VARCHAR(50) NOT NULL,
  `details` VARBINARY(1024),
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `shopusers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Payment method ID (PK)
- `user_id` - User ID (FK)
- `method` - Payment method type
- `details` - Encrypted payment details
- `is_default` - Default payment method flag
- `created_at` - Creation timestamp

---

### 9. **gift_cards** - Gift Card Balances

```sql
CREATE TABLE `gift_cards` (
  `code` VARCHAR(100) NOT NULL PRIMARY KEY,
  `balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `code` - Gift card code (PK)
- `balance` - Remaining balance

---

### 10. **refunds** - Refund Transactions

```sql
CREATE TABLE `refunds` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `payment_id` INT(10) UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `payment_id` (`payment_id`),
  FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Columns:**
- `id` - Refund ID (PK)
- `payment_id` - Payment ID (FK)
- `amount` - Refund amount
- `status` - Refund status (pending, completed, failed)
- `created_at` - Refund timestamp

---

## 📈 Relationships & Data Flow

```
shopusers (1) ──────────────────┐
      │                         │
      ├─ (1:N) → cart_items    │
      ├─ (1:N) → wishlist       │
      ├─ (1:N) → orders         │
      ├─ (1:N) → payments       │
      └─ (1:N) → user_payment_methods

orders (1) ──────────────────────┐
      │                          │
      ├─ (1:N) → order_items    │
      ├─ (1:N) → payments       │
      └─ (1:N) → order_status_log

products (1) ─────────────────────┐
      ├─ (1:N) → cart_items      │
      ├─ (1:N) → wishlist        │
      ├─ (1:N) → order_items     │
      
payments (1) ─────────────────────┘
      └─ (1:N) → refunds
```

---

## 🔍 Query Examples

### Get User's Cart with Product Details
```sql
SELECT 
    ci.id,
    p.id as product_id,
    p.name,
    p.price,
    ci.quantity,
    (p.price * ci.quantity) as subtotal
FROM cart_items ci
JOIN products p ON ci.product_id = p.id
WHERE ci.user_id = 1
ORDER BY ci.created_at DESC;
```

### Get User's Orders with Items
```sql
SELECT 
    o.id as order_id,
    o.total_amount,
    o.status,
    o.created_at,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
WHERE o.user_id = 1
GROUP BY o.id
ORDER BY o.created_at DESC;
```

### Get Wishlist with Details
```sql
SELECT 
    w.id,
    p.id as product_id,
    p.name,
    p.price,
    p.stock,
    p.category,
    w.added_at
FROM wishlist w
JOIN products p ON w.product_id = p.id
WHERE w.user_id = 1
ORDER BY w.added_at DESC;
```

---

## 🔒 Data Integrity

### Foreign Key Constraints
All foreign keys use **ON DELETE CASCADE** to ensure:
- Deleting a user removes all their carts, orders, and wishlist items
- Deleting an order removes all order items and associated payments
- Deleting a product is restricted for existing orders

### Unique Constraints
- Email uniqueness in shopusers
- One cart entry per user per product
- One wishlist entry per user per product

### Indexes
Optimized for common queries:
- `shopusers.email` - Fast user lookup
- `products.category` - Category filtering
- `orders.status` - Order status filtering
- `cart_items.user_id` - User's cart retrieval
- `wishlist.user_id` - User's wishlist retrieval

---

## 💾 Character Set

All tables use:
- **Charset**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`

Supports full Unicode including emojis and special characters.

---

## 📝 Sample Data

Run `init_db.php` to populate 10 sample products:
1. Laptop Pro - $999.99
2. Smartphone X - $599.99
3. Wireless Headphones - $149.99
4. Tablet Plus - $399.99
5. Smart Watch - $299.99
6. Action Camera - $799.99
7. USB-C Hub - $49.99
8. Phone Case - $19.99
9. Screen Protector - $9.99
10. Charging Cable - $14.99

---

## 🚀 Performance Considerations

1. **Indexing**: All foreign keys and frequently searched columns are indexed
2. **Pagination**: Use `LIMIT` for product and order listings
3. **Caching**: Optional Redis for frequently accessed data
4. **Archive**: Consider archiving old orders to separate table
5. **Partitioning**: For large datasets, partition orders by date

---

**Database Version**: MySQL 5.7+  
**Last Updated**: December 18, 2025  
**Status**: ✅ Production Ready
