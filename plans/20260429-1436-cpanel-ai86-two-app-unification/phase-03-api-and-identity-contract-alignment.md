# Phase 03 — API and identity contract alignment

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phase 01, phase 02
- Docs: [research/researcher-01-report.md](./research/researcher-01-report.md), [reports/scout-report.md](./reports/scout-report.md)

## Overview
- Date: 2026-04-29
- Description: Align activate/verify/token_verify with canonical machine identity v2.
- Priority: P0
- Implementation status: planned
- Review status: pending

## Key Insights
- Root failure pattern: different app fingerprints => extra device seat usage.
- Existing token/session design supports reset invalidation; keep behavior, improve messaging.

## Requirements
- Functional: same machine across both apps resolves one seat.
- Non-functional: backward-compatible transition for legacy clients.

## Architecture
- Input contract: `machine_fingerprint_v2` + `identity_version`.
- Resolution priority: v2 fingerprint > legacy machine_id/device_id fallback (temporary).
- Seat key: `(license, canonical_machine_id)` only.
- Telemetry fields: include both raw and canonical IDs + fallback reason.

## Related code files
- Modify: `api/license/activate.php`, `api/license/verify.php`, `api/verify.php`, `app/Services/LicenseService.php`, `app/Models/LicenseModel.php`
- Create: identity contract doc + compatibility matrix
- Optional modify: `app/Services/SecurityService.php` for extra audit tags

## Implementation Steps
1. Add canonical machine-id parser/validator and strict format rules.
2. Switch seat counting to canonical machine-id key.
3. Keep fallback window with explicit telemetry labels.
4. Add clear error semantics for client upgrade required.

## Todo list
- [ ] Contract fields integrated in all 3 API flows
- [ ] Seat counting logic switched
- [ ] Fallback telemetry and policy flags defined

## Success Criteria
- One machine can activate app A + app B without consuming extra seat.
- No regression for valid legacy clients during transition window.

## Risk Assessment
- Fingerprint instability across hardware/OS shifts.
- Overly long fallback window causes operational ambiguity.

## Security Considerations
- Do not trust client raw IDs blindly.
- Preserve nonce/replay/rate-limit checks unchanged.

## Next steps
- Reduce admin scope and align UI actions to new v1 target (phase 04).