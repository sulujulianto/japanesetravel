# Postman and HTTP Checking Guide

## Scope

Japan Travel is a hybrid Laravel HTML application, not a public JSON API. Postman is useful for health checks, public HTML responses, negative authorization checks, and webhook contract tests. A real browser remains the correct tool for CSRF-protected form and Inertia journeys.

The included collection is a safe template. It has not been executed as current evidence and does not contain valid provider credentials or signed success events.

## Included Collection

Import:

```text
docs/postman/JapaneseTravel.postman_collection.json
```

## Local Environment

| Variable | Example | Sensitive? |
|---|---|---|
| `base_url` | `http://127.0.0.1:8000` | No |
| `place_slug` | `senso-ji-temple` | No |
| `order_id` | `1` | Potentially sensitive in public evidence |
| `midtrans_server_key` | private/blank | Yes |
| `paypal_webhook_id` | private/blank | Yes |

Use a private Postman environment. Never export populated secrets, session cookies, full signatures, addresses, or provider payloads.

## Safe Read Checks

### Health

```http
GET {{base_url}}/up
```

Expected: successful Laravel health response.

### Public HTML

```http
GET {{base_url}}/
GET {{base_url}}/places
GET {{base_url}}/place/{{place_slug}}
GET {{base_url}}/shop
GET {{base_url}}/cart
```

Expected: HTML response, normally `200`. The home page may be rendered through Inertia, while other pages may use Blade; the HTTP contract remains HTML.

### Protected routes without a session

```http
GET {{base_url}}/dashboard
GET {{base_url}}/orders
GET {{base_url}}/admin
```

Expected: redirect to the appropriate user or admin login page. A protected response body must not be returned.

## Negative Webhook Checks

### Invalid Midtrans signature

```http
POST {{base_url}}/payments/webhook/midtrans
Content-Type: application/json

{
  "order_id": "INVALID-EXAMPLE",
  "status_code": "200",
  "gross_amount": "10000.00",
  "transaction_status": "settlement",
  "transaction_id": "invalid-example",
  "signature_key": "not-a-valid-signature"
}
```

Expected: request rejected as an invalid signature. This proves a negative path only.

### Invalid PayPal verification

```http
POST {{base_url}}/payments/webhook/paypal
Content-Type: application/json

{
  "id": "INVALID-EVENT",
  "event_type": "CHECKOUT.ORDER.APPROVED",
  "resource": {
    "id": "INVALID-ORDER"
  }
}
```

Expected: verification fails. A valid event requires official sandbox headers, a matching webhook ID, and provider API access.

Never treat a manually constructed JSON body as proof of a successful signed provider callback.

## CSRF-Protected Journeys

Registration, login, review, cart mutations, checkout, profile/address changes, and admin CRUD require cookies and CSRF tokens. Inertia requests also use specific request/response headers.

Postman can reproduce these details, but doing so is brittle and provides less confidence than the existing feature tests plus browser QA. Do not disable CSRF, authorization, email verification, or session separation to simplify a manual request.

If a session check is necessary:

1. Start with a GET request to obtain the correct user or admin cookie.
2. Obtain a fresh CSRF token through the normal application flow.
3. Preserve user and admin cookie jars separately.
4. Submit the form using its real method and content type.
5. Confirm redirects, flash errors, and authorization behavior.
6. Delete the private environment after evidence capture.

## Suggested Collection Structure

- Health
- Public HTML
- Authentication redirects
- User session — private/manual
- Admin session — private/manual
- Payment webhooks — negative
- Payment provider sandbox — deferred
- Evidence notes

## Local-First Evidence Checklist

| Check | Status | Evidence requirement |
|---|---|---|
| `/up` local | Not executed | Date, commit, response status |
| Public pages | Not executed | Date, commit, representative response status |
| Unauthorized redirects | Not executed | Separate user/admin destinations |
| Invalid Midtrans signature | Not executed | Redacted request and rejection status |
| Invalid PayPal verification | Not executed | Redacted request and rejection status |

Do not replace `Not executed` with `Pass` until the request has been run against the named commit and evidence is recorded in `docs/qa/test-execution-evidence.md`.

## Deferred Provider Checks

The following are optional external-account work and are not blockers for the local-first portfolio release:

- configure private Midtrans sandbox credentials;
- register and validate a Midtrans notification endpoint;
- configure PayPal sandbox credentials and webhook ID;
- capture official PayPal verification headers;
- execute success, failure, amount mismatch, and duplicate-event scenarios;
- preserve only redacted provider evidence.

## Limitations

- The collection is not a substitute for browser/UI QA.
- It does not contain secrets or valid signed provider events.
- It does not prove that a payment account is configured.
- HTML assertions are intentionally limited.
- Authentication examples never bypass CSRF or authorization.
- Inertia component contracts are more reliably tested in PHPUnit than inferred from raw HTML requests.
