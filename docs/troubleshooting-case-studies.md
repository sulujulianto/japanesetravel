# Troubleshooting Case Studies

These cases are based on the repository's architecture. Example root causes are illustrative; they are not claims of real production incidents.

## Case 1: Checkout Cannot Create a Payment

### Symptom

- User submits checkout and returns to the cart with “Failed to create payment.”
- Order/payment may be marked cancelled/failed.
- Stock should be restored and the cart should remain available.

### Possible Causes

- Missing or invalid Midtrans/PayPal sandbox credentials.
- Production flag does not match the credential environment.
- PayPal exchange rate is invalid.
- Provider API is unavailable or blocked.
- `APP_URL` generates unreachable return URLs.
- Midtrans does not return a redirect URL.

### Investigation

1. Reproduce using a small souvenir quantity.
2. Check application logs:

```bash
tail -n 200 storage/logs/laravel.log
```

3. Confirm configuration without printing secrets:

```bash
php artisan about
php artisan config:show services
```

Redact output before sharing.

4. Inspect the latest order/payment:

```sql
SELECT id, user_id, total_price, status, created_at
FROM orders
ORDER BY id DESC
LIMIT 5;

SELECT id, order_id, provider, provider_ref, status, currency, amount, created_at
FROM payments
ORDER BY id DESC
LIMIT 5;
```

5. Confirm stock returned after gateway creation failure.
6. Compare `APP_URL`, provider mode flags, and provider dashboard URLs.
7. Run targeted tests:

```bash
php artisan test --filter=Checkout
php artisan test --filter=PaymentWebhook
```

### Example Root Cause

`PAYPAL_CLIENT_SECRET` was empty while PayPal was selected. Gateway creation failed before a redirect URL was returned.

### Fix

- Add valid sandbox credentials as environment secrets.
- Keep `PAYPAL_IS_PRODUCTION=false`.
- Retry with an approved sandbox buyer.
- Confirm the failed attempt did not leave stock decremented.

### Prevention

- Validate the payment sandbox checklist before demonstrations.
- Keep automated compensation/retry tests in CI.
- Monitor gateway errors without logging credentials.

### Interview Explanation

> “I first separate an internal checkout failure from a provider failure. I inspect the order/payment rows and logs, confirm stock compensation, verify environment mode and callback URLs, then reproduce with sandbox credentials. The application is designed to fail safely by restoring stock and avoiding a false paid state.”

## Case 2: Uploaded Images Return 404 After Deployment

### Symptom

- Upload succeeds and displays initially.
- After restart/redeploy, the old `/storage/uploads/...` URL returns `404`.
- Database still contains the relative media path.

### Possible Causes

- No persistent Railway Volume.
- Volume mounted to the wrong directory.
- Missing `public/storage` symlink.
- `APP_URL` is incorrect.
- File permissions prevent Apache from reading the volume.

### Investigation

1. Inspect the database path:

```sql
SELECT id, image FROM places WHERE image IS NOT NULL ORDER BY id DESC LIMIT 10;
SELECT id, image FROM souvenirs WHERE image IS NOT NULL ORDER BY id DESC LIMIT 10;
```

2. Inspect runtime storage:

```bash
ls -la public/storage
find storage/app/public/uploads -maxdepth 3 -type f
```

3. Confirm the volume mount is `/var/www/html/storage/app/public`.
4. Run safely:

```bash
php artisan storage:link
```

5. Check the public URL with:

```bash
curl -I https://your-domain.example/storage/uploads/places/example.webp
```

6. Review deploy logs for permission/symlink errors.

### Example Root Cause

`MEDIA_DISK=public` wrote files to the container filesystem, but no persistent volume was mounted. Redeploy replaced the container while database paths remained.

### Fix

- Mount a Railway Volume at `/var/www/html/storage/app/public`.
- Redeploy and upload the missing media again from approved assets.
- Confirm runtime creates the symlink and permissions.

### Prevention

- Complete the media persistence smoke test before sharing the deployment.
- Back up the volume.
- Use one web replica with local volume storage.
- Move to object storage before scaling horizontally.

### Interview Explanation

> “The database stored relative paths, not image bytes. The bug was an infrastructure persistence mismatch, so changing Blade or the database would not fix it. The correct fix is persistent storage plus a valid public symlink.”

## Case 3: Verification or Reset Email Is Not Delivered

### Symptom

- The UI reports that a link was sent.
- No email reaches the inbox.
- Local development shows no provider delivery event.

### Possible Causes

- `MAIL_MAILER=log`.
- Invalid SMTP credentials, port, or TLS scheme.
- Sender domain is unverified.
- Message is in spam.
- Queue worker is not running if mail becomes queued later.
- `APP_URL` is incorrect, causing invalid links.

### Investigation

1. Check the active mailer:

```bash
php artisan config:show mail
```

2. For local `log` mailer:

```bash
tail -n 200 storage/logs/laravel.log
```

3. Clear stale cached configuration after environment changes:

```bash
php artisan config:clear
php artisan config:cache
```

4. Check SMTP/provider activity logs.
5. Verify `MAIL_FROM_ADDRESS` and domain status.
6. Confirm `APP_URL` matches the current HTTPS deployment.
7. Run:

```bash
php artisan test --filter=PasswordReset
php artisan test --filter=EmailVerification
```

### Example Root Cause

Staging still used `MAIL_MAILER=log`, so Laravel wrote the message to application logs instead of delivering it.

### Fix

- Configure a valid transactional SMTP provider.
- Verify the sender/domain.
- Rebuild config cache.
- Repeat reset and verification smoke tests.

### Prevention

- Keep mail delivery in the deployment checklist.
- Do not describe password reset as production-ready until inbox delivery is proven.
- Monitor provider rejection/bounce logs.

### Interview Explanation

> “Framework tests can prove that Laravel creates the notification and token, but only an external smoke test proves deliverability. I verify the active mailer, cached configuration, sender domain, provider logs, and final signed link.”

