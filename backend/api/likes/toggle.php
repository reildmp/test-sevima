<?php
/**
 * Toggle Like API (Like/Unlike)
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/Like.php';

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
  $like = new Like($db);

  $data = json_decode(file_get_contents("php://input"));

  if (!empty($data->post_id)) {
    $like->user_id = $decoded->id;
    $like->post_id = $data->post_id;

    // Check if already liked
    if ($like->hasLiked()) {
      // Unlike
      if ($like->delete()) {
        http_response_code(200);
        echo json_encode(array(
          "success" => true,
          "message" => "Post unliked.",
          "action" => "unliked"
        ));
      } else {
        http_response_code(500);
        echo json_encode(array(
          "success" => false,
          "message" => "Unable to unlike post."
        ));
      }
    } else {
      // Like
      if ($like->create()) {
        http_response_code(201);
        echo json_encode(array(
          "success" => true,
          "message" => "Post liked.",
          "action" => "liked"
        ));
      } else {
        http_response_code(500);
        echo json_encode(array(
          "success" => false,
          "message" => "Unable to like post."
        ));
      }
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
