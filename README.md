# JapanTravel

JapanTravel is a Laravel-based Japanese travel discovery and souvenir commerce project. It combines a bilingual destination catalog, travel inquiries through WhatsApp, and an internal souvenir checkout flow in one portfolio application.

Travel services are not booked or paid for directly through the website. Online checkout is limited to souvenir products.

![JapanTravel interface preview](japantravel/japanese-travel.jpg)

## Key Features

- Destination catalog, detail pages, and verified-user reviews.
- Optional WhatsApp travel inquiry CTA on destination pages.
- Souvenir catalog, cart, checkout, and user order history.
- User dashboard, profile management, and email verification flows.
- Separate user and admin authentication sessions.
- Admin dashboard with revenue, order, inventory, and catalog summaries.
- Admin management for destinations, souvenirs, orders, and low stock.
- Indonesian and English localization with locale-aware dates and IDR formatting.
- Midtrans Snap and PayPal Checkout integration structure for sandbox testing.
- Image upload validation, dimension limits, and WebP conversion.
- Light and dark themes across public, user, and admin interfaces.

## Tech Stack

- PHP 8.3 and Laravel 12
- Blade, Tailwind CSS, Alpine.js, and Vite
- MySQL/MariaDB or SQLite
- Chart.js
- Midtrans PHP SDK and PayPal REST integration
- PHPUnit, Laravel Pint, and Larastan/PHPStan

## Screenshots

The repository currently includes the interface preview above. A fuller project gallery can be added under `docs/screenshots/` with:

- Homepage
- Destination catalog
- Destination detail
- Souvenir shop
- Cart and checkout
- User order history
- Admin dashboard
- Admin destination and souvenir management

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
  - `intl` is recommended; the presentation formatter has a deterministic fallback
- Writable `storage/` and `bootstrap/cache/` directories

## Local Setup

Clone the repository and install locked dependencies:

```bash
git clone <repository-url>
cd japanesetravel
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

### Option A: MySQL or MariaDB

Create a local database, for example `japantravel`, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=japantravel
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and the curated demo seed:

```bash
php artisan migrate --seed
```

### Option B: SQLite Quick Start

Create the SQLite database:

```bash
touch database/database.sqlite
```

Set the connection and use the absolute path to the new file:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/japanesetravel/database/database.sqlite
```

Then run:

```bash
php artisan migrate --seed
```

> **Local data warning:** `migrate --seed` calls `DemoSeeder`, which clears application tables before loading demo data. It is guarded to `local` and `testing` environments and must not be used for staging or production data.

Create the dedicated visual-review accounts and sample order history:

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

Use `npm run dev` while developing. To verify a compiled asset build instead:

```bash
npm run build
php artisan serve
```

## Demo Accounts

These credentials are only for local visual review and are rejected by their seeder outside `local` or `testing`.

**User**

- Email: [user.demo@japantravel.test](mailto:user.demo@japantravel.test)
- Password: `Password123!`
- Login: `/login`

**Admin**

- Email: [admin.demo@japantravel.test](mailto:admin.demo@japantravel.test)
- Password: `Password123!`
- Login: `/admin/login`

The demo catalog contains 10 destinations, 10 souvenirs, and 15 reviews. Images are intentionally not bundled; upload assets that you own or are licensed to use through the admin interface.

## Testing and Quality Checks

Current verified baseline: **129 tests and 509 assertions**.

```bash
composer validate
composer audit
npm audit --json
npm run build
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --no-progress
```

GitHub Actions also runs the project build, test suite, static analysis, formatting check, and dependency audits.

## Optional Integrations

### Mail

Local development defaults to `MAIL_MAILER=log`. Password reset and verification messages are written to `storage/logs/laravel.log` rather than sent to an inbox. Real delivery requires a configured SMTP or transactional mail provider.

### WhatsApp Travel Inquiry

`TRAVEL_WHATSAPP_NUMBER` is optional and must use international digits without `+` or spaces. The destination CTA remains informational and disabled when the value is empty. The application does not process travel bookings.

### Payment Sandbox

Midtrans and PayPal credentials are optional for browsing the catalog, using the admin area, and reviewing the cart. Testing checkout requires valid sandbox credentials, provider webhook configuration, and a reachable callback URL. Automated tests mock external gateway communication and do not prove that a provider account is live-ready.

### Media

Demo images are not included. Local uploads use the `public` media disk, require `php artisan storage:link`, and should use legal or properly licensed assets.

## Security and Demo-Data Notes

- User and admin authentication use separate guards and session cookies.
- Standard web forms use CSRF protection; payment webhook exceptions are limited to webhook endpoints.
- Payment webhooks verify provider signatures and record idempotency events.
- Media uploads are limited to 2 MB and 6000 x 6000 pixels before WebP conversion.
- Media deletion is restricted to the approved destination and souvenir upload directories.
- A database constraint enforces one review per user and destination.
- Demo seeders are restricted to `local` and `testing` environments.
- Never commit real mail, payment, database, or monitoring credentials.

## Project Scope and Limitations

- Travel services use an inquiry flow only; ticketing and direct travel booking are not implemented.
- Direct checkout applies only to souvenir products.
- Payment and mail integrations require external sandbox or provider configuration.
- Demo media is intentionally omitted.
- The Railway deployment material documents a proposed portfolio/staging setup; it does not claim an active production deployment.
- S3-compatible media storage is not enabled in the current dependency set.

## Additional Documentation

See [Backend Technical Documentation](docs/backend-technical-documentation.md) for architecture, payment state handling, webhook behavior, security controls, media storage, and Railway deployment notes.

## License

This project is available under the [MIT License](LICENSE).
