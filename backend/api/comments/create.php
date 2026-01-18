<?php
/**
 * Create Comment API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/Comment.php';

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
  $comment = new Comment($db);

  $data = json_decode(file_get_contents("php://input"));

  if (!empty($data->post_id) && !empty($data->comment_text)) {
    $comment->user_id = $decoded->id;
    $comment->post_id = $data->post_id;
    $comment->comment_text = $data->comment_text;

    if ($comment->create()) {
      http_response_code(201);
      echo json_encode(array(
        "success" => true,
        "message" => "Comment created successfully.",
        "data" => array(
          "id" => $comment->id,
          "user_id" => $comment->user_id,
          "post_id" => $comment->post_id,
          "comment_text" => $comment->comment_text
        )
      ));
    } else {
      http_response_code(500);
      echo json_encode(array(
        "success" => false,
        "message" => "Unable to create comment."
      ));
    }
  } else {
    http_response_code(400);
    echo json_encode(array(
      "success" => false,
      "message" => "Post ID and comment text are required."
    ));
  }

} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(array(
    "success" => false,
    "message" => "Access denied. " . $e->getMessage()
  ));
}
