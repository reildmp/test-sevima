<?php
/**
 * List Posts API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/Post.php';
include_once '../../models/Like.php';

// Get JWT from Authorization header (optional for viewing posts)
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$current_user_id = null;

if (!empty($authHeader)) {
  try {
    $jwt = str_replace('Bearer ', '', $authHeader);
    $decoded = JWT::decode($jwt);
    $current_user_id = $decoded->id;
  } catch (Exception $e) {
    // Continue without user context
  }
}

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$stmt = $post->getAll($limit, $offset);
$num = $stmt->rowCount();

if ($num > 0) {
  $posts_arr = array();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $post_item = array(
      "id" => $row['id'],
      "user_id" => $row['user_id'],
      "username" => $row['username'],
      "full_name" => $row['full_name'],
      "profile_picture" => $row['profile_picture'],
      "caption" => $row['caption'],
      "image_url" => $row['image_url'],
      "likes_count" => intval($row['likes_count']),
      "comments_count" => intval($row['comments_count']),
      "created_at" => $row['created_at']
    );

    // Check if current user has liked this post
    if ($current_user_id) {
      $like = new Like($db);
      $like->user_id = $current_user_id;
      $like->post_id = $row['id'];
      $post_item['is_liked'] = $like->hasLiked();
    } else {
      $post_item['is_liked'] = false;
    }

    array_push($posts_arr, $post_item);
  }

  http_response_code(200);
  echo json_encode(array(
    "success" => true,
    "data" => $posts_arr,
    "count" => $num
  ));
} else {
  http_response_code(200);
  echo json_encode(array(
    "success" => true,
    "data" => array(),
    "count" => 0
  ));
}
