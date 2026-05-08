# Sentry Error Tracking

This application uses the official `sentry/sentry-laravel` package with Laravel 12 style integration in `bootstrap/app.php`.

Sentry stays disabled unless a DSN is configured.

## Enable In Production

Set these environment variables in production:

```env
SENTRY_LARAVEL_DSN=https://<key>@<host>/<project>
SENTRY_RELEASE=<git-sha-or-release-id>
SENTRY_ENVIRONMENT=production
SENTRY_TRACES_SAMPLE_RATE=0.05
SENTRY_SEND_DEFAULT_PII=false
```

Notes:

- The installed package config uses `SENTRY_LARAVEL_DSN` and also supports `SENTRY_DSN` as a fallback.
- Keep the DSN unset or `null` in local and testing environments.
- This setup does not enable Sentry log forwarding by default.
- The `/up` health transaction is ignored.

## Safe Verification

After deploy, first verify the app still boots normally:

```bash
php artisan about
```

Then trigger one controlled test event only when production DSN is configured:

```bash
php artisan sentry:test
```

Confirm the event appears in Sentry, then stop there.

## Disable Again

To disable Sentry without removing the package:

```env
SENTRY_LARAVEL_DSN=null
```

or leave `SENTRY_LARAVEL_DSN` unset entirely.

After changing env values, refresh config on the next deploy:

```bash
php artisan config:clear
php artisan config:cache
```
