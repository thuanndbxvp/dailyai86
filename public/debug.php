<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo '<h2>CPanel Local Database Diagnostic Tool</h2>';

$appRoot = dirname(__DIR__);

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

// 4. Thử chạy toàn bộ app xem có lỗi logic nào không
echo '<h3>4. Kiểm tra nạp Router & Controller:</h3>';
try {
    $_SERVER['REQUEST_URI'] = '/admin/dashboard';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    require_once $appRoot . '/routes.php';
    echo "<p style='color:green;'>✅ Router nạp thành công không bị crash!</p>";
} catch (Throwable $e) {
    echo "<p style='color:red; font-weight:bold;'>❌ LỖI KHI NẠP APP:</p>";
    echo "<pre style='background:#fee; padding:12px; border:1px solid #f88;'>" . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
