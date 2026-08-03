# Scout report

Date: 2026-04-29
Plan dir: `plans/20260429-1436-cpanel-ai86-two-app-unification`

## API + identity flow files
- `public/index.php` — single entrypoint, loads bootstrap + routes, dispatches router.
- `routes.php` — binds `/api/verify`, `/api/license/activate`, `/api/license/verify`, admin routes, reset-device route.
- `api/license/activate.php` — activation endpoint, rate limit, calls `LicenseService::verify`, returns compact token.
- `api/license/verify.php` — token verify endpoint, checks session_version + device match + full verify.
- `api/verify.php` — direct signed verify flow with nonce + timestamp + per-ip/per-license throttling.
- `app/Services/LicenseService.php` — core logic: app resolution, device slot resolution, max_devices enforcement, audit + api logs.
- `app/Services/SecurityService.php` — IP detect, rate-limit buckets, nonce replay prevention, CORS, blocked IPs.
- `app/Services/TokenService.php` — Ed25519 sign/verify compact token + signed API payload.
- `app/Models/LicenseModel.php` — licenses/devices CRUD, reset devices, counters/log readers.

## Admin scope files
- `app/Controllers/AdminController.php` — full admin actions (dashboard, apps, agencies, report, bulk ops, reset-device, export).
- `views/admin/*.php` — dashboard/create/edit/bulk/apps/agencies/report/change-password/login.
- `views/public/reset_device.php` — customer self-service reset.
- `views/layouts/main.php` — sidebar nav items; must be reduced if panel only manages 2 apps.

## Bootstrap + deploy files
- `bootstrap/config.php` — env loader, constants, app IDs/profiles, rate limit and auth settings.
- `bootstrap/database.php` — PDO singleton + column existence cache.
- `bootstrap/app.php` — autoload map for Controllers/Models/Services.
- `.env.example` — deploy checklist baseline for auth, keys, limits, CORS, proxy.
- `.htaccess` + `public/.htaccess` — rewrite and sensitive path protection.
- `gomhuong1_syn.sql` — historical dump, useful for seed pattern reference only (new deployment starts empty).

## Coupling / risks for empty DB deployment
- `LicenseModel` and `ReportService` query assumes `licenses`, `devices`, `audit_log` tables exist.
- Some tables auto-created lazily (`platform_apps`, `app_aliases`, request metrics tables), others are not.
- Device quota logic currently keyed by device slot, not explicit cross-app canonical machine contract.
- `session_version` invalidates old tokens after reset-device; expected but can look like random inactive tokens.

## Immediate planning implications
- Need explicit bootstrap migration/seed sequence before first admin/API traffic.
- Need contract-first machine identity (`machine_id_v2`) shared by both apps.
- Need panel reduction plan: apps/aliases management can be limited or hidden after fixed 2-app seed.

## Unresolved questions
1. Keep agencies/report modules in v1 cpanel, or trim to minimum?
2. Keep customer reset-device public page in new domain or move to support-only flow?
3. Should app aliases remain editable or fixed for 2-app scope?