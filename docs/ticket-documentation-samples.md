# Ticket Documentation Samples

These are portfolio samples, not records of active production tickets.

## Ticket 1 — Bug

- **Title:** Uploaded media returns 404 after staging redeploy
- **Type:** Bug
- **Priority:** P0
- **Environment:** Railway staging, single web service

### Description

Destination and souvenir uploads display immediately but disappear after a redeploy while database image paths remain.

### Steps to Reproduce

1. Upload a destination image in admin.
2. Save its `/storage/uploads/places/...` URL.
3. Redeploy the web service.
4. Open the saved URL.

### Expected Result

The image remains available after redeploy.

### Actual Result

The URL returns `404`.

### Acceptance Criteria

- Railway Volume is mounted at `/var/www/html/storage/app/public`.
- Existing/new uploads survive restart and redeploy.
- `public/storage` symlink exists.
- Replace/delete behavior remains correct.
- Persistence evidence is attached.

### Notes

Likely infrastructure persistence issue, not a Blade rendering issue.

## Ticket 2 — Feature Request

- **Title:** Add owner-controlled portfolio screenshot gallery
- **Type:** Feature
- **Priority:** P1
- **Environment:** Repository documentation

### Description

Add verified desktop/mobile/light/dark screenshots so reviewers can understand the application before cloning it.

### Task Steps

1. Follow `docs/screenshots/README.md`.
2. Capture the required views from the current commit.
3. Remove personal/sensitive data.
4. Add only existing files to README.

### Expected Result

README displays a concise gallery with no broken links.

### Actual Result

Only one general interface preview currently exists.

### Acceptance Criteria

- Required screenshot files exist.
- Naming convention is followed.
- Commit SHA and capture date are recorded.
- README links render on GitHub.

### Notes

No generated/fake screenshots.

## Ticket 3 — QA Verification

- **Title:** Verify duplicate payment webhooks are idempotent in PayPal sandbox
- **Type:** QA
- **Priority:** P0
- **Environment:** PayPal sandbox + public staging URL

### Description

Replay a verified PayPal event and confirm it is recorded/processed only once.

### Task Steps

1. Complete a sandbox checkout.
2. Record order/payment state.
3. Deliver the valid webhook event.
4. Replay the same event ID.
5. Inspect order, payment, webhook-event rows, and logs.

### Expected Result

- One webhook-event row exists for the provider/event ID.
- Order/payment are not updated twice.
- Stock is not changed by duplicate delivery.

### Actual Result

Not executed; automated tests cover the internal contract only.

### Acceptance Criteria

- Provider dashboard and application evidence are captured.
- Secrets/personal data are redacted.
- Database query confirms uniqueness.
- Any discrepancy creates a bug ticket.

### Notes

Owner action required because sandbox credentials and provider dashboard access are external.
