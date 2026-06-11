# Test Execution Evidence

Do not pre-fill Pass/Fail results. Add a row only when a person actually executes the scenario.

## Execution Table

| Test ID | Scenario | Date | Tester | Environment | Result | Evidence link/path | Notes |
|---|---|---|---|---|---|---|---|
| Example only | Remove this row when recording real evidence | YYYY-MM-DD | Name | Local/Staging + commit SHA | Not executed | — | Template row |

Allowed result values:

- Pass
- Fail
- Blocked
- Not executed

## Screenshot Evidence Checklist

- [ ] Homepage desktop
- [ ] Destination catalog/detail
- [ ] Review form and duplicate feedback
- [ ] Shop/cart/checkout
- [ ] User order list/detail
- [ ] Admin login/dashboard/order/catalog
- [ ] Mobile 320–390 px
- [ ] Dark mode
- [ ] `/up` and deployment health
- [ ] Media before/after redeploy
- [ ] Mail delivery, redacted
- [ ] Payment sandbox callback/webhook, redacted

## Naming Convention

```text
<test-id>-<short-scenario>-<yyyy-mm-dd>.<png|txt|json>
```

Examples:

```text
CHECKOUT-002-stock-restored-2026-06-11.png
MEDIA-001-redeploy-url-2026-06-11.txt
```

Store UI evidence under:

```text
docs/screenshots/evidence/
```

Do not commit:

- Environment files
- Provider credentials
- Session cookies
- Full webhook signatures
- Personal email/address data
- Unredacted provider payloads

## Referencing Evidence

README should show only polished product screenshots. Detailed QA evidence belongs here or in an issue/PR.

Case study example:

```markdown
Manual staging evidence: [MEDIA-001 persistence check](test-execution-evidence.md)
```

GitHub issue example:

```markdown
Evidence: `docs/screenshots/evidence/MEDIA-001-redeploy-url-2026-06-11.txt`
```
