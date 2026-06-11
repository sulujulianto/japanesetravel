# SQL Inspection Examples

Queries use the current schema and are intended for read-only troubleshooting. Run them against a local/staging database, not against unknown production data. Replace example IDs and date ranges.

## 1. Recent Orders

**Purpose:** inspect recent order state and ownership.

```sql
SELECT id, user_id, total_price, status, created_at, updated_at
FROM orders
ORDER BY created_at DESC
LIMIT 20;
```

**Expected result:** newest orders with `pending`, `processing`, `completed`, or `cancelled` status.

**What it proves:** whether checkout created an order and its current state.

**Interview explanation:** “I start with the parent order before inspecting payment or item details.”

## 2. Order Items

**Purpose:** verify quantity, price, and product snapshots.

```sql
SELECT id, order_id, souvenir_id, product_name, quantity, price, product_price, product_image
FROM order_items
WHERE order_id = 123
ORDER BY id;
```

**Expected result:** one or more items whose quantities and snapshot values match the order.

**What it proves:** historical order data is preserved even if the current souvenir changes.

**Interview explanation:** “Order items keep snapshot fields so history does not depend entirely on the live product row.”

## 3. Payment Status

**Purpose:** compare payment and order states.

```sql
SELECT
    p.id AS payment_id,
    p.order_id,
    p.provider,
    p.provider_ref,
    p.status AS payment_status,
    p.amount,
    p.currency,
    p.paid_at,
    o.status AS order_status
FROM payments p
JOIN orders o ON o.id = p.order_id
WHERE p.order_id = 123
ORDER BY p.id DESC;
```

**Expected result:** payment attempts ordered newest-first.

**What it proves:** payment status and order fulfillment status are separate concepts.

**Interview explanation:** “A paid payment may promote a pending order to processing, but webhook guards prevent terminal orders from being downgraded or revived.”

## 4. Duplicate Review Prevention

**Purpose:** confirm no user/place pair has multiple reviews.

```sql
SELECT place_id, user_id, COUNT(*) AS review_count
FROM place_reviews
GROUP BY place_id, user_id
HAVING COUNT(*) > 1;
```

**Expected result:** zero rows.

**What it proves:** the application data respects the unique `(place_id, user_id)` rule.

**Interview explanation:** “The controller gives a friendly response, while the database constraint protects against races.”

## 5. Low-Stock Souvenirs

**Purpose:** identify products at or below an operational threshold.

```sql
SELECT id, name, stock, price, updated_at
FROM souvenirs
WHERE stock <= 5
ORDER BY stock ASC, id ASC;
```

**Expected result:** only low/out-of-stock products.

**What it proves:** admin inventory data matches the low-stock view.

**Note:** `name` is stored as localized JSON.

## 6. Failed or Expired Payment Attempts

**Purpose:** inspect recent unsuccessful attempts.

```sql
SELECT id, order_id, provider, provider_ref, status, amount, currency, created_at
FROM payments
WHERE status IN ('failed', 'expired', 'cancelled')
ORDER BY created_at DESC
LIMIT 20;
```

**Expected result:** recent unsuccessful attempts without exposing full payload data.

**What it proves:** failure states are persisted and can be correlated with logs.

**Interview explanation:** “I avoid selecting payload JSON by default because it may contain provider metadata that should be handled carefully.”

## 7. Webhook Idempotency

**Purpose:** confirm provider event IDs are unique.

```sql
SELECT provider, event_id, COUNT(*) AS event_count
FROM payment_webhook_events
GROUP BY provider, event_id
HAVING COUNT(*) > 1;
```

**Expected result:** zero rows.

**What it proves:** the unique constraint prevents duplicate webhook-event records.

To inspect one payment:

```sql
SELECT id, payment_id, provider, event_id, status, created_at
FROM payment_webhook_events
WHERE payment_id = 456
ORDER BY created_at;
```

## 8. User Order History

**Purpose:** verify the orders visible to one user.

```sql
SELECT o.id, o.total_price, o.status, o.created_at
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE u.email = 'user.demo@japantravel.test'
ORDER BY o.created_at DESC;
```

**Expected result:** only orders belonging to that email.

**What it proves:** the dataset available to the user order query.

**Interview explanation:** “The application also enforces ownership before rendering order detail.”

## 9. Admin-Role Accounts

The project uses the `users` table for both user and admin providers, separated by guards and the `role` value.

```sql
SELECT id, username, email, role, email_verified_at, created_at
FROM users
WHERE role = 'admin'
ORDER BY id;
```

**Expected result:** approved admin-role accounts only.

**What it proves:** which records can authenticate through the admin guard.

## 10. Order Total Cross-Check

**Purpose:** compare stored total with item calculations.

```sql
SELECT
    o.id,
    o.total_price AS stored_total,
    SUM(oi.price * oi.quantity) AS calculated_total
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.id = 123
GROUP BY o.id, o.total_price;
```

**Expected result:** totals match.

**What it proves:** order/item financial consistency for the selected record.

