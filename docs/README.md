# Japan Travel Documentation

[Bahasa Indonesia](../README.md) · [English](../README.en.md)

This directory is the documentation hub for Japan Travel. It separates recruiter-facing evidence, engineering references, QA records, operational runbooks, and future work so that planned capabilities are not confused with verified results.

## Release Scope

The current target is a **local-first portfolio release**:

- the repository can be installed and reviewed locally;
- automated quality gates are reproducible;
- screenshots and engineering documents provide review evidence;
- public deployment and external provider validation are intentionally deferred;
- no production usage, real payment, or travel-booking claim is made.

## Start Here

| Document | Audience | Purpose |
|---|---|---|
| [Project README — Indonesian](../README.md) | Recruiters and developers | Primary project overview, setup, architecture, and verified status |
| [Project README — English](../README.en.md) | International reviewers | English project overview |
| [Case Study](case-study-japanese-travel.md) | Recruiters and interviewers | Problem framing, design decisions, trade-offs, and outcomes |
| [Portfolio Readiness Checklist](portfolio-readiness-checklist.md) | Maintainer | Local-first publication gate and deferred items |
| [Testing Summary](testing-summary.md) | Engineers | Test coverage, commands, evidence rules, and known gaps |
| [Backend Technical Documentation](backend-technical-documentation.md) | Engineers | Domain model, transactions, payment flow, and operational boundaries |
| [Design System and Rebranding](design-system-and-rebranding.md) | Frontend contributors | Shared tokens, branding, and hybrid frontend boundaries |

## QA and Evidence

| Document | Purpose |
|---|---|
| [Test Execution Evidence](qa/test-execution-evidence.md) | Commit-bound automated and manual execution records |
| [Test Cases](qa/test-cases-japanese-travel.md) | Functional test matrix |
| [Manual QA Checklist](qa/manual-qa-checklist.md) | Human regression checks |
| [Regression Scenarios](qa/regression-test-scenarios.md) | High-risk regression coverage |
| [Screenshot Evidence](screenshots/README.md) | Naming, provenance, and refresh rules for visual evidence |
| [Bug Report Samples](qa/bug-report-sample.md) | Clearly labelled simulated defect examples |
| [Ticket Documentation Samples](ticket-documentation-samples.md) | Clearly labelled simulated delivery records |

## Integration and Operations References

These files document supported procedures; they are **not proof that an external environment or provider account has been exercised**.

- [Deployment Checklist](deployment-checklist.md)
- [Demo Video Script](demo-video-script.md)
- [Postman/API Checking Guide](postman-api-checking.md)
- [Safe Postman Collection](postman/JapaneseTravel.postman_collection.json)
- [Troubleshooting Case Studies](troubleshooting-case-studies.md)
- [SQL Inspection Examples](sql-inspection-examples.md)
- [Incident Report Template and Simulated Sample](incident-report-template.md)
- [Project Workflow](project-workflow.md)
- [Proposed GitHub Issues Backlog](github-issues-backlog.md)
- [Asset Sources](asset-sources.md)

## Source-of-Truth Rules

1. Automated results must name the exact commit and execution date.
2. Historical evidence remains historical; it must not be presented as the current working-tree result.
3. Manual checks remain incomplete until tester, date, environment, and evidence are recorded.
4. External integrations remain unverified until approved account evidence exists.
5. Planned backlog items are proposals, not implemented features or existing GitHub issues.
6. Simulated tickets and incidents must remain labelled as simulated.
7. Never commit credentials, cookies, personal data, full webhook signatures, or unredacted provider payloads.
