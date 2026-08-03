# Build Plan — Nguyên tắc đóng gói chuẩn hóa cho tất cả app

Date: 2026-04-29
Scope: áp dụng cho mọi app desktop kết nối license server/cpanel.

## 1) Mục tiêu bắt buộc
- 1 máy vật lý dùng nhiều app vẫn tính 1 seat nếu cùng license.
- Không app nào tự chế auth flow riêng.
- Mọi app phát hành phải cùng contract telemetry + identity.

## 2) Chuẩn package bắt buộc
- Dùng chung 1 `Auth SDK` nội bộ (single source of truth).
- SDK xuất API tối thiểu:
  - `getMachineFingerprintV2()`
  - `activateLicense(payloadV2)`
  - `verifyToken(payloadV2)`
  - `handleSessionReset()`
- Nghiêm cấm mỗi app tự build fingerprint riêng.

## 3) Contract payload thống nhất (v2)
- Bắt buộc fields:
  - `license_key`
  - `app_id`
  - `machine_fingerprint_v2`
  - `identity_version = 2`
  - `app_version`
  - `nonce`, `timestamp`
- Optional có kiểm soát:
  - legacy identifiers chỉ trong giai đoạn transition.

## 4) Quy tắc seat counting
- Seat key phía server: `(license_key, canonical_machine_id)`.
- `app_id` chỉ để entitlement + telemetry, không tham gia seat key.

## 5) Versioning + tương thích
- SDK theo semver:
  - MAJOR: đổi contract breaking.
  - MINOR: thêm field backward compatible.
  - PATCH: bugfix.
- Mỗi app phải pin version SDK rõ ràng.
- Không cho release app nếu dùng SDK version bị deprecate.

## 6) CI/CD gate bắt buộc trước phát hành app
- Contract tests pass (payload schema + response handling).
- Determinism test pass: cùng máy => cùng fingerprint trên mọi app.
- Negative tests pass: invalid signature, nonce replay, session reset.
- Telemetry completeness >= 99.9% trường bắt buộc.

## 7) Runtime safety gate
- Feature flags server:
  - `identity_v2_strict`
  - `identity_fallback_enabled`
- Mở strict mode chỉ khi fallback traffic < ngưỡng đã chốt.

## 8) Checklist release cho app mới
1. Map `app_id` trong cpanel seed.
2. Tích hợp Auth SDK chuẩn.
3. Pass contract test suite.
4. Pass staging activate/verify/reset-device E2E.
5. Canary rollout và theo dõi KPI 24h.

## 9) KPI vận hành bắt buộc
- `license_rejected` by reason.
- `session_reset_required` rate.
- Seat inflation rate (cùng license, nhiều app, cùng máy).
- `% fallback identity` (mục tiêu tiến về 0).

## 10) Chính sách không tuân thủ
- App không đạt chuẩn => không được phép go-live.
- Hotfix bypass chuẩn chỉ được phép khi có rollback plan + approval.

## Unresolved questions
1. Ngưỡng cụ thể để bật `identity_v2_strict` là bao nhiêu?
2. SLA bao lâu để app legacy phải nâng cấp SDK?
3. Có cần ký số artifact SDK nội bộ ngay phase đầu không?