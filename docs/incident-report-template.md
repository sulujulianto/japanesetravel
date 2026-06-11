# Incident Report Template

Use this template for real incidents. Keep customer data and credentials out of the document.

## Incident Details

- **Incident title:**
- **Date/time and timezone:**
- **Severity:** SEV-1 / SEV-2 / SEV-3 / SEV-4
- **Affected area:**
- **Status:** Investigating / Mitigated / Resolved
- **Incident owner:**

## Summary

Describe what happened in clear, non-blaming language.

## Timeline

| Time | Event/action |
|---|---|
| | |

## Impact

- Affected users:
- Duration:
- Data integrity impact:
- Financial/payment impact:
- Security/privacy impact:

## Root Cause

Explain the technical and process causes. Distinguish confirmed facts from assumptions.

## Resolution

Describe the mitigation and permanent fix.

## Corrective Actions

| Action | Priority | Owner | Due date | Status |
|---|---|---|---|---|
| | | | | |

## Preventive Actions

List monitoring, test, documentation, backup, or process changes.

## Follow-Up Owner

- Name/role:
- Review date:
- Evidence links:

---

# Simulated Portfolio Incident

> **This is a simulated portfolio incident report. It is not a claim that a real production outage occurred.**

## Incident Details

- **Incident title:** Uploaded images returned 404 after redeploy due to missing persistent storage
- **Date/time:** Simulated
- **Severity:** SEV-3
- **Affected area:** Destination and souvenir media
- **Status:** Resolved in proposed staging configuration
- **Incident owner:** Portfolio project owner

## Summary

Media uploaded through the admin interface was stored on the container's local `storage/app/public` directory. After redeploy, the container filesystem was replaced. Database records retained the relative paths, causing public image URLs to return `404`.

## Timeline

| Time | Event/action |
|---|---|
| T+0 | Admin uploads destination and souvenir images. |
| T+20m | A new application deploy replaces the container. |
| T+25m | Reviewer reports broken image URLs. |
| T+35m | Database paths and filesystem are compared. |
| T+45m | Missing persistent volume is identified. |
| T+70m | Volume is mounted and media is re-uploaded. |
| T+90m | Restart/redeploy persistence check passes. |

## Impact

- Uploaded images became unavailable.
- Destination/product records remained intact.
- No payment or order data was affected.
- No security/privacy impact was identified.

## Root Cause

The application correctly used the public media disk, but the deployment did not provide persistent storage for `storage/app/public`. Documentation/runtime assumptions were incomplete at deployment time.

## Resolution

- Mount a Railway Volume at `/var/www/html/storage/app/public`.
- Preserve the `public/storage` symlink.
- Re-upload approved assets.
- Verify URLs before and after a restart/redeploy.

## Corrective Actions

| Action | Priority | Owner | Due date | Status |
|---|---|---|---|---|
| Add volume to staging | P0 | Owner | Before public demo | Pending owner action |
| Execute persistence smoke test | P0 | Owner/QA | Before public demo | Pending |
| Document backup/export | P1 | Owner | Before long-term use | Pending |
| Evaluate object storage | P2 | Owner | Before multi-replica scaling | Backlog |

## Preventive Actions

- Keep persistence in the deployment checklist.
- Capture restart/redeploy evidence.
- Avoid multiple replicas with local volume storage.
- Add object storage only in a dedicated tested change.

## Follow-Up Owner

Portfolio project owner; evidence should be linked from the deployment checklist and QA execution table.

