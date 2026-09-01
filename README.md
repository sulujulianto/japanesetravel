# Japan Travel

Japan Travel is a Laravel 12 reference application for destination discovery and souvenir commerce. It combines bilingual travel content, verified-user reviews, optional travel inquiries through WhatsApp, and an internal souvenir checkout flow. The repository is suitable for portfolio review while keeping product copy and operational boundaries realistic.

Travel services are inquiry-only: the application does not sell tickets or process travel bookings. Direct checkout and payment apply only to souvenir products.

## Portfolio Status

- **Live demo:** pending public deployment
- **Demo video:** pending recording
- **Current quality baseline:** verified through the automated commands in [Testing and Quality](#testing-and-quality)
- **Positioning:** production-oriented portfolio project with documented deployment and integration limitations

Public deployment, screenshots, payment sandbox validation, and video recording require owner-controlled external accounts. The repository includes checklists and evidence templates for those steps.

## Features

- Bilingual Indonesian/English destination catalog and detail pages.
- Verified-user destination reviews with application and database duplicate protection.
- Optional WhatsApp travel inquiry CTA.
- Souvenir catalog, cart, checkout, payment retry, and order history.
- Midtrans Snap and PayPal Checkout integration structure for sandbox validation.
- Separate user and admin authentication sessions.
- User dashboard, profile management, password reset, and email verification.
- Admin dashboard for revenue, orders, inventory, destinations, and souvenirs.
- Stock locking during checkout and compensation after gateway creation failure.
- Signed/idempotent payment webhook handling.
- Locale-aware dates, numbers, ratings, and IDR formatting.
- Media validation, dimension limits, WebP conversion, and restricted deletion paths.
- Responsive light and dark interfaces.

## Tech Stack

- PHP 8.3 and Laravel 12
- Blade, Tailwind CSS, Alpine.js, and Vite
- MySQL/MariaDB or SQLite
- Chart.js bundled through Vite
- Midtrans PHP SDK and PayPal REST integration
- PHPUnit, Laravel Pint, Larastan/PHPStan
- GitHub Actions CI
- Docker/Railway deployment assets

## Architecture Notes

- `routes/web.php` contains public, authenticated, admin, checkout, and webhook routes.
- `app/Services/Payments/` isolates Midtrans and PayPal gateway behavior behind a shared interface.
- User and admin authentication use separate guards and session cookie names.
- Order items store product snapshots so order history remains readable if a product changes.
- Payment webhook events use a unique provider/event identifier for idempotency.
- `app/Support/Format.php` centralizes locale-aware presentation formatting.
- `app/Support/Media.php` centralizes media storage, WebP conversion, and safe deletion.
- `app/Support/Brand.php` and `config/brand.php` centralize brand identity for Blade, Inertia, admin, and payment descriptions.
- Database-backed cache, session, and queue drivers are the documented deployment baseline.

See [Backend Technical Documentation](docs/backend-technical-documentation.md) for the detailed data flow and state transitions.
See [Design System and Rebranding Guide](docs/design-system-and-rebranding.md) for UI tokens, shell boundaries, and safe brand changes.

## Screenshots

The project includes curated visual evidence under [`docs/screenshots`](docs/screenshots).

| View | Screenshot |
|---|---|
| Homepage | [`01-homepage.png`](docs/screenshots/01-homepage.png) |
| Destination catalog | [`02-destinations.png`](docs/screenshots/02-destinations.png) |
| Destination detail | [`03-destination-detail.png`](docs/screenshots/03-destination-detail.png) |
| Souvenir shop | [`05-souvenir-shop.png`](docs/screenshots/05-souvenir-shop.png) |
| Cart | [`06-cart.png`](docs/screenshots/06-cart.png) |
| Admin dashboard | [`10-admin-dashboard.png`](docs/screenshots/10-admin-dashboard.png) |
| Mobile responsive view | [`14-mobile-responsive.png`](docs/screenshots/14-mobile-responsive.png) |
| Dark mode | [`15-dark-mode.png`](docs/screenshots/15-dark-mode.png) |

See [Screenshot Capture Checklist](docs/screenshots/README.md) for required views, filenames, viewport guidance, and README linking instructions. Do not add placeholder image links before the corresponding files exist.

## Local Requirements

- PHP 8.3 or newer
- Composer 2
- Node.js 20 LTS or newer
- npm
- MySQL/MariaDB, or SQLite for a quick local review
- PHP extensions:
  - `pdo_mysql` or `pdo_sqlite`
  - `gd` with WebP support
  - `fileinfo`
  - `curl`
  - `mbstring`
  - `openssl`
  - `xml`
  - `zip`
  - `intl` recommended; the formatter has a deterministic fallback
- Writable `storage/` and `bootstrap/cache/` directories

Docker is available for deployment parity but is not required for daily local development.

## Local Setup

Clone the repository and install locked dependencies:

```bash
git clone https://github.com/sulujulianto/japanesetravel.git
cd japanesetravel
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

### Option A: MySQL or MariaDB

Create a database such as `japantravel`, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=japantravel
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and the curated local demo seed:

```bash
php artisan migrate --seed
```

### Option B: SQLite Quick Start

```bash
touch database/database.sqlite
```

Set:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/japanesetravel/database/database.sqlite
```

Then run:

```bash
php artisan migrate --seed
```

> `migrate --seed` is for disposable local/testing data. `DemoSeeder` clears application tables and is guarded against staging/production execution.

Create the dedicated local visual-review accounts and sample orders:

```bash
php artisan db:seed --class=DevAccountSeeder
php artisan storage:link
```

Start Laravel and Vite in separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

For a compiled asset check:

```bash
npm run build
php artisan serve
```

## Demo Accounts

These credentials are local/testing-only and are rejected by their seeder in other environments.

| Role | Email | Password | Login |
|---|---|---|---|
| User | `user.demo@japantravel.test` | `Password123!` | `/login` |
| Admin | `admin.demo@japantravel.test` | `Password123!` | `/admin/login` |

The curated dataset contains 10 destinations, 10 souvenirs, and 15 reviews. Local demo images are sourced from Unsplash and documented in [`docs/asset-sources.md`](docs/asset-sources.md); replace them through the admin interface with assets you own or are licensed to use.

## Testing and Quality

Do not copy a test count into portfolio claims without rerunning the complete suite. The commands below are the source of truth for the current branch.

```bash
composer validate
composer audit
npm audit --audit-level=high
npm run build
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --no-progress
```

GitHub Actions runs dependency installation, asset build, formatting, static analysis, tests, and dependency audits.

No coverage percentage is claimed because a coverage driver is not part of the default lightweight setup. See [Testing Summary](docs/testing-summary.md) for coverage instructions and current gaps.

## Optional Integrations

### Mail

Local development uses `MAIL_MAILER=log`; reset and verification messages are written to `storage/logs/laravel.log`. Real delivery requires a valid SMTP or transactional provider configuration.

### WhatsApp Travel Inquiry

`TRAVEL_WHATSAPP_NUMBER` is optional and must contain international digits without `+` or spaces. The CTA stays disabled when it is empty. It does not create a travel booking or payment.

### Payment Sandbox

Catalog, admin, and cart review do not require gateway credentials. Checkout testing requires Midtrans or PayPal sandbox credentials, a public HTTPS callback URL, and registered webhook configuration. Automated tests mock provider calls and do not prove that an external account is configured correctly.

### Media

Local uploads use the `public` media disk and require `php artisan storage:link`. Railway deployment with local media requires a persistent volume mounted at `/var/www/html/storage/app/public`.

## Deployment Notes

The repository is deployment-ready in the sense that it contains a multi-stage Dockerfile, Railway runtime scripts, environment documentation, and a manual staging checklist. It is **not** presented as an active or fully production-proven deployment.

Key rules:

- Run migrations separately with `php artisan migrate --force`.
- Do not run `DemoSeeder`, `DevAccountSeeder`, or `migrate --seed` in staging/production.
- Configure Railway health checks against `/up`.
- Mount persistent media storage before testing uploads.
- Keep payment flags in sandbox mode until provider validation is complete.
- Configure a real mail provider before testing password reset or verification delivery.

See [Deployment Checklist](docs/deployment-checklist.md).

## Security Notes

- User and admin authentication use separate guards and session cookies.
- Standard forms use CSRF protection; only payment webhook endpoints are exempted.
- Login, review, and webhook routes are rate-limited.
- Payment webhooks verify provider signatures and record idempotency events.
- Checkout locks stock and restores it when gateway creation fails.
- Media uploads are limited to 2 MB and 6000 x 6000 pixels before processing.
- Media deletion is restricted to approved upload directories.
- A database constraint enforces one review per user and destination.
- Demo seeders are restricted to local/testing environments.

These controls reduce common portfolio risks but do not replace a real security review, provider validation, monitoring, backups, or operational testing.

## Known Limitations

- No live deployment, complete screenshot set, or recorded demo is currently claimed.
- Travel ticketing and direct travel booking are outside project scope.
- Payment integrations require external sandbox configuration.
- Real email delivery is not configured by default.
- PayPal IDR conversion uses a manually configured exchange rate.
- Media persistence requires a Railway Volume; S3 support is not installed.
- The local-volume deployment model is intended for a single web replica.
- There is no measured code coverage percentage.
- Postman artifacts are request templates, not proof of executed provider tests.
- The PHPStan baseline contains known type-analysis debt documented for future cleanup.

## Roadmap

- Complete owner-controlled staging deployment and smoke-test evidence.
- Record the 1–2 minute project walkthrough.
- Add desktop/mobile/light/dark screenshots.
- Execute Midtrans and PayPal sandbox matrices.
- Configure transactional email and verify deliverability.
- Add measured coverage using PCOV or Xdebug on a suitable environment.
- Reduce the PHPStan baseline incrementally.
- Evaluate object storage before multi-replica deployment.

## Portfolio Evidence and Documentation

- [Documentation Index](docs/README.md)
- [Deployment Checklist](docs/deployment-checklist.md)
- [Demo Video Script](docs/demo-video-script.md)
- [Testing Summary](docs/testing-summary.md)
- [Case Study](docs/case-study-japanese-travel.md)
- [QA Test Cases](docs/qa/test-cases-japanese-travel.md)
- [Manual QA Checklist](docs/qa/manual-qa-checklist.md)
- [Postman/API Checking Guide](docs/postman-api-checking.md)
- [Troubleshooting Case Studies](docs/troubleshooting-case-studies.md)
- [SQL Inspection Examples](docs/sql-inspection-examples.md)
- [Project Workflow](docs/project-workflow.md)
- [GitHub Issues Backlog](docs/github-issues-backlog.md)
- [Portfolio Readiness Checklist](docs/portfolio-readiness-checklist.md)
- [Backend Technical Documentation](docs/backend-technical-documentation.md)

## License

This project is available under the [MIT License](LICENSE).
