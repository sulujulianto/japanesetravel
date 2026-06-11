# Postman and HTTP Checking Guide

JapanTravel is primarily a server-rendered Laravel web application, not a public JSON API. Postman is useful for health checks, public route responses, negative authorization checks, and webhook contract validation. Browser sessions remain the better tool for CSRF-protected form journeys.

## Included Collection

Import:

```text
docs/postman/JapaneseTravel.postman_collection.json
```

The collection contains safe examples only. It has not been executed as evidence.

## Suggested Environment Variables

| Variable | Example | Secret? |
|---|---|---|
| `base_url` | `http://127.0.0.1:8000` | No |
| `place_slug` | `senso-ji-temple` | No |
| `order_id` | `1` | Potentially sensitive in public evidence |
| `midtrans_server_key` | blank placeholder | Yes; do not commit |
| `paypal_webhook_id` | blank placeholder | Yes; do not commit |

Use a private Postman environment for credentials. Do not export populated secrets.

## Safe Checks

### Health

```http
GET {{base_url}}/up
```

Expected: successful response from Laravel's health endpoint.

### Public Pages

```http
GET {{base_url}}/
GET {{base_url}}/places
GET {{base_url}}/place/{{place_slug}}
GET {{base_url}}/shop
GET {{base_url}}/cart
```

Expected: HTML response, normally `200`.

### Protected Routes Without a Session

```http
GET {{base_url}}/orders
GET {{base_url}}/admin
```

Expected: redirect to the relevant login page, not protected content.

### Invalid Midtrans Signature

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

Expected: `400` with an invalid-signature response. This is a negative test only.

### Invalid PayPal Signature

```http
POST {{base_url}}/payments/webhook/paypal
Content-Type: application/json

{
  "id": "INVALID-EVENT",
  "event_type": "CHECKOUT.ORDER.COMPLETED",
  "resource": {
    "id": "INVALID-ORDER"
  }
}
```

Expected: verification fails. A valid test requires official sandbox headers, webhook ID, and provider API access.

## CSRF-Protected Forms

Registration, login, review, cart, checkout, profile, and admin CRUD forms require cookies and CSRF tokens. Postman can reproduce them by:

1. Sending a GET request to obtain session/XSRF cookies.
2. Parsing the form token from HTML or using the XSRF cookie where applicable.
3. Sending cookies and token with the form request.

This is possible but brittle for a Blade application. Existing feature tests are stronger repeatable evidence for these contracts.

## Suggested Collection Structure

- Health
- Public Pages
- Authentication Redirects
- Payment Webhooks — Negative
- Payment Webhooks — Sandbox Owner Action
- Evidence Notes

## Sandbox Owner Actions

- [ ] Add private Midtrans sandbox variables.
- [ ] Generate a real signed notification using provider-supported tools.
- [ ] Add private PayPal sandbox credentials/webhook ID.
- [ ] Capture official PayPal webhook headers.
- [ ] Run success/failure/duplicate scenarios.
- [ ] Redact secrets before exporting screenshots or logs.

## Evidence Checklist

| Check | Executed date | Result | Evidence |
|---|---|---|---|
| `/up` | Pending | Not executed | Owner action required |
| Public pages | Pending | Not executed | Owner action required |
| Unauthorized redirect | Pending | Not executed | Owner action required |
| Invalid Midtrans signature | Pending | Not executed | Owner action required |
| Midtrans sandbox success/replay | Pending | Not executed | Owner action required |
| Invalid PayPal signature | Pending | Not executed | Owner action required |
| PayPal sandbox success/replay | Pending | Not executed | Owner action required |

Do not replace “Not executed” with Pass until the request has actually been run and evidence saved.

## Limitations

- The collection is not a substitute for browser/UI QA.
- It does not include secrets or valid signed provider events.
- It does not prove a payment account is configured.
- HTML assertions are intentionally minimal.
- Authentication examples do not bypass CSRF or authorization.

