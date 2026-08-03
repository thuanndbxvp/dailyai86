<?php
// Script to import gomhuong1_appnew.sql into TiDB
set_time_limit(300);

$host = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = '4000';
$user = 'dtzC8ywp1FLVzF4.root';
$pass = 'wY1GbrwwnlYNL09o';
$db = 'test'; // Target database

try {
    echo "Kết nối tới TiDB...<br>";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/cacert.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    
    echo "Đang đọc file gomhuong1_appnew_patched.sql...<br>";
    $sql = file_get_contents(__DIR__ . '/gomhuong1_appnew_patched.sql');
    
    if ($sql === false) {
        throw new Exception("Không tìm thấy file gomhuong1_appnew_patched.sql");
    }
    
    echo "Bắt đầu import (có thể mất vài phút)...<br>";
    $pdo->exec($sql);
    
    echo "<h3 style='color:green'>✅ Import Database lên TiDB Serverless thành công!</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Lỗi: " . $e->getMessage() . "</h3>";
}
?>
