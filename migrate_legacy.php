<?php
require __DIR__ . '/bootstrap/app.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Starting legacy data migration...\n";

// 1. Connect to TiDB (Destination)
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
echo "Connected to TiDB successfully.\n";

// 2. Connect to Local MySQL (Source)
$localHost = 'localhost';
$localDb = 'gomhuong1_syn';
$localUser = 'root';
$localPass = '';

try {
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb;charset=utf8mb4", $localUser, $localPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected to Local DB successfully.\n";
} catch (PDOException $e) {
    die("Failed to connect to Local DB: " . $e->getMessage() . "\n");
}

// 3. Fetch all legacy licenses
$stmt = $localPdo->query("SELECT * FROM licenses");
$legacyLicenses = $stmt->fetchAll();
echo "Found " . count($legacyLicenses) . " legacy licenses to process.\n";

// Prepare insert statement for TiDB with UPSERT
$insertSql = "INSERT INTO licenses (
    license_key, customer_name, email, expiry_date, max_devices, 
    validity_days, start_on_first_activation, first_activated_at, 
    app_profile, allowed_apps, admin_note, session_version, 
    agency_id, revoked, created_at, updated_at
) VALUES (
    :license_key, :customer_name, :email, :expiry_date, :max_devices, 
    :validity_days, :start_on_first_activation, :first_activated_at, 
    :app_profile, :allowed_apps, :admin_note, :session_version, 
    :agency_id, :revoked, :created_at, :updated_at
) ON DUPLICATE KEY UPDATE 
    customer_name = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(customer_name), licenses.customer_name),
    email = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(email), licenses.email),
    expiry_date = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(expiry_date), licenses.expiry_date),
    max_devices = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(max_devices), licenses.max_devices),
    validity_days = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(validity_days), licenses.validity_days),
    app_profile = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(app_profile), licenses.app_profile),
    allowed_apps = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(allowed_apps), licenses.allowed_apps),
    agency_id = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(agency_id), licenses.agency_id),
    revoked = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(revoked), licenses.revoked),
    updated_at = IF(VALUES(updated_at) >= licenses.updated_at, VALUES(updated_at), licenses.updated_at)";

$insertStmt = $tidb->prepare($insertSql);

$migrated = 0;
$errors = 0;

foreach ($legacyLicenses as $row) {
    // Transform allowed_apps
    $allowed = [];
    if (!empty($row['allowed_apps'])) {
        $decoded = json_decode($row['allowed_apps'], true);
        if (is_array($decoded)) {
            $allowed = $decoded;
        }
    }
    
    if (!in_array('syncaudio_v1', $allowed)) {
        $allowed[] = 'syncaudio_v1';
    }
    
    // Bind values
    try {
        $insertStmt->execute([
            ':license_key' => $row['license_key'],
            ':customer_name' => $row['customer_name'] ?: 'Khách cũ',
            ':email' => $row['email'] ?: 'no-email@example.com',
            ':expiry_date' => $row['expiry_date'],
            ':max_devices' => (int)$row['max_devices'],
            ':validity_days' => (int)$row['validity_days'],
            ':start_on_first_activation' => (int)$row['start_on_first_activation'],
            ':first_activated_at' => $row['first_activated_at'],
            ':app_profile' => $row['app_profile'] ?: 'bundle_3apps',
            ':allowed_apps' => json_encode($allowed),
            ':admin_note' => $row['admin_note'],
            ':session_version' => (int)$row['session_version'],
            ':agency_id' => null, // Ignore missing agencies
            ':revoked' => (int)$row['revoked'],
            ':created_at' => $row['created_at'],
            ':updated_at' => $row['updated_at']
        ]);
        $migrated++;
    } catch (PDOException $e) {
        echo "Error inserting key {$row['license_key']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "Migration Summary:\n";
echo "- Total legacy keys processed: " . count($legacyLicenses) . "\n";
echo "- Successfully migrated/upserted: $migrated\n";
echo "- Errors: $errors\n";

echo "Done.\n";
