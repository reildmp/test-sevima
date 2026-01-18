<?php
/**
 * List Comments API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Comment.php';

$database = new Database();
$db = $database->getConnection();
$comment = new Comment($db);

if (!empty($_GET['post_id'])) {
  $comment->post_id = $_GET['post_id'];

  $stmt = $comment->getByPost();
  $num = $stmt->rowCount();

  if ($num > 0) {
    $comments_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $comment_item = array(
        "id" => $row['id'],
        "user_id" => $row['user_id'],
        "username" => $row['username'],
        "full_name" => $row['full_name'],
        "profile_picture" => $row['profile_picture'],
        "comment_text" => $row['comment_text'],
        "created_at" => $row['created_at']
      );

      array_push($comments_arr, $comment_item);
    }

    http_response_code(200);
    echo json_encode(array(
      "success" => true,
      "data" => $comments_arr,
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
} else {
  http_response_code(400);
  echo json_encode(array(
    "success" => false,
    "message" => "Post ID is required."
  ));
}
