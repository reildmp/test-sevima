<?php
/**
 * User Login API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
  $user->username = $data->username;

  if ($user->usernameExists()) {
    // Verify password
    if (password_verify($data->password, $user->password)) {
      // Generate JWT token
      $token = JWT::encode(array(
        "id" => $user->id,
        "username" => $user->username,
        "email" => $user->email
      ));

      http_response_code(200);
      echo json_encode(array(
        "success" => true,
        "message" => "Login successful.",
        "data" => array(
          "id" => $user->id,
          "username" => $user->username,
          "email" => $user->email,
          "full_name" => $user->full_name,
          "bio" => $user->bio,
          "profile_picture" => $user->profile_picture,
          "token" => $token
        )
      ));
    } else {
      http_response_code(401);
      echo json_encode(array(
        "success" => false,
        "message" => "Invalid password."
      ));
    }
  } else {
    http_response_code(401);
    echo json_encode(array(
      "success" => false,
      "message" => "User not found."
    ));
  }
} else {
  http_response_code(400);
  echo json_encode(array(
    "success" => false,
    "message" => "Unable to login. Data is incomplete."
  ));
}
