<?php
/**
 * Check PHP PDO PostgreSQL Support
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$info = [
  "php_version" => phpversion(),
  "pdo_available" => extension_loaded('pdo'),
  "pdo_pgsql_available" => extension_loaded('pdo_pgsql'),
  "pdo_drivers" => PDO::getAvailableDrivers(),
  "loaded_extensions" => get_loaded_extensions()
];

// Test PostgreSQL connection
if (extension_loaded('pdo_pgsql')) {
  try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=instaapp";
    $username = "postgres";
    $password = "1234";

    $conn = new PDO($dsn, $username, $password, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $info['connection_test'] = 'SUCCESS';
    $info['message'] = 'PostgreSQL connection successful!';

    // Test query
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    $info['postgres_version'] = $version['version'];

  } catch (PDOException $e) {
    $info['connection_test'] = 'FAILED';
    $info['error'] = $e->getMessage();
    $info['error_code'] = $e->getCode();
  }
} else {
  $info['connection_test'] = 'SKIPPED';
  $info['error'] = 'PDO PostgreSQL driver not installed';
}

echo json_encode($info, JSON_PRETTY_PRINT);
