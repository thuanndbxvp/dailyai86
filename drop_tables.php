<?php
$host = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = '4000';
$user = 'dtzC8ywp1FLVzF4.root';
$pass = 'wY1GbrwwnlYNL09o';
$db = 'test';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/cacert.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "Dropping $table...<br>\n";
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "Done dropping tables.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
