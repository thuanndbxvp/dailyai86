# Researcher-02 report — Fresh DB deployment & panel scope reduction

Date: 2026-04-29
Scope: deploy new admin domain `adminx.gomhuongcanh.vn` with empty DB, while license API check remains on `gomhuongcanh.vn`; only 2 managed apps.

## Findings
- App bootstrap has lazy table creation for `platform_apps`, `app_aliases`, request metrics tables.
- Core license flow still needs baseline tables (`licenses`, `devices`, `audit_log`) pre-created.
- Admin panel is broad: apps, agencies, report, bulk, export, reset-device.
- With new scope (2 apps only), panel can be simplified to reduce operator errors.

## Minimum go-live data model
- Required: `licenses`, `devices`, `audit_log`, admin auth storage, request logs/counters.
- Required for panel consistency: `platform_apps` with exactly 2 active app rows.
- Optional in phase 1: `agencies` if no agency partitioning at launch.

## Seed strategy for empty DB
1. Run schema bootstrap script/migration set before first request.
2. Seed 2 fixed apps and required aliases.
3. Verify API keys/ed25519 keys/session settings loaded from `.env`.
4. Run smoke tests for activate/verify/token_verify/reset-device.

## Panel scope recommendation
- Keep: dashboard, create/edit license, view, reset devices, change password.
- Consider hiding in v1: app registry CRUD, agency CRUD, heavy reports, bulk operations.
- Keep readonly telemetry panel if cheap to retain.

## Deployment checklist highlights
- DNS + TLS + rewrite rules + block sensitive paths.
- Strict `.env` for domain-specific keys; no reuse from old domain.
- Log dirs writable (`logs/ratelimit`, `logs/nonces`) and rotation policy.
- CORS tightened to production origins, not wildcard once clients fixed.

## Rollback/cutover
- Blue/green deploy preferred.
- Keep old panel live until smoke/UAT passes on new domain.
- If activation errors spike, rollback traffic to old panel while preserving logs.

## Risks
- Missing non-lazy tables on empty DB causes runtime errors under first real requests.
- Over-trimming admin features may block urgent support actions.
- Domain cutover without telemetry baseline delays incident detection.

## Unresolved questions
1. Keep public `/reset-device` endpoint on new domain or separate support domain?
2. Need agencies dimension now or postpone?
3. Which aliases are mandatory for the 2 apps at launch?