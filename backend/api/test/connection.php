<?php
/**
 * Test Database Connection
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

try {
  $database = new Database();
  $db = $database->getConnection();

  // Test query
  $query = "SELECT COUNT(*) as count FROM users";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  http_response_code(200);
  echo json_encode([
    "success" => true,
    "message" => "Database connection successful!",
    "database" => "PostgreSQL",
    "users_count" => $result['count'],
    "php_version" => phpversion(),
    "pdo_drivers" => PDO::getAvailableDrivers()
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => "Database connection failed",
    "error" => $e->getMessage(),
    "php_version" => phpversion(),
    "pdo_drivers" => PDO::getAvailableDrivers(),
    "pgsql_available" => in_array('pgsql', PDO::getAvailableDrivers())
  ]);
}
