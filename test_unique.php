<?php
$tidbOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/cacert.pem',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];
$tidb = new PDO(
    "mysql:host=gateway01.ap-southeast-1.prod.aws.tidbcloud.com;port=4000;dbname=test;charset=utf8mb4",
    "dtzC8ywp1FLVzF4.root",
    "wY1GbrwwnlYNL09o",
    $tidbOptions
);

$stmt = $tidb->query("SHOW CREATE TABLE licenses");
$row = $stmt->fetch();
echo $row['Create Table'];
