# Manual QA Checklist

Record execution in [Test Execution Evidence](test-execution-evidence.md). Empty boxes mean not yet verified.

## Environment

- [ ] Commit SHA recorded.
- [ ] Environment URL and database type recorded.
- [ ] Browser/version and viewport recorded.
- [ ] Sandbox credentials used where applicable.
- [ ] No production/customer data used.

## Public Pages

- [ ] Homepage renders without console errors.
- [ ] Destination preview links correctly.
- [ ] `/places` search, rating filter, sort, and pagination work.
- [ ] Destination detail handles image and no-image states.
- [ ] `/shop` filters/sorts and product cards work.
- [ ] Footer links are correct.
- [ ] No travel-booking/payment claim appears.

## Auth

- [ ] Registration validates required/duplicate fields.
- [ ] Login succeeds with valid credentials.
- [ ] Invalid login is rate-limited and translated.
- [ ] Logout invalidates the session.
- [ ] Password reset mail/log flow works.
- [ ] Email verification link works.
- [ ] Unverified user cannot access verified routes.

## User Dashboard

- [ ] Dashboard metrics match the user's orders.
- [ ] Profile update works.
- [ ] Password update requires current password.
- [ ] Account deletion requires password.
- [ ] User navigation works on desktop/mobile.

## Destination and Review

- [ ] Verified user can submit a 1–5 rating.
- [ ] Guest/unverified user cannot submit.
- [ ] Duplicate review is rejected with friendly feedback.
- [ ] Same user can review a different destination.
- [ ] ID/EN content and count labels are correct.

## Shop and Cart

- [ ] Add-to-cart works for in-stock product.
- [ ] Out-of-stock product cannot be added.
- [ ] Quantity cannot exceed stock.
- [ ] Deleted/stale products are removed safely.
- [ ] Item subtotal and total are correct.
- [ ] Remove item works.
- [ ] Empty cart state is clear.

## Checkout and Payment

- [ ] Empty cart cannot checkout.
- [ ] Provider values remain Midtrans/PayPal.
- [ ] Checkout creates correct order/item/payment records.
- [ ] Gateway creation failure restores stock.
- [ ] Successful sandbox payment changes status through valid callback/webhook.
- [ ] Duplicate webhook has no duplicate effect.
- [ ] Failed/cancelled webhook does not corrupt a terminal order.
- [ ] Retry is blocked for completed/cancelled/other-user orders.
- [ ] No real money used.

## Admin

- [ ] Admin login works separately from user login.
- [ ] Normal user cannot access admin pages.
- [ ] Dashboard metrics/charts render in ID/EN.
- [ ] Destination create/edit/delete works.
- [ ] Souvenir create/edit/delete works.
- [ ] Low-stock filter/restock works.
- [ ] Order filters work.
- [ ] Valid status transitions succeed.
- [ ] Invalid/terminal status transitions fail safely.
- [ ] Admin mobile navigation remains usable at 320–360 px.

## Media Upload

- [ ] Valid JPEG/PNG uploads and converts to WebP.
- [ ] GIF/WebP accepted according to existing validation.
- [ ] Over-2MB upload is rejected.
- [ ] Over-6000×6000 upload is rejected.
- [ ] Replacement removes the old allowed media path.
- [ ] Unsafe/outside path is not deleted.
- [ ] Upload survives staging restart/redeploy.

## Localization

- [ ] ID/EN toggle persists through cookie.
- [ ] Headings and flash messages translate.
- [ ] IDR format differs correctly between ID and EN presentation.
- [ ] Dates/times/ratings follow locale.
- [ ] Review/item singular/plural labels are correct.
- [ ] Database names/descriptions use the appropriate translation.

## Security Basics

- [ ] CSRF rejection occurs for normal form POST without token.
- [ ] Webhook routes reject invalid signatures.
- [ ] User cannot access another user's order.
- [ ] User/admin sessions remain isolated.
- [ ] Security headers are present.
- [ ] `.env` and credentials are not publicly served.
- [ ] Logs do not expose secrets.

## Responsive UI

- [ ] 320 px width has no horizontal page overflow.
- [ ] Mobile menus default closed.
- [ ] Tables use mobile cards or controlled overflow.
- [ ] Forms and buttons remain reachable.
- [ ] Footer follows content and leaves no trailing gap.
- [ ] Light and dark text remain readable.

## Deployment Smoke Test

- [ ] `/up` returns success.
- [ ] APP URL and generated links use HTTPS.
- [ ] Secure cookies are enabled.
- [ ] Migration ran without demo seed.
- [ ] Media volume is mounted and persistent.
- [ ] Mail delivery is proven or clearly disabled.
- [ ] Sandbox payment callbacks reach the deployment.
- [ ] Worker/scheduler state matches documented configuration.

