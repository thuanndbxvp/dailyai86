# Phase 05 — Client SDK alignment across 2 desktop apps

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phase 03
- External app paths: `D:\Sync 3.5_BatchCapcut`, `F:\Code laptop\Voicebox-V2`

## Overview
- Date: 2026-04-29
- Description: Align both desktop clients to identical auth/device contract.
- Priority: P0
- Implementation status: planned
- Review status: pending

## Key Insights
- Backend fix alone is insufficient if clients keep divergent machine fingerprints.
- Shared SDK/util is the only reliable long-term path.

## Requirements
- Functional: both apps send same canonical machine_fingerprint_v2 on same machine.
- Non-functional: deterministic output, reproducible across reinstall/update.

## Architecture
- Shared auth client module behavior:
  - canonical machine fingerprint generation
  - request payload schema v2
  - token/session persistence semantics
  - structured client telemetry IDs

## Related code files
- External repos only (not in current workspace)
- Server touchpoints: API payload contracts in phase 03 docs

## Implementation Steps
1. Define SDK contract test vectors.
2. Patch app A and app B to same SDK/version.
3. Add pre-release contract conformance tests per app.

## Todo list
- [ ] shared SDK spec approved
- [ ] app A upgraded
- [ ] app B upgraded
- [ ] contract tests pass on both apps

## Success Criteria
- Same machine produces same fingerprint across both apps and versions.

## Risk Assessment
- Environment-specific entropy causes drift.
- One app ships late causing mixed-client window.

## Security Considerations
- Avoid collecting sensitive host attributes beyond contract necessity.

## Next steps
- Execute deployment and staged cutover (phase 06).