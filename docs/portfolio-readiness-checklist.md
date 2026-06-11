# Portfolio Readiness Checklist

Legend:

- `[x]` repository artifact exists and was verified locally.
- `[ ]` owner/manual action remains.

## Repository

- [x] Professional local-first README.
- [x] MIT license.
- [x] Project-specific Composer metadata.
- [x] Locked Composer/npm dependencies.
- [x] `.env.example` contains placeholders, not real secrets.
- [x] GitHub Actions CI workflow.
- [x] GitHub issue templates.
- [x] Documentation index.
- [ ] Owner action required: add a concise GitHub repository description/topics.
- [ ] Owner action required: clean/defer stale dependency PRs after review.

## Local Review

- [x] MySQL/MariaDB setup documented.
- [x] SQLite quick start documented.
- [x] Curated local demo seed documented.
- [x] Demo accounts documented and environment-guarded.
- [x] Media symlink requirement documented.
- [x] Mail/payment/WhatsApp limitations documented.

## Automated Quality

- [x] Feature/unit tests present.
- [x] Pint check configured.
- [x] PHPStan/Larastan configured.
- [x] Composer/npm audits configured.
- [x] Vite production build configured.
- [x] Current baseline recorded in testing summary.
- [ ] Owner action required: measure coverage with PCOV/Xdebug if a percentage is desired.
- [ ] Future: reduce PHPStan baseline.

## Portfolio Presentation

- [x] Case study exists.
- [x] Demo video script exists.
- [x] Screenshot checklist and naming standard exist.
- [x] Safe CV/portfolio positioning exists in README/case study.
- [ ] Owner action required: record demo video.
- [ ] Owner action required: capture complete screenshot set.
- [ ] Owner action required: add verified links to README.

## QA Evidence

- [x] Test case matrix exists.
- [x] Manual QA checklist exists.
- [x] Regression scenarios exist.
- [x] Evidence table/template exists.
- [x] Bug/ticket/incident samples exist and are labelled simulated.
- [ ] Owner action required: execute manual QA against the chosen environment.
- [ ] Owner action required: attach real redacted evidence.

## Deployment

- [x] Dockerfile and Railway scripts exist.
- [x] Migration is separated from normal runtime.
- [x] `/up` health endpoint is configured.
- [x] Production-safe environment values are documented.
- [x] Persistent media strategy is documented.
- [x] Demo seeders are blocked outside local/testing.
- [ ] Owner action required: create Railway services and database.
- [ ] Owner action required: mount media volume.
- [ ] Owner action required: run `migrate --force`.
- [ ] Owner action required: verify public HTTPS URL and `/up`.
- [ ] Owner action required: add real live-demo URL to README.

## External Integrations

- [x] Mail configuration requirements documented.
- [x] WhatsApp optional state documented.
- [x] Payment sandbox matrix documented.
- [x] Safe Postman collection/template exists.
- [ ] Owner action required: configure transactional mail and verify delivery.
- [ ] Owner action required: provide an approved WhatsApp number or keep CTA disabled.
- [ ] Owner action required: execute Midtrans sandbox validation.
- [ ] Owner action required: execute PayPal sandbox validation.
- [ ] Owner action required: record provider/webhook evidence.

## Final Publication Gate

- [ ] All P0 manual checks pass.
- [ ] README links point only to real evidence.
- [ ] No credentials or personal data are committed.
- [ ] Current CI on `main` is green.
- [ ] Known limitations remain visible.
- [ ] Resume/CV wording does not claim production usage, real payments, or travel booking.

