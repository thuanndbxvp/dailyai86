<?php
/**
 * Realtime Sync Receiver — Nhận dữ liệu đồng bộ từ Server nguồn và lưu vào Database (TiDB / MySQL).
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Định nghĩa cấu hình cơ bản nếu chưa được nạp
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
    require_once APP_ROOT . '/bootstrap/app.php';
}

$secretKey = defined('SYNC_SECRET') ? SYNC_SECRET : 'gomhuong1_sync_secret_2026_x86';

// 1. Xác thực Token bảo mật
$headers = function_exists('getallheaders') ? getallheaders() : [];
$receivedToken = $headers['X-Sync-Token'] ?? $headers['x-sync-token'] ?? ($_SERVER['HTTP_X_SYNC_TOKEN'] ?? '');

if (empty($receivedToken) || !hash_equals($secretKey, $receivedToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Invalid sync token']);
    exit;
}

// 2. Đọc payload JSON
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$action = $payload['action'] ?? 'upsert_batch';

try {
    $pdo = Database::getInstance()->getPdo();

    if ($action === 'upsert_batch' || $action === 'upsert_one') {
        $licenses = $action === 'upsert_one' ? [$payload['license']] : ($payload['licenses'] ?? []);

        if (empty($licenses)) {
            echo json_encode(['success' => true, 'count' => 0, 'message' => 'No licenses to sync']);
            exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO licenses (
                id, license_key, customer_name, email, expiry_date, max_devices, 
                validity_days, start_on_first_activation, first_activated_at,
                app_profile, allowed_apps, admin_note, session_version, agency_id, 
                revoked, created_at, updated_at
            ) VALUES (
                :id, :license_key, :customer_name, :email, :expiry_date, :max_devices, 
                :validity_days, :start_on_first_activation, :first_activated_at,
                :app_profile, :allowed_apps, :admin_note, :session_version, :agency_id, 
                :revoked, :created_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE
                customer_name              = VALUES(customer_name),
                email                      = VALUES(email),
                expiry_date                = VALUES(expiry_date),
                max_devices                = VALUES(max_devices),
                validity_days              = VALUES(validity_days),
                start_on_first_activation  = VALUES(start_on_first_activation),
                first_activated_at         = VALUES(first_activated_at),
                app_profile                = VALUES(app_profile),
                allowed_apps               = VALUES(allowed_apps),
                admin_note                 = VALUES(admin_note),
                session_version            = VALUES(session_version),
                agency_id                  = VALUES(agency_id),
                revoked                    = VALUES(revoked),
                updated_at                 = VALUES(updated_at)
        ");

        $count = 0;
        foreach ($licenses as $lic) {
            if (empty($lic['license_key'])) continue;
            
            $stmt->execute([
                ':id'                        => $lic['id'] ?? null,
                ':license_key'               => $lic['license_key'],
                ':customer_name'             => $lic['customer_name'] ?? '',
                ':email'                     => $lic['email'] ?? '',
                ':expiry_date'               => $lic['expiry_date'],
                ':max_devices'               => $lic['max_devices'] ?? 2,
                ':validity_days'             => $lic['validity_days'] ?? 365,
                ':start_on_first_activation' => $lic['start_on_first_activation'] ?? 0,
                ':first_activated_at'        => $lic['first_activated_at'] ?? null,
                ':app_profile'               => $lic['app_profile'] ?? 'bundle_3apps',
                ':allowed_apps'              => is_array($lic['allowed_apps'] ?? null) ? json_encode($lic['allowed_apps']) : ($lic['allowed_apps'] ?? '[]'),
                ':admin_note'                => $lic['admin_note'] ?? null,
                ':session_version'           => $lic['session_version'] ?? 1,
                ':agency_id'                 => $lic['agency_id'] ?? null,
                ':revoked'                   => $lic['revoked'] ?? 0,
                ':created_at'                => $lic['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at'                => $lic['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'count'   => $count,
            'message' => "Đã đồng bộ thành công $count licenses vào TiDB!"
        ]);
        exit;
    }

    if ($action === 'delete_one') {
        $key = $payload['license_key'] ?? '';
        if ($key !== '') {
            $stmt = $pdo->prepare("DELETE FROM licenses WHERE license_key = ?");
            $stmt->execute([$key]);
        }
        echo json_encode(['success' => true, 'message' => "Đã xoá license $key"]);
        exit;
    }

    if ($action === 'upsert_app' || $action === 'upsert_apps_batch') {
        $apps = $action === 'upsert_app' ? [$payload['app']] : ($payload['apps'] ?? []);
        if (empty($apps)) {
            echo json_encode(['success' => true, 'count' => 0, 'message' => 'No apps to sync']);
            exit;
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            INSERT INTO platform_apps (
                id, app_id, app_name, verify_mode, default_max_devices, default_years,
                device_tracking, is_active, created_at, updated_at
            ) VALUES (
                :id, :app_id, :app_name, :verify_mode, :default_max_devices, :default_years,
                :device_tracking, :is_active, :created_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE
                app_name            = VALUES(app_name),
                verify_mode         = VALUES(verify_mode),
                default_max_devices = VALUES(default_max_devices),
                default_years       = VALUES(default_years),
                device_tracking     = VALUES(device_tracking),
                is_active           = VALUES(is_active),
                updated_at          = VALUES(updated_at)
        ");

        $count = 0;
        foreach ($apps as $a) {
            if (empty($a['app_id'])) continue;
            $stmt->execute([
                ':id'                  => $a['id'] ?? null,
                ':app_id'              => strtolower(trim($a['app_id'])),
                ':app_name'            => $a['app_name'] ?? '',
                ':verify_mode'         => $a['verify_mode'] ?? 'standard',
                ':default_max_devices' => $a['default_max_devices'] ?? 2,
                ':default_years'       => $a['default_years'] ?? 1,
                ':device_tracking'     => $a['device_tracking'] ?? 1,
                ':is_active'           => $a['is_active'] ?? 1,
                ':created_at'          => $a['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at'          => $a['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $count++;
        }
        $pdo->commit();

        echo json_encode(['success' => true, 'count' => $count, 'message' => "Đã đồng bộ $count apps vào TiDB!"]);
        exit;
    }

    if ($action === 'upsert_alias') {
        $alias = $payload['alias'] ?? [];
        if (!empty($alias['alias']) && !empty($alias['app_id'])) {
            $stmt = $pdo->prepare("
                INSERT INTO app_aliases (alias, app_id, note, created_at)
                VALUES (:alias, :app_id, :note, :created_at)
                ON DUPLICATE KEY UPDATE
                    app_id = VALUES(app_id),
                    note   = VALUES(note)
            ");
            $stmt->execute([
                ':alias'      => strtolower(trim($alias['alias'])),
                ':app_id'     => strtolower(trim($alias['app_id'])),
                ':note'       => $alias['note'] ?? null,
                ':created_at' => $alias['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
        echo json_encode(['success' => true, 'message' => "Đã đồng bộ alias thành công!"]);
        exit;
    }

    if ($action === 'delete_alias') {
        $aliasId = (int) ($payload['alias_id'] ?? 0);
        $aliasStr = trim((string) ($payload['alias'] ?? ''));
        if ($aliasId > 0) {
            $pdo->prepare("DELETE FROM app_aliases WHERE id = ?")->execute([$aliasId]);
        } elseif ($aliasStr !== '') {
            $pdo->prepare("DELETE FROM app_aliases WHERE alias = ?")->execute([$aliasStr]);
        }
        echo json_encode(['success' => true, 'message' => "Đã xoá alias!"]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
