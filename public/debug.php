<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require_once $appRoot . '/bootstrap/app.php';
require_once $appRoot . '/app/Auth.php';

// Bảo vệ bằng Layer 1 Gate để tránh lộ thông tin chẩn đoán ra ngoài
Auth::enforceLayer1();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo '<h2>CPanel Local Database Diagnostic Tool</h2>';

// 1. Kiểm tra đọc file .env
echo '<h3>1. Kiểm tra cấu hình .env:</h3>';
$envFile = $appRoot . '/.env';
if (!file_exists($envFile)) {
    echo "<p style='color:red;'>❌ Không tìm thấy file .env tại: $envFile</p>";
} else {
    echo "<p style='color:green;'>✅ Đã tìm thấy file .env</p>";
}

require_once $appRoot . '/bootstrap/app.php';

echo "<ul>";
echo "<li><b>DB_HOST:</b> " . (defined('DB_HOST') ? DB_HOST : 'Chưa định nghĩa') . "</li>";
echo "<li><b>DB_NAME:</b> " . (defined('DB_NAME') ? DB_NAME : 'Chưa định nghĩa') . "</li>";
echo "<li><b>DB_USER:</b> " . (defined('DB_USER') ? DB_USER : 'Chưa định nghĩa') . "</li>";
echo "<li><b>DB_PORT:</b> " . (defined('DB_PORT') ? DB_PORT : '3306') . "</li>";
echo "<li><b>DB_SSL:</b> " . (defined('DB_SSL') && DB_SSL ? 'BẬT' : 'TẮT') . "</li>";
echo "</ul>";

// 2. Thử kết nối Database
echo '<h3>2. Thử kết nối PDO Database:</h3>';
try {
    $pdo = Database::getInstance()->getPdo();
    echo "<p style='color:green; font-weight:bold;'>✅ KẾT NỐI DATABASE THÀNH CÔNG!</p>";

    // Kiểm tra danh sách bảng
    echo '<h3>3. Kiểm tra các bảng trong Database:</h3>';
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Các bảng hiện có: <b>" . implode(', ', $tables) . "</b></p>";

    // Kiểm tra bảng licenses
    if (in_array('licenses', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM licenses")->fetchColumn();
        echo "<p style='color:green;'>✅ Bảng <code>licenses</code> có <b>$count</b> bản ghi.</p>";
    } else {
        echo "<p style='color:red;'>❌ Chưa có bảng <code>licenses</code>! Bạn cần Import file SQL vào database này.</p>";
    }

    // Kiểm tra bảng sessions
    if (in_array('sessions', $tables)) {
        echo "<p style='color:green;'>✅ Bảng <code>sessions</code> đã sẵn sàng.</p>";
    } else {
        echo "<p style='color:red;'>❌ Thiếu bảng <code>sessions</code>! (Trang admin cần bảng sessions để duy trì đăng nhập).</p>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red; font-weight:bold;'>❌ LỖI KẾT NỐI DATABASE:</p>";
    echo "<pre style='background:#fee; padding:12px; border:1px solid #f88;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

// 5. Kiểm tra kết nối tới Vercel Sync Endpoint
echo '<h3>5. Kiểm tra kết nối tới Vercel Sync Endpoint:</h3>';
$syncUrl = 'https://dailyai86.vercel.app/api/sync_receiver.php';
$syncSecret = 'gomhuong1_sync_secret_2026_x86';
$host = 'dailyai86.vercel.app';
$ip = gethostbyname($host);
echo "<ul>";
echo "<li><b>URL Endpoint:</b> $syncUrl</li>";
echo "<li><b>DNS Resolve ($host):</b> " . ($ip !== $host ? "<span style='color:green;'>$ip</span>" : "<span style='color:red;'>Không resolve được DNS</span>") . "</li>";
echo "</ul>";

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $syncUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['action' => 'upsert_batch', 'licenses' => []]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Sync-Token: ' . $syncSecret,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (vHost-Debug)',
    ]);
    $res = curl_exec($ch);
    $errNo = curl_errno($ch);
    $errMsg = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);

    if ($errNo === 0 && $httpCode >= 200 && $httpCode < 400) {
        echo "<p style='color:green; font-weight:bold;'>✅ KẾT NỐI VERCEL THÀNH CÔNG (HTTP $httpCode trong {$time}s)!</p>";
        echo "<pre style='background:#efe; padding:10px; border:1px solid #8c8;'>" . htmlspecialchars($res) . "</pre>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ LỖI KẾT NỐI VERCEL (cURL $errNo / HTTP $httpCode sau {$time}s):</p>";
        echo "<pre style='background:#fee; padding:10px; border:1px solid #f88;'>" . htmlspecialchars($errMsg ?: $res) . "</pre>";
    }
} else {
    echo "<p style='color:orange;'>cURL không khả dụng trên hosting.</p>";
}

// 4. Kiểm tra nạp Router & Controller
echo '<h3>4. Kiểm tra nạp Router & Controller:</h3>';
try {
    require_once $appRoot . '/app/Auth.php';
    require_once $appRoot . '/app/Router.php';
    require_once $appRoot . '/routes.php';
    echo "<p style='color:green;'>✅ Nạp Router và Routes thành công!</p>";
} catch (Throwable $e) {
    echo "<p style='color:red; font-weight:bold;'>❌ LỖI KHI KHỞI CHẠY TRANG ADMIN:</p>";
    echo "<pre style='background:#fee; padding:12px; border:1px solid #f88;'>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
