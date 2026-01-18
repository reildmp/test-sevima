<?php
/**
 * Router for PHP Built-in Server
 * This file handles routing for the backend API
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove query string
$request_uri = strtok($request_uri, '?');

// If it's a file that exists, serve it
if ($request_uri !== '/' && file_exists(__DIR__ . $request_uri)) {
  return false; // Let PHP built-in server handle it
}

// Route API requests
if (preg_match('/^\/api\/(.+)\.php$/', $request_uri, $matches)) {
  // Request is already in the correct format
  $file = __DIR__ . $request_uri;
  if (file_exists($file)) {
    require $file;
    exit();
  }
}

// Route API requests without .php extension
if (preg_match('/^\/api\/(.+)$/', $request_uri, $matches)) {
  $path = $matches[1];
  $file = __DIR__ . '/api/' . $path . '.php';

  if (file_exists($file)) {
    require $file;
    exit();
  }
}

// If no route matched, return 404
http_response_code(404);
header("Content-Type: application/json");
echo json_encode([
  "success" => false,
  "message" => "Endpoint not found: " . $request_uri
]);
