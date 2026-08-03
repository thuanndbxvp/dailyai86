# Phase 08 — Packaging standardization principles (all future apps)

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phase 03, phase 05
- Artifact: [build-plan.md](./build-plan.md)

## Overview
- Date: 2026-04-29
- Description: Define mandatory packaging/integration contract for every new app.
- Priority: P1
- Implementation status: planned
- Review status: pending

## Key Insights
- Multi-app seat bugs usually come from per-app custom auth packaging.
- Mandatory standard package avoids drift and support cost.

## Requirements
- Every app must integrate same auth SDK, payload contract, telemetry schema, release gate.

## Architecture
- One distributable auth package + contract tests + compatibility matrix.
- Build pipeline fails if contract tests fail.

## Related code files
- Create: centralized package guidelines and CI gate docs.
- External app repos: integrate same package versioning policy.

## Implementation Steps
1. Define package manifest, semantic versioning, and deprecation policy.
2. Define mandatory CI checks for identity contract.
3. Define release checklist and runtime kill-switch policies.

## Todo list
- [ ] package policy approved
- [ ] CI gate approved
- [ ] onboarding template approved

## Success Criteria
- New app onboarding follows standard in <1 day and passes contract tests first try.

## Risk Assessment
- Teams bypass standards under time pressure.

## Security Considerations
- Signed artifacts and checksum verification for shared package.

## Next steps
- Publish build-plan and enforce in onboarding workflow.