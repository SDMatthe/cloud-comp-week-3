# Checkout & Payment System - How It Works

## 🎯 Overview

Your checkout system is **100% simulated** - it does NOT connect to any real payment processor or bank. All payments are validated locally and recorded in the database for demonstration purposes only.

---

## 🔄 Complete Checkout Flow

### Step 1: User Navigates to Checkout
1. User clicks "Proceed to Checkout" from cart page
2. Session validates user is logged in
3. Cart items are loaded from database
4. User fills in shipping and payment forms

### Step 2: Shipping Information Collection
**Form collects:**
- Full Name
- Email
- Phone Number
- Address
- City
- State
- ZIP Code

This information is **stored in the database** under user profile (shopusers table)

### Step 3: Payment Information Collection
**Form collects:**
- Payment Method (dropdown)
- Card Number (16 digits)
- Expiry Date (MM/YY format)
- CVV (3 digits)

**No actual payment is processed** - only validated and recorded.

### Step 4: Local Validation (No Real Bank Interaction)
```
User submits form
    ↓
Validate shipping fields (required, format)
    ↓
Validate payment method (credit_card, virtual_wallet, bank_transfer)
    ↓
Validate card format (16 digits, Luhn algorithm check)
    ↓
Create order in database
    ↓
Create payment record in database (status = "processing")
    ↓
Generate unique transaction ID
    ↓
Mark payment as "completed" in database
    ↓
Redirect to success page
```

---

## 💳 Payment Processing Details

### Supported Payment Methods
1. **Credit Card** ✅
   - Validates: 16-digit card number
   - Algorithm: Luhn check (validates card format only, not real account)
   - Test card: `4532 1111 1111 1111` (passes Luhn check)
   - Any future expiry (MM/YY)
   - Any 3-digit CVV

2. **Virtual Wallet** ✅
   - Simulated wallet system
   - Requires wallet_id and amount

3. **Bank Transfer** ✅
   - Simulated bank transfer
   - Requires account_number and routing_number

4. **Gift Card** ✅
   - References gift_cards table
   - Validates code exists and has balance

### Validation Process (PaymentController.php)

```php
validatePayment($method, $details) {
    if ($method == 'credit_card') {
        // Remove non-digits: "4532 1111 1111 1111" → "4532111111111111"
        // Check length = 16
        // Run Luhn check (mathematical validation, not real processing)
        return luhnCheck($cardNumber);
    }
    // ... other methods
}
```

**Key Points:**
- ✅ Validates format and structure
- ❌ Does NOT connect to any bank
- ❌ Does NOT charge actual credit cards
- ❌ Does NOT contact payment processors (Stripe, PayPal, etc.)
- ✅ Only records transaction in local database

---

## 📊 Database Records Created

### 1. Order Created in `orders` table
```sql
INSERT INTO orders (
    user_id, 
    total_amount, 
    payment_method, 
    shipping_address,  -- JSON with all address info
    status              -- 'pending' → 'confirmed'
) VALUES (...)
```

### 2. Order Items Added to `order_items` table
```sql
INSERT INTO order_items (
    order_id, 
    product_id, 
    quantity, 
    price
) VALUES (...)
```

### 3. Payment Record Created in `payments` table
```sql
INSERT INTO payments (
    order_id, 
    user_id, 
    method,                    -- 'credit_card', 'virtual_wallet', etc.
    amount,                    -- Order total
    status,                    -- 'processing' → 'completed'
    transaction_id,            -- 'TXN-1234567890-abcdef123456'
    created_at
) VALUES (...)
```

### 4. Status Logged (Optional)
```sql
INSERT INTO order_status_log (
    order_id, 
    status,              -- 'confirmed'
    notes, 
    created_at
) VALUES (...)
```

---

## 🧪 Test Card Examples

These cards all pass the **Luhn validation** (format check):

| Card Number | Type | Status |
|------------|------|--------|
| 4532111111111111 | Visa | ✅ Valid (demo) |
| 5425233010103010 | Mastercard | ✅ Valid (demo) |
| 378282246310005 | Amex | ✅ Valid (demo) |

**For all test cards:**
- Expiry: Any future date (e.g., 12/25, 06/26, etc.)
- CVV: Any 3 digits (e.g., 123, 999, etc.)
- Result: Payment "completed" and recorded in database

---

## 🚀 What Happens After Checkout

### Immediate (Same Request)
1. Order created in database
2. Payment record created
3. Transaction ID generated
4. User redirected to success.php

### What the User Sees
- Success page with order ID
- Order confirmation message
- Links to view order details

### What's Actually Stored
- Order details (items, total, shipping address)
- Payment method used
- Unique transaction ID
- Order status: "confirmed"
- Timestamps of all actions

---

## 🔐 No Real Charges

```
Your System                Real Payment Processor
┌──────────────┐           ┌──────────────────┐
│   Database   │           │  Stripe/PayPal   │
│   Records    │  ❌ NO    │  Bank Account    │
│   Payment    │   API     │  Credit Card     │
│   Info       │   CALL    │  Processing      │
└──────────────┘           └──────────────────┘
```

**The payment system:**
- ✅ Validates format
- ✅ Stores records
- ✅ Generates receipts
- ❌ Does NOT charge cards
- ❌ Does NOT contact real payment processors
- ❌ Does NOT access bank systems

---

## 📋 Order Flow Example

### User Actions
```
1. Add products to cart
2. Click "Proceed to Checkout"
3. Fill in shipping form:
   - Name: John Doe
   - Email: john@example.com
   - Address: 123 Main St
   - City: Springfield
   - State: IL
   - ZIP: 62701
4. Select payment method: "Credit Card"
5. Enter card: 4532 1111 1111 1111
6. Enter expiry: 12/25
7. Enter CVV: 123
8. Click "Complete Order"
```

### System Processing
```
Step 1: Validate all fields
        ✅ Shipping: All required fields present
        
Step 2: Update user profile
        UPDATE shopusers SET address = '123 Main St', city = 'Springfield'...
        
Step 3: Create order
        INSERT INTO orders (user_id, total_amount, status='pending')
        ORDER ID = 42
        
Step 4: Add order items
        INSERT INTO order_items (order_id=42, product_id=1, quantity=2)
        
Step 5: Validate payment
        Card format: "4532111111111111"
        Length: 16 ✅
        Luhn check: PASS ✅
        
Step 6: Create payment record
        INSERT INTO payments (order_id=42, method='credit_card', status='processing')
        
Step 7: Generate transaction ID
        TXN-1702980523-a1b2c3d4e5f6g7h8
        
Step 8: Complete payment
        UPDATE payments SET status='completed', transaction_id='TXN-...'
        UPDATE orders SET status='confirmed'
        
Step 9: Success!
        Redirect to success.php?order_id=42
```

### Database Result
```
orders:
  id: 42
  user_id: 1
  total_amount: 1299.98
  status: confirmed
  shipping_address: {"fullName":"John Doe",...}

order_items:
  id: 1 | order_id: 42 | product_id: 1 | quantity: 2 | price: 999.99
  id: 2 | order_id: 42 | product_id: 7 | quantity: 1 | price: 49.99

payments:
  id: 15
  order_id: 42
  user_id: 1
  method: credit_card
  amount: 1299.98
  status: completed
  transaction_id: TXN-1702980523-a1b2c3d4e5f6g7h8
```

---

## 🎓 To Upgrade to Real Payments

If you want to handle **real payments** in the future, you would:

1. **Integrate a payment gateway** (Stripe, PayPal, Square)
   - Replace validation with API calls
   - Get authorization tokens
   - Handle actual charges

2. **Example code change:**
   ```php
   // CURRENT (Simulated)
   if ($this->luhnCheck($cardNumber)) {
       // Record as complete
   }
   
   // FUTURE (Real Payment)
   try {
       $charge = Stripe::charges()->create([
           'amount' => $amount,
           'currency' => 'usd',
           'source' => $token,
       ]);
       // Only mark complete if Stripe returns success
   } catch (Exception $e) {
       // Payment failed
   }
   ```

3. **Never store sensitive data:**
   - Don't save full card numbers (only last 4 digits)
   - Use tokenization
   - Follow PCI compliance

---

## ✅ Summary

**Current System:**
- ✅ Fully simulated/demo mode
- ✅ No real charges
- ✅ Local validation only
- ✅ Records stored in database
- ✅ Perfect for testing/demonstration
- ✅ Test card: 4532 1111 1111 1111

**Safe to:**
- ✅ Test with any card number
- ✅ Use test data
- ✅ Demo to stakeholders
- ✅ Run integration tests

**NOT safe for:**
- ❌ Real customer transactions
- ❌ Processing actual payments
- ❌ Production without real gateway

---

**Is your checkout system secure for demos?** Yes! 100% safe - no real payments possible.

**Can I use test data?** Yes! Use test card: 4532 1111 1111 1111 with any future date and any 3-digit CVV.
