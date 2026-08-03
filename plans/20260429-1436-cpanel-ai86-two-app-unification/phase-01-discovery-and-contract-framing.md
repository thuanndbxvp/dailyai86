# Phase 01 — Discovery and contract framing

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: none
- Docs: [reports/scout-report.md](./reports/scout-report.md), [research/researcher-01-report.md](./research/researcher-01-report.md), [research/researcher-02-report.md](./research/researcher-02-report.md)

## Overview
- Date: 2026-04-29
- Description: Lock problem statement, non-goals, and identity contract scope.
- Priority: P0
- Implementation status: planned
- Review status: ready for review

## Key Insights
- Current seat counting ties to device slot; cross-app mismatch can consume extra seat.
- `session_version` invalidation is expected after reset-device; needs operator education.
- Empty DB deploy requires explicit bootstrap for non-lazy tables.

## Requirements
- Functional: two-app-only management, one-machine-one-seat across both apps.
- Non-functional: auditable events, low-risk rollout, reversible cutover.

## Architecture
- Contract-first: canonical machine ID v2 shared by both apps and server.
- App ID remains entitlement dimension; not seat identity dimension.

## Related code files
- Modify: `app/Services/LicenseService.php`, `api/license/activate.php`, `api/license/verify.php`, `api/verify.php`, `app/Controllers/AdminController.php`, `views/layouts/main.php`
- Create: schema bootstrap scripts, rollout checklist docs
- Delete/disable: optional panel sections (decision pending)

## Implementation Steps
1. Approve canonical identity contract (`machine_fingerprint_v2`, `identity_version`).
2. Approve panel scope for v1 launch.
3. Approve rollout gates and fallback window.

## Todo list
- [ ] Contract doc approved
- [ ] Scope reduction approved
- [ ] Rollout thresholds approved

## Success Criteria
- Contract accepted by backend + both app owners.
- No unresolved high-risk ambiguity.

## Risk Assessment
- Contract churn delays implementation.
- Hidden dependencies in removed panel modules.

## Security Considerations
- Privacy constraints on machine fingerprint inputs.
- Anti-replay and rate-limit behavior unchanged by contract migration.

## Next steps
- Move to schema/bootstrap with explicit table and seed sequence.