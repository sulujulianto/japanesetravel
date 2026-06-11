# Sample Bug Reports

These are simulated portfolio examples, not claims of unresolved production defects.

## BUG-001: Uploaded Image Disappears After Redeploy

- **Environment:** Railway staging, `MEDIA_DISK=public`, no confirmed volume
- **Severity:** High
- **Priority:** P0

### Steps to Reproduce

1. Log in as admin.
2. Upload a destination image.
3. Confirm its public URL returns `200`.
4. Redeploy/restart the service.
5. Reload the image URL.

### Expected Result

The upload remains available.

### Actual Result

The URL returns `404`; database still stores the relative path.

### Evidence Placeholder

- Before URL/status: pending
- After URL/status: pending
- Railway volume screenshot: pending
- Log excerpt: pending

### Suspected Cause

Container-local `storage/app/public` was replaced because no persistent volume was mounted.

### Suggested Fix

Mount a Railway Volume at `/var/www/html/storage/app/public`, preserve the symlink, re-upload the missing asset, and repeat the redeploy test.

### Regression Test Recommendation

Add the media persistence scenario to every staging release smoke test. Automated local filesystem tests cannot prove deployment persistence.

## BUG-002: Pending Payment Has No Redirect and Cannot Be Retried

- **Environment:** Local/staging with sandbox provider
- **Severity:** Medium
- **Priority:** P1

### Steps to Reproduce

1. Create an order with a pending payment.
2. Ensure stored gateway payload has no usable redirect/approval URL.
3. Open order detail.
4. Trigger payment retry.

### Expected Result

The application avoids duplicate pending payments and gives a recoverable explanation/action.

### Actual Result

Simulated failure state: retry cannot continue because the existing pending payment has no redirect URL.

### Evidence Placeholder

- Order/payment IDs: pending, redact before sharing
- UI message: pending
- Relevant log: pending
- Database query: pending

### Suspected Cause

Gateway creation stopped after persisting a pending payment but before a usable redirect URL was stored.

### Suggested Fix

Preserve the existing duplicate-payment guard. Decide in a dedicated change whether an old pending payment without a redirect should expire safely before creating a replacement. Do not bypass status/ownership checks.

### Regression Test Recommendation

Keep `CheckoutTest::test_retry_payment_with_pending_payment_without_redirect_url_does_not_create_duplicate_payment` and add a UI/manual assertion for clear recovery instructions.

