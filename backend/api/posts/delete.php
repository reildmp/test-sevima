<?php
/**
 * Delete Post API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/Post.php';

// Get JWT from Authorization header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($authHeader)) {
  http_response_code(401);
  echo json_encode(array(
    "success" => false,
    "message" => "Access denied. No token provided."
  ));
  exit();
}

try {
  $jwt = str_replace('Bearer ', '', $authHeader);
  $decoded = JWT::decode($jwt);

  $database = new Database();
  $db = $database->getConnection();
  $post = new Post($db);

  $data = json_decode(file_get_contents("php://input"));

  if (!empty($data->id)) {
    $post->id = $data->id;
    $post->user_id = $decoded->id;

    if ($post->delete()) {
      http_response_code(200);
      echo json_encode(array(
        "success" => true,
        "message" => "Post deleted successfully."
      ));
    } else {
      http_response_code(403);
      echo json_encode(array(
        "success" => false,
        "message" => "Unable to delete post. You may not have permission."
      ));
    }
  } else {
    http_response_code(400);
    echo json_encode(array(
      "success" => false,
      "message" => "Post ID is required."
    ));
  }

} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(array(
    "success" => false,
    "message" => "Access denied. " . $e->getMessage()
  ));
}
