# Security Baseline — 2026-08-23

This record separates verified controls from planned controls. It is evidence
for the portfolio, not a claim of OWASP ASVS certification or a third-party
security assessment.

## Scope

- Branch: `work/vue-commerce-phase-0`
- Baseline commit: `f92a29b87407e4f1bb3b84036abfff8f5314d258`
- Dependency remediation commit: `f4e8d04`
- CI and security workflow commit: `6cd88d6`
- GitHub Actions CI run: `32643309689`
- GitHub Actions Security run: `32643309728`

## Dependency Remediation

The 2026-08-23 baseline audit found 18 Composer advisories affecting four
packages and eight npm vulnerabilities (seven high and one low). The lockfiles
were updated without a major-version framework migration.

| Package | Previous | Verified update |
|---|---:|---:|
| `laravel/framework` | 12.61.0 | 12.67.0 |
| `guzzlehttp/guzzle` | 7.11.0 | 7.15.3 |
| `guzzlehttp/psr7` | 2.11.0 | 2.13.0 |
| `league/commonmark` | 2.8.2 | 2.10.0 |
| `axios` | 1.16.0 | 1.19.0 |
| `concurrently` | 9.2.1 | 9.2.4 |
| `esbuild` | 0.27.7 | 0.28.2 |
| `form-data` | 4.0.5 | 4.0.6 |
| `nanoid` | 3.3.11 | 3.3.18 |
| `postcss` | 8.5.14 | 8.5.26 |
| `shell-quote` | 1.8.4 | 1.9.0 |
| `vite` | 7.3.3 | 7.3.6 |

After remediation, `composer audit --locked` and
`npm audit --audit-level=high` reported no known vulnerability.

## Verified Gates

| Gate | Result | Evidence |
|---|---|---|
| Composer metadata and locked dependency audit | Pass | CI run `32643309689` |
| npm clean install, audit, and production build | Pass | CI run `32643309689` |
| Pint and PHPStan/Larastan level 6 | Pass | CI run `32643309689` |
| PHPUnit on SQLite | 132 tests / 633 assertions | CI run `32643309689` |
| PHPUnit on MariaDB 10.11 | Pass | CI run `32643309689` |
| PHPUnit on MariaDB 11.8 | Pass | CI run `32643309689` |
| Gitleaks full-history scan | Pass | Security run `32643309728` |
| CodeQL JavaScript/TypeScript | Pass | Security run `32643309728` |

The MariaDB jobs create an isolated `japantravel_test` database and run
`migrate:fresh`; they never connect to a developer or production database.

## Known Limitations

- Dependency Review is pull-request-only and was skipped on the push run. It
  must pass before this phase can be merged.
- CodeQL does not analyze PHP. PHP currently uses Larastan/PHPStan, tests, and
  dependency auditing; additional PHP-focused SAST remains planned.
- PHPStan uses a committed baseline. A green result means no new findings
  outside that accepted debt, not zero total findings.
- Payment provider sandbox contracts, webhook concurrency, real parallel stock
  races, browser E2E, accessibility, DAST, container scanning, MFA, Turnstile,
  CSP nonces, and ASVS evidence mapping remain planned work.
- A passing secret scan is not proof that an exposed credential is safe. Any
  confirmed real credential must be revoked or rotated before history cleanup.

## Interpretation

These results establish a reproducible dependency and database baseline. They
do not yet establish production readiness for the commerce subsystem. Order,
payment, inventory, authorization, upload, session, and data-retention
invariants must be implemented and tested in subsequent phases.
