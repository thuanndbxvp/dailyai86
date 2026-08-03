# Plan Overview — adminx panel + gomhuongcanh API unification

Date: 2026-04-29
Status: Draft for review
Scope: New admin panel at `adminx.gomhuongcanh.vn` on empty DB; manage only 2 desktop apps; license API check remains at `gomhuongcanh.vn`; unify machine identity; safe rollout.

## Phases
- [x] Phase 01 — Discovery and contract framing ([phase-01-discovery-and-contract-framing.md](./phase-01-discovery-and-contract-framing.md))
- [ ] Phase 02 — Server schema and bootstrap for empty DB ([phase-02-server-schema-and-bootstrap-empty-db.md](./phase-02-server-schema-and-bootstrap-empty-db.md))
- [ ] Phase 03 — API and identity contract alignment ([phase-03-api-and-identity-contract-alignment.md](./phase-03-api-and-identity-contract-alignment.md))
- [ ] Phase 04 — Admin panel scope reduction to 2 apps ([phase-04-admin-panel-scope-reduction-two-apps.md](./phase-04-admin-panel-scope-reduction-two-apps.md))
- [ ] Phase 05 — Client SDK alignment across 2 desktop apps ([phase-05-client-sdk-alignment-two-desktop-apps.md](./phase-05-client-sdk-alignment-two-desktop-apps.md))
- [ ] Phase 06 — Migration, deploy, and cutover ([phase-06-migration-deploy-and-cutover.md](./phase-06-migration-deploy-and-cutover.md))
- [ ] Phase 07 — QA/UAT and rollback/observability ([phase-07-qa-uat-rollback-observability.md](./phase-07-qa-uat-rollback-observability.md))
- [ ] Phase 08 — Packaging standardization principle (all future apps) ([phase-08-packaging-standardization-principles.md](./phase-08-packaging-standardization-principles.md))

## Inputs used
- Scout report: [reports/scout-report.md](./reports/scout-report.md)
- Research report 01: [research/researcher-01-report.md](./research/researcher-01-report.md)
- Research report 02: [research/researcher-02-report.md](./research/researcher-02-report.md)

## Key decisions pending
- Keep/disable agencies and report modules in v1.
- Keep public reset-device endpoint in new domain or move support-only.
- Mandatory alias list and strict-mode cutoff threshold for legacy machine ID.

## Suggested timeline
- Week 1: phases 02–04
- Week 2: phases 05–07 + go-live
- Phase 08 authored in parallel, enforced before next app onboard.