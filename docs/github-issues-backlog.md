# Copy-Paste GitHub Issues Backlog

These are proposed issues, not claims that GitHub issues or a project board already exist.

## Deployment

### Deploy a public Railway staging environment

- **Labels:** `deployment`, `portfolio`, `P0`
- **Priority:** P0
- **Suggested branch:** `docs/railway-staging-evidence`
- **Description:** Deploy the current `main` commit to Railway using the documented Docker/runtime contract. Keep demo seeders disabled and payment providers in sandbox mode.
- **Acceptance criteria:**
  - Public HTTPS URL is available.
  - `/up` health check passes.
  - Migration runs separately with `migrate --force`.
  - No demo seeder runs in staging.
  - Public/user/admin smoke checks are recorded.
  - README live-demo placeholder is replaced with the real URL.

### Verify Railway database backup and rollback procedure

- **Labels:** `deployment`, `database`, `P1`
- **Priority:** P1
- **Suggested branch:** `docs/railway-backup-runbook`
- **Description:** Document and test a small staging backup/restore and application rollback procedure.
- **Acceptance criteria:**
  - Database export and restore steps are documented.
  - Media volume backup/export is documented.
  - Deployed commit SHA and rollback method are recorded.
  - No production or personal data is used.

## Documentation

### Capture and publish portfolio screenshots

- **Labels:** `documentation`, `portfolio`, `P1`
- **Priority:** P1
- **Suggested branch:** `docs/add-portfolio-screenshots`
- **Description:** Capture the views listed in `docs/screenshots/README.md` using the current curated dataset.
- **Acceptance criteria:**
  - Required desktop/mobile/dark screenshots exist.
  - No secret or personal data is visible.
  - README references only existing files.
  - Capture date and commit SHA are recorded.

### Record the project demo video

- **Labels:** `documentation`, `portfolio`, `P1`
- **Priority:** P1
- **Suggested branch:** `docs/add-demo-video-link`
- **Description:** Record a 1–2 minute walkthrough using `docs/demo-video-script.md`.
- **Acceptance criteria:**
  - Video follows the agreed scope.
  - Honest limitations are included.
  - Link is public and accessible.
  - README placeholder is replaced only after verification.

## Testing / QA

### Execute the full manual regression checklist

- **Labels:** `qa`, `regression`, `P0`
- **Priority:** P0
- **Suggested branch:** `qa/manual-regression-evidence`
- **Description:** Execute `docs/qa/manual-qa-checklist.md` against the staging environment.
- **Acceptance criteria:**
  - Tester/date/environment are recorded.
  - Failures create separate bug issues.
  - Evidence paths are added without secrets.
  - Final result is summarized in the execution table.

### Add browser smoke tests for critical journeys

- **Labels:** `testing`, `automation`, `P2`
- **Priority:** P2
- **Suggested branch:** `test/browser-critical-smoke`
- **Description:** Add lightweight browser coverage for public navigation, user order access, and admin access without introducing an excessive local resource burden.
- **Acceptance criteria:**
  - Tool choice and laptop impact are documented.
  - Tests run in CI.
  - At least one public, user, and admin path is covered.
  - Existing feature tests remain green.

## Payment Sandbox Validation

### Validate Midtrans sandbox checkout and webhook flow

- **Labels:** `payment`, `qa`, `external`, `P0`
- **Priority:** P0
- **Suggested branch:** `qa/midtrans-sandbox-evidence`
- **Description:** Configure an approved Midtrans sandbox account and execute the documented payment matrix.
- **Acceptance criteria:**
  - Checkout creates a sandbox transaction.
  - Signed pending/settlement/failure callbacks are observed.
  - Duplicate callback has no duplicate effect.
  - Order and stock state match expectations.
  - Sensitive values are redacted from evidence.

### Validate PayPal sandbox checkout, return, and webhook flow

- **Labels:** `payment`, `qa`, `external`, `P0`
- **Priority:** P0
- **Suggested branch:** `qa/paypal-sandbox-evidence`
- **Description:** Configure PayPal sandbox credentials/webhook ID and validate create, approve, capture, cancel, failure, and replay scenarios.
- **Acceptance criteria:**
  - Return/cancel URLs use staging HTTPS.
  - Capture success/failure behavior is recorded.
  - Webhook verification succeeds.
  - Duplicate event is idempotent.
  - Manual exchange-rate configuration is reviewed.

## Media Persistence

### Verify media survives Railway redeploy

- **Labels:** `media`, `deployment`, `P0`
- **Priority:** P0
- **Suggested branch:** `qa/media-persistence-evidence`
- **Description:** Mount a Railway Volume and prove that admin uploads survive restart/redeploy.
- **Acceptance criteria:**
  - Volume is mounted at `/var/www/html/storage/app/public`.
  - Destination and souvenir uploads return `200` before and after redeploy.
  - Replace/delete behavior is verified.
  - Backup procedure is recorded.

## Observability

### Enable privacy-conscious staging error monitoring

- **Labels:** `observability`, `security`, `P2`
- **Priority:** P2
- **Suggested branch:** `chore/staging-sentry-config`
- **Description:** Enable Sentry or equivalent monitoring for staging without sending default PII.
- **Acceptance criteria:**
  - DSN remains an environment secret.
  - `SENTRY_SEND_DEFAULT_PII=false`.
  - A controlled test exception is captured.
  - `/up` is excluded as currently configured.
  - Retention and access are documented.

## Performance

### Establish a lightweight performance baseline

- **Labels:** `performance`, `qa`, `P2`
- **Priority:** P2
- **Suggested branch:** `qa/performance-baseline`
- **Description:** Record simple response/build/database observations without introducing paid tooling.
- **Acceptance criteria:**
  - Homepage, places, shop, and admin dashboard are measured.
  - Test data size and environment are stated.
  - Slow queries or large assets are documented.
  - No unsupported “high-scale” claim is made.

## Refactor / Technical Debt

### Reduce the PHPStan baseline incrementally

- **Labels:** `refactor`, `static-analysis`, `P2`
- **Priority:** P2
- **Suggested branch:** `refactor/phpstan-baseline-batch-1`
- **Description:** Remove a small coherent group of baseline entries by adding return types, relationship generics, or data-shape annotations without changing behavior.
- **Acceptance criteria:**
  - Selected baseline entries are removed.
  - No new ignore rules are added.
  - Full test/Pint/PHPStan/build checks pass.
  - Behavior changes, if any, are explicitly excluded.

### Generalize the Railway worker connection

- **Labels:** `refactor`, `deployment`, `P2`
- **Priority:** P2
- **Suggested branch:** `refactor/railway-worker-connection`
- **Description:** Allow `railway/run-worker.sh` to honor `QUEUE_CONNECTION` instead of hardcoding `database`.
- **Acceptance criteria:**
  - Database remains the default.
  - Documentation and environment examples are updated.
  - Shell behavior is tested manually.
  - No queue backend is claimed without configuration.

