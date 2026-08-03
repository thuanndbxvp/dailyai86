# Phase 04 — Admin panel scope reduction to 2 apps

## Context links
- Parent: [plan.md](./plan.md)
- Dependencies: phase 01–03 decisions
- Docs: [reports/scout-report.md](./reports/scout-report.md), [research/researcher-02-report.md](./research/researcher-02-report.md)

## Overview
- Date: 2026-04-29
- Description: Remove operator surface not needed for 2-app launch.
- Priority: P1
- Implementation status: planned
- Review status: pending

## Key Insights
- Current admin includes apps/agencies/report/bulk/export; v1 scope can be slimmer.
- Less UI surface reduces misconfiguration risk during launch.

## Requirements
- Functional: manage licenses for exactly two apps, reset device, inspect status.
- Non-functional: minimal clicks, low operator error.

## Architecture
- Keep core flows: login, dashboard, create/edit/view, revoke, reset-device, change-password.
- Gate optional modules behind feature flag or hide entirely in v1.

## Related code files
- Modify: `app/Controllers/AdminController.php`, `views/layouts/main.php`, `views/admin/dashboard.php`, `views/admin/create.php`, `views/admin/edit.php`, `views/admin/view.php`
- Disable/hide: `views/admin/apps.php`, `views/admin/agencies.php`, `views/admin/report.php`, bulk views (decision pending)

## Implementation Steps
1. Define v1 navigation map.
2. Disable routes/views outside v1 scope.
3. Update forms to show only 2 app choices and stable labels.

## Todo list
- [ ] v1 menu map approved
- [ ] hidden routes list approved
- [ ] 2-app labels and aliases approved

## Success Criteria
- Operators can complete full license lifecycle with reduced UI only.

## Risk Assessment
- Removing too much may block support operations.

## Security Considerations
- Keep CSRF/auth checks unchanged while refactoring routes/views.

## Next steps
- Align client SDK and release orchestration (phase 05).