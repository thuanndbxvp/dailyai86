# Phase 02 — Server schema and bootstrap for empty DB

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: [phase-01-discovery-and-contract-framing.md](./phase-01-discovery-and-contract-framing.md)
- Docs: [research/researcher-02-report.md](./research/researcher-02-report.md)

## Overview
- Date: 2026-04-29
- Description: Define deterministic bootstrap for empty DB and two-app seed.
- Priority: P0
- Implementation status: planned
- Review status: pending

## Key Insights
- Some tables auto-create lazily; core tables do not. Empty DB needs preflight schema run.
- Seed must pin exactly two active apps at go-live.

## Requirements
- Functional: boot DB to runnable state before first traffic.
- Non-functional: idempotent schema setup, deterministic seed, no manual SQL patching in prod.

## Architecture
- Bootstrap pipeline order:
  1) Core schema (`licenses`, `devices`, `audit_log`, metrics logs/counters)
  2) Auth/admin storage
  3) App registry + alias seed (2 apps)
  4) Post-bootstrap smoke checks

## Related code files
- Modify: `bootstrap/database.php`, `bootstrap/config.php`, `app/Models/AppModel.php`, `app/Models/AppAliasModel.php`
- Create: migration/bootstrap scripts + runbook
- Read-only impact: `.env.example`

## Implementation Steps
1. Create explicit schema migration set for empty DB launch.
2. Add seed manifest with 2 app IDs + alias map.
3. Add bootstrap verification command/checklist.
4. Validate idempotency of repeated deploy.

## Todo list
- [ ] Schema migration set drafted
- [ ] Two-app seed manifest drafted
- [ ] Bootstrap verification checklist drafted

## Success Criteria
- Fresh DB + one deployment produces fully functional admin/API without runtime missing-table errors.

## Risk Assessment
- Missing table edge case under first API call.
- Seed drift between environments.

## Security Considerations
- Key/secret injection only via env.
- Ensure logs path permissions are least-privilege.

## Next steps
- Apply identity contract in API and model logic (phase 03).