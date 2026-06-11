# Railway Staging Deployment Checklist

This checklist prepares JapanTravel for a public portfolio/staging deployment. It does not claim that deployment or external integrations have been completed.

## Current Runtime Contract

- The Dockerfile builds Composer production dependencies and Vite assets in separate stages.
- `railway/start-web.sh` performs runtime directory/cache preparation and starts Apache.
- Normal web startup does **not** run migrations or seeders.
- Migrations must run separately with:

```bash
sh railway/init-app.sh --migrate-only
```

- Laravel exposes the health endpoint at `GET /up`.
- `railway/run-worker.sh` explicitly uses the `database` queue connection.
- `railway/run-cron.sh` runs Laravel's scheduler worker.

## 1. Owner Account Preparation

- [ ] **Owner action required:** create a Railway account and project.
- [ ] Connect the GitHub repository `sulujulianto/japanesetravel`.
- [ ] Create a MySQL-compatible database service.
- [ ] Decide whether worker and scheduler services are needed for the first demo.
- [ ] Do not import `japantravel.sql` or execute demo seeders against staging data.

## 2. Required Application Environment

Set secrets through Railway variables, never in committed files.

```env
APP_NAME=JapanTravel
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:generated-value
APP_URL=https://your-public-domain.example
TRUSTED_PROXIES=*
LOG_CHANNEL=stack
LOG_STACK=stderr
LOG_LEVEL=info

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
MEDIA_DISK=public
```

Generate `APP_KEY` locally once and store the output as a Railway secret:

```bash
php artisan key:generate --show
```

Do not regenerate it on each deploy.

## 3. Database

- [ ] Configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`, or the provider's supported database URL.
- [ ] Confirm the database is reachable from the application service.
- [ ] Run the one-off migration command:

```bash
sh railway/init-app.sh --migrate-only
```

- [ ] Confirm the command reports successful migrations.
- [ ] Do **not** run `migrate:fresh`, `migrate --seed`, `DemoSeeder`, or `DevAccountSeeder`.
- [ ] Create any staging reviewer accounts manually with temporary credentials and rotate/remove them after review.

## 4. Persistent Media

- [ ] Create a Railway Volume.
- [ ] Mount it at:

```text
/var/www/html/storage/app/public
```

- [ ] Keep `MEDIA_DISK=public`.
- [ ] Confirm runtime creates the `public/storage` symlink.
- [ ] Use one web replica while relying on a local volume.
- [ ] Configure a volume backup/export procedure.

Smoke test:

1. Upload one destination image.
2. Upload one souvenir image.
3. Save both public URLs.
4. Restart/redeploy the service.
5. Confirm both URLs still return `200`.
6. Replace one image and confirm the old path is removed.
7. Delete a test item and confirm its valid uploaded media is removed.

## 5. Health Check and Networking

- [ ] Set Railway HTTP health check path to `/up`.
- [ ] Verify `https://your-domain/up` returns a successful response.
- [ ] Confirm `APP_URL` uses the final HTTPS origin.
- [ ] Verify user/admin cookies are marked Secure in the browser.
- [ ] Verify generated PayPal return/cancel URLs use HTTPS.

Docker does not currently define a `HEALTHCHECK`; Railway's HTTP health check is the documented mechanism.

## 6. Mail

Local `MAIL_MAILER=log` does not deliver email.

- [ ] **Owner action required:** choose a transactional SMTP/mail provider.
- [ ] Configure `MAIL_MAILER`, host, port, username, password, scheme, and sender identity.
- [ ] Request a password reset and confirm delivery.
- [ ] Register an unverified user, resend verification, and confirm the signed link works.
- [ ] Check spam placement and provider logs.

## 7. WhatsApp Inquiry

- [ ] Set `TRAVEL_WHATSAPP_NUMBER` to international digits without `+` or spaces if the CTA should be active.
- [ ] Leave it empty if there is no approved business number.
- [ ] Confirm the CTA does not add an automatic message.
- [ ] Confirm no copy implies direct travel booking or ticket payment.

## 8. Payment Sandbox

Keep both production flags disabled for staging:

```env
MIDTRANS_IS_PRODUCTION=false
PAYPAL_IS_PRODUCTION=false
```

- [ ] **Owner action required:** add valid sandbox credentials.
- [ ] Register `POST /payments/webhook/midtrans` with Midtrans.
- [ ] Register `POST /payments/webhook/paypal` with PayPal and set `PAYPAL_WEBHOOK_ID`.
- [ ] Test a low-value souvenir checkout with official sandbox instruments.
- [ ] Confirm redirect/callback URLs return to the HTTPS deployment.
- [ ] Confirm paid webhook changes `pending` order to `processing`.
- [ ] Replay the same webhook and confirm no duplicate effect.
- [ ] Test failure/cancel and confirm stock/order behavior.
- [ ] Verify the manually configured PayPal exchange rate before any demonstration.
- [ ] Never use real money for portfolio validation.

## 9. Worker and Scheduler

The first portfolio deployment may omit these if no asynchronous application work depends on them, but database-backed mail/queue choices must match actual usage.

- [ ] If enabled, create a worker service using `sh railway/run-worker.sh`.
- [ ] Keep `QUEUE_CONNECTION=database`; the current worker script hardcodes that connection.
- [ ] Optionally create a scheduler service using `sh railway/run-cron.sh`.
- [ ] Check `failed_jobs` and service logs after smoke testing.

## 10. Application Smoke Test

- [ ] `/` renders in ID and EN.
- [ ] `/places` search/filter and destination detail work.
- [ ] User registration/login/email verification flow works.
- [ ] Verified user can submit one review and cannot duplicate it.
- [ ] `/shop` and `/cart` work.
- [ ] Checkout sandbox flow reaches the selected provider.
- [ ] User sees only their own orders.
- [ ] `/admin/login` works with an approved staging admin.
- [ ] Normal user cannot access admin routes.
- [ ] Admin can manage destinations and souvenirs.
- [ ] Admin can inspect orders and update valid statuses.
- [ ] Upload persists after restart/redeploy.
- [ ] `/up` remains healthy.
- [ ] Application logs do not expose secrets or raw credentials.

## 11. Evidence to Capture

- [ ] Public URL.
- [ ] Railway service status and `/up` response.
- [ ] Migration command result with secrets redacted.
- [ ] Media persistence before/after redeploy.
- [ ] Mail delivery evidence with personal data redacted.
- [ ] Sandbox payment IDs with sensitive values redacted.
- [ ] CI run URL.
- [ ] Completed manual QA execution table.

## 12. Rollback and Safety

- [ ] Record the deployed commit SHA.
- [ ] Back up database and media volume before destructive maintenance.
- [ ] Roll back application image without rolling back schema blindly.
- [ ] Never run demo seeders as a recovery action.
- [ ] Keep payment production flags disabled until a dedicated go-live review.

