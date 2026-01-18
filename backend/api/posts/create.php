<?php
/**
 * Create Post API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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

  // Handle file upload
  $image_url = null;
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../uploads/posts/';
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
      http_response_code(400);
      echo json_encode(array(
        "success" => false,
        "message" => "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed."
      ));
      exit();
    }

    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
      $image_url = 'uploads/posts/' . $new_filename;
    }
  }

  $post->user_id = $decoded->id;
  $post->caption = isset($_POST['caption']) ? $_POST['caption'] : '';
  $post->image_url = $image_url;

  if ($post->create()) {
    http_response_code(201);
    echo json_encode(array(
      "success" => true,
      "message" => "Post created successfully.",
      "data" => array(
        "id" => $post->id,
        "user_id" => $post->user_id,
        "caption" => $post->caption,
        "image_url" => $post->image_url
      )
    ));
  } else {
    http_response_code(500);
    echo json_encode(array(
      "success" => false,
      "message" => "Unable to create post."
    ));
  }

} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(array(
    "success" => false,
    "message" => "Access denied. " . $e->getMessage()
  ));
}
