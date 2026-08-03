<?php
require __DIR__ . '/bootstrap/app.php';
use Models\LicenseModel;

$key = 'QQE9-9X7M-HVYG-D1CL';
$db = Database::getInstance()->getPdo();
$stmt = $db->prepare("SELECT expiry_date, revoked FROM licenses WHERE license_key = ?");
$stmt->execute([$key]);
$res = $stmt->fetch();

print_r($res);
