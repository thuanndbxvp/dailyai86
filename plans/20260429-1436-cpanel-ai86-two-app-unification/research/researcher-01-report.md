# Researcher-01 report — Identity contract & multi-app activation

Date: 2026-04-29
Scope: device identity, activation/verify/token/session alignment for 2 desktop apps.

## Current behavior
- `LicenseService::resolveDeviceSlotId()` picks normalized `machine_id` then `device_id` fallback.
- Device seat check compares count in `devices` table against `licenses.max_devices`.
- If same physical machine yields different IDs per app, backend counts as different devices.
- `/api/license/verify` marks token inactive when token `session_version` != DB `session_version`.

## Root cause behind user symptom
- Multi-app same-machine flow fails when app A and app B generate different machine fingerprints.
- With `max_devices = 1`, second app activation can hit `Maximum devices reached (1)`.
- After reset-device, old token from another app becomes `session_reset_required` (expected behavior).

## Options
### Option A — client contract hardening (recommended)
- Both apps must send same `machine_fingerprint_v2` from shared SDK.
- Server prefers v2 fingerprint; keeps old fields as fallback for transition.
- Pros: simple, deterministic, low server complexity.
- Cons: requires coordinated client release.

### Option B — server-side fuzzy merge
- Server tries to infer same machine from legacy IDs + heuristics.
- Pros: less client dependency at first.
- Cons: collision risk, hard to audit, high false-positive risk.

### Option C — hybrid
- Enforce v2 for new activations, run short fallback window with telemetry alerts.
- Pros: practical rollout safety.
- Cons: temporary dual path complexity.

## Recommended direction
- Adopt Option C then converge to Option A strict mode.
- Seat key must be `(license_key, canonical_machine_id)` and not include `app_id`.
- Keep `app_id` only for entitlement + telemetry, not device seat counting.

## Rollout strategy
1. Add contract docs + schema fields for `machine_id_v2` + `identity_version`.
2. Add telemetry to measure fallback usage and mismatch rate.
3. Release both apps with shared SDK.
4. Enable strict mode after fallback usage < threshold.

## Risks
- Fingerprint instability across OS reinstall/hardware change.
- Legacy clients produce mixed IDs and inflate seats during rollout.
- Alias/app_id mismatch can reject license despite valid seat.

## Decision points
- What attributes are allowed in machine fingerprint (privacy/legal)?
- How long fallback window stays open?
- Threshold to force upgrade?

## Unresolved questions
1. Do both desktop apps currently share any common local machine GUID source?
2. Is forced client upgrade acceptable for strict-mode cutover?
3. Need offline grace behavior identical across both apps?