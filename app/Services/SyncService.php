<?php
/**
 * SyncService — Xử lý gửi đồng bộ dữ liệu License sang Vercel (TiDB) tức thì qua HTTPS.
 */

declare(strict_types=1);

namespace Services;

use Database;
use Throwable;

class SyncService {

    /**
     * Gửi toàn bộ danh sách licenses từ Database hiện tại sang Vercel.
     */
    public static function syncAll(): array {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->query("SELECT * FROM licenses");
        $licenses = $stmt->fetchAll();

        return self::sendRequest([
            'action'   => 'upsert_batch',
            'licenses' => $licenses
        ]);
    }

    /**
     * Đồng bộ tức thì 1 license vừa tạo / sửa sang Vercel.
     */
    public static function syncOne(array $license): array {
        return self::sendRequest([
            'action'  => 'upsert_one',
            'license' => $license
        ]);
    }

    /**
     * Xoá 1 license trên Vercel khi bị xoá trên local.
     */
    public static function deleteOne(string $licenseKey): array {
        return self::sendRequest([
            'action'      => 'delete_one',
            'license_key' => $licenseKey
        ]);
    }

    /**
     * Gửi request HTTPS POST sang Vercel endpoint.
     */
    private static function sendRequest(array $payload): array {
        $url = defined('VERCEL_SYNC_URL') ? VERCEL_SYNC_URL : 'https://dailyai86.vercel.app/api/sync_receiver.php';
        $secret = defined('SYNC_SECRET') ? SYNC_SECRET : 'gomhuong1_sync_secret_2026_x86';

        $body = json_encode($payload);

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                             "X-Sync-Token: " . $secret . "\r\n",
                'method'  => 'POST',
                'content' => $body,
                'timeout' => 4, // 4s timeout để không làm chậm giao diện admin
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);

        try {
            $result = @file_get_contents($url, false, $context);
            if ($result === false) {
                return ['success' => false, 'error' => 'Không thể kết nối tới máy chủ Vercel.'];
            }
            $data = json_decode($result, true);
            return is_array($data) ? $data : ['success' => false, 'error' => 'Phản hồi không hợp lệ từ Vercel'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
