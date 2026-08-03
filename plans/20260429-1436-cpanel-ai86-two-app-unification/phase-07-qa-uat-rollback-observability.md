# Phase 07 — QA/UAT and rollback/observability

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phase 06

## Overview
- Date: 2026-04-29
- Description: Validate E2E behavior, finalize operational gates.
- Priority: P0
- Implementation status: planned
- Review status: pending

## Key Insights
- Need explicit E2E for activate + token_verify + reset-device across both apps.
- `session_reset_required` must be treated as expected state after reset, not incident.

## Requirements
- Functional: pass all E2E scenarios.
- Non-functional: alerting detects regressions quickly; rollback tested.

## Architecture
- Observability KPIs:
  - seat-inflation rate
  - license_rejected reason distribution
  - session_reset_required frequency
  - fallback identity usage ratio

## Related code files
- Modify: telemetry dashboards/queries and operational docs
- Create: UAT script and rollback drills

## Implementation Steps
1. Run E2E checklist:
   - Activate app A on machine M => success.
   - Activate app B on same machine M => success, still one seat.
   - Token verify both apps => active.
   - Reset device => old tokens inactive with `session_reset_required`.
   - Re-activate both apps on same machine => success.
2. Run negative tests:
   - mismatched/invalid machine ID formats
   - stale token and replay attempts
3. Execute rollback drill and confirm RTO/RPO targets.

## Todo list
- [ ] E2E checklist passed
- [ ] negative tests passed
- [ ] rollback drill passed

## Success Criteria
- Go-live quality gate green and runbooks approved.

## Risk Assessment
- False-positive alerts due to threshold tuning.

## Security Considerations
- Audit log completeness for all auth lifecycle events.

## Next steps
- Enforce packaging standard for all future app onboarding (phase 08 + build-plan).