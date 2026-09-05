# Railway Deployment Runbook

## Status

**Deferred and optional for the current local-first portfolio release.**

This document preserves a future Railway deployment contract. Empty checkboxes are not portfolio-release blockers, and their presence must not be interpreted as proof that hosting, provider accounts, or production operations have been completed.

## Runtime Contract

- The multi-stage Dockerfile builds production Composer dependencies and Vite assets separately.
- `railway/start-web.sh` performs runtime directory/cache preparation and starts Apache.
- Normal startup does not run migrations or seeders.
- Migrations run as a separate one-off action:

```bash
sh railway/init-app.sh --migrate-only
```

- Laravel exposes `GET /up` for HTTP health checks.
- `railway/run-worker.sh` explicitly uses the `database` queue connection.
- `railway/run-cron.sh` runs the Laravel scheduler worker.
- Payment payload retention requires the scheduler to execute `payments:prune-payloads`.

## 1. Owner Account Preparation

- [ ] Create a Railway account and project.
- [ ] Connect `sulujulianto/japanesetravel` at an explicitly reviewed commit.
- [ ] Create a MySQL-compatible database service.
- [ ] Decide whether worker and scheduler services are required.
- [ ] Record the selected plan limits and expected cost before enabling resources.
- [ ] Do not import an old demo SQL dump or execute demo seeders on remote data.

## 2. Application Environment

Store secrets in Railway variables, never in committed files:

```env
APP_NAME=Japan Travel
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

Generate `APP_KEY` once and store it as a secret:

```bash
php artisan key:generate --show
```

Do not regenerate the key during normal deploys; doing so would invalidate encrypted data and sessions.

## 3. Database

- [ ] Configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` or a supported database URL.
- [ ] Confirm network access from the application service.
- [ ] Run `sh railway/init-app.sh --migrate-only` as a separate deploy step.
- [ ] Confirm migrations completed without running seeds.
- [ ] Never run `migrate:fresh`, `migrate --seed`, `DemoSeeder`, or `DevAccountSeeder` remotely.
- [ ] Create temporary reviewer accounts manually and remove or rotate them after review.

## 4. Persistent Media

- [ ] Create a Railway Volume.
- [ ] Mount it at `/var/www/html/storage/app/public`.
- [ ] Keep `MEDIA_DISK=public` and confirm `public/storage` is a valid symlink.
- [ ] Use one web replica while relying on a local volume.
- [ ] Document a volume backup/export procedure.

Persistence smoke test:

1. Upload one destination and one souvenir image.
2. Save both public URLs.
3. Restart or redeploy the service.
4. Confirm both URLs still return `200`.
5. Replace one image and confirm the previous upload is removed safely.
6. Delete a test record and confirm only its approved upload path is removed.

## 5. Health, Proxy, and Cookies

- [ ] Configure Railway HTTP health path `/up`.
- [ ] Confirm the public HTTPS `/up` response succeeds.
- [ ] Set `APP_URL` to the final origin.
- [ ] Confirm trusted-proxy handling produces HTTPS URLs.
- [ ] Verify separate user/admin cookies are Secure and have the intended attributes.
- [ ] Verify generated PayPal return/cancel URLs use the public HTTPS origin.

The Dockerfile does not define Docker `HEALTHCHECK`; Railway's HTTP check is the documented mechanism.

## 6. Mail and WhatsApp

`MAIL_MAILER=log` does not send email.

- [ ] Configure an approved transactional SMTP/mail provider.
- [ ] Verify password reset and email-verification delivery.
- [ ] Review sender-domain authentication and spam placement.
- [ ] Set `TRAVEL_WHATSAPP_NUMBER` only for an approved number using international digits.
- [ ] Leave the number empty when no approved contact exists.
- [ ] Confirm no copy implies direct booking or travel payment.

## 7. Payment Sandbox

Keep production flags disabled:

```env
MIDTRANS_IS_PRODUCTION=false
PAYPAL_IS_PRODUCTION=false
```

- [ ] Add sandbox credentials through environment secrets.
- [ ] Register `POST /payments/webhook/midtrans` with Midtrans.
- [ ] Register `POST /payments/webhook/paypal` with PayPal and set the matching `PAYPAL_WEBHOOK_ID`.
- [ ] Test checkout using official sandbox instruments and no real money.
- [ ] Confirm amount, currency, reference, redirect, return, and callback behavior.
- [ ] Replay an event and confirm no duplicate financial or inventory effect.
- [ ] Test terminal failure/refund handling and exactly-once stock restoration.
- [ ] Verify the manually configured PayPal exchange rate before demonstration.
- [ ] Redact credentials, personal data, signatures, and raw provider payloads from evidence.

## 8. Worker and Scheduler

- [ ] Create a worker service with `sh railway/run-worker.sh` only if asynchronous work requires it.
- [ ] Keep the database queue connection unless the script and documentation are changed together.
- [ ] Create a scheduler service with `sh railway/run-cron.sh` if scheduled pruning must run.
- [ ] Confirm `payments:prune-payloads` executes at the intended retention boundary.
- [ ] Review `failed_jobs`, scheduler output, and service logs.

## 9. Application Smoke Test

- [ ] `/` renders in Indonesian and English.
- [ ] Destination search/filter/detail work.
- [ ] User registration, verification, login, and password reset work.
- [ ] User/admin sessions remain independent.
- [ ] Verified users can manage owned profile/address data and submit one review.
- [ ] Shop, cart, address-aware checkout, and payment retry work.
- [ ] Users cannot read other users' orders or addresses.
- [ ] Admin can inspect users and manage destinations, souvenirs, inventory, and valid order transitions.
- [ ] Duplicate checkout and webhook delivery do not create duplicate effects.
- [ ] Uploads survive restart/redeploy.
- [ ] `/up` remains healthy and logs contain no raw secrets/provider payloads.

## 10. Evidence

- [ ] Public URL and deployed commit SHA.
- [ ] Service status and `/up` result.
- [ ] Migration output with secrets redacted.
- [ ] Media persistence before/after redeploy.
- [ ] Mail delivery evidence with personal data redacted.
- [ ] Sandbox provider IDs and callbacks with sensitive fields redacted.
- [ ] CI/security workflow URLs for the deployed commit.
- [ ] Completed manual QA execution row.

Until these items exist, README and portfolio copy must continue to state that deployment and external integrations are unverified.

## 11. Rollback and Safety

- [ ] Record the deployed image/commit before every release.
- [ ] Back up database and media before destructive maintenance.
- [ ] Test restore steps with nonpersonal data.
- [ ] Roll back application code without blindly reversing schema migrations.
- [ ] Never use demo seeders as a recovery method.
- [ ] Keep payment production flags disabled until a dedicated go-live review passes.
- [ ] Define access, retention, monitoring, and incident ownership before accepting real users.

## Known Operational Limits

- Local-volume media supports one web replica, not horizontal scaling.
- Object storage, Redis, and production Sentry are not configured or validated.
- The worker script currently selects the database connection explicitly.
- Docker `HEALTHCHECK` and runtime `route:cache` are not configured.
- Provider sandbox and SMTP behavior require owner-controlled external accounts.
- This runbook documents preparation; it is not a claim of production readiness.
