# Phase 06 — Migration, deploy, and cutover

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phases 02–05

## Overview
- Date: 2026-04-29
- Description: Go-live with split domains: admin panel on `adminx.gomhuongcanh.vn`, license API check stays on `gomhuongcanh.vn`, with empty DB for new admin stack and staged traffic.
- Priority: P0
- Implementation status: planned
- Review status: pending

## Key Insights
- Empty DB reduces data migration complexity but raises bootstrap correctness risk.
- Cutover safety needs measurable gates, not manual intuition.

## Requirements
- Functional: deploy stable admin panel at `adminx.gomhuongcanh.vn`, keep license API check on `gomhuongcanh.vn`, and route clients safely.
- Non-functional: low downtime, rapid rollback capability.

## Split-domain constraints
- Admin UI/cpanel traffic and auth session cookies must be isolated to `adminx.gomhuongcanh.vn`.
- Desktop apps must call license endpoints on `gomhuongcanh.vn` only.
- CORS/allowed origins on API must explicitly allow adminx domain where needed for admin-side tooling, without weakening public API policy.
- Telemetry must include request host/domain so incidents can be segmented by admin domain vs API domain.

## Architecture
- Blue/green (preferred) or canary style rollout.
- Short dual-mode period: fallback allowed but monitored.

## Related code files
- Modify: env/deploy scripts and runtime config files
- Create: release checklist + rollback SOP

## Implementation Steps
1. Provision domain/TLS/rewrite/security headers.
2. Run schema + seed + bootstrap verification.
3. Deploy server build and run smoke checks.
4. Route pilot traffic, then staged % increase with gate metrics.

## Todo list
- [ ] infra ready
- [ ] schema seed done
- [ ] smoke checks passed
- [ ] staged cutover completed

## Success Criteria
- All core flows pass under production traffic without seat inflation regressions.

## Risk Assessment
- Misconfigured CORS/proxy/IP logic under new domain.

## Security Considerations
- Rotate keys per environment.
- Enforce strict secret handling and access boundaries.

## Next steps
- Run QA/UAT + formal rollback/observability validation (phase 07).