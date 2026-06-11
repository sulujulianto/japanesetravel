# Project and GitHub Workflow

## Goal

Keep portfolio work reviewable, reversible, and easy to explain in an interview.

## Suggested Board

**Owner action required:** create a GitHub Project in the repository/owner account.

Suggested columns:

1. Backlog
2. Ready
3. In Progress
4. Review
5. Blocked
6. Done

Suggested custom fields:

- Priority: P0, P1, P2
- Area: deployment, docs, QA, payment, media, observability, performance, refactor
- Evidence: not started, partial, complete
- Owner

### GitHub UI Steps

1. Open the repository on GitHub.
2. Select **Projects** → **New project**.
3. Choose a board layout.
4. Create the columns and fields above.
5. Copy issues from [GitHub Issues Backlog](github-issues-backlog.md).
6. Link pull requests to their issue using `Closes #<issue>`.
7. Move an item to Done only when its acceptance criteria and evidence are present.

## Branch Naming

```text
docs/<short-topic>
qa/<short-topic>
fix/<short-topic>
feat/<short-topic>
chore/<short-topic>
```

Examples:

```text
docs/railway-staging-evidence
qa/payment-sandbox-matrix
fix/media-volume-path
chore/phpstan-baseline-reduction
```

## Pull Request Checklist

- [ ] Scope is linked to one issue.
- [ ] Business behavior changes are explicitly described.
- [ ] Database migration impact is documented.
- [ ] Tests cover the changed risk.
- [ ] `git diff --check` passes.
- [ ] Build, tests, Pint, PHPStan, Composer audit, and npm audit pass.
- [ ] Screenshots are included for visual changes.
- [ ] Secrets and personal data are absent.
- [ ] README/docs are updated if operational behavior changes.
- [ ] Reviewer can identify rollback implications.

## Definition of Done

An item is Done when:

- Acceptance criteria are met.
- Automated/manual verification is recorded.
- Documentation reflects actual behavior.
- External owner actions are either completed with evidence or remain explicitly open.
- No claim exceeds the available proof.

## Dependency Updates

Handle dependency upgrades on a dedicated maintenance branch. Major Tailwind, Vite plugin, framework, or payment SDK updates require a full build/test/UI regression cycle. A clean audit is not a reason to merge every Dependabot PR immediately.

## Release Notes

For portfolio milestones, record:

- Commit SHA
- Test baseline
- Known limitations
- Manual checks completed
- Deployment URL, if real
- Screenshot/video evidence links, if real

