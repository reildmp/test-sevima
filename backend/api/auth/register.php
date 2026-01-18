<?php
/**
 * User Registration API
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

if (
  !empty($data->username) &&
  !empty($data->email) &&
  !empty($data->password) &&
  !empty($data->full_name)
) {
  $user->username = $data->username;
  $user->email = $data->email;
  $user->password = $data->password;
  $user->full_name = $data->full_name;
  $user->bio = $data->bio ?? '';
  $user->profile_picture = 'default-avatar.jpg';

  // Validate email format
  if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array(
      "success" => false,
      "message" => "Invalid email format."
    ));
    exit();
  }

  // Check if username already exists
  if ($user->usernameExists()) {
    http_response_code(400);
    echo json_encode(array(
      "success" => false,
      "message" => "Username already exists."
    ));
    exit();
  }

  // Check if email already exists
  if ($user->emailExists()) {
    http_response_code(400);
    echo json_encode(array(
      "success" => false,
      "message" => "Email already exists."
    ));
    exit();
  }

  // Create user
  if ($user->create()) {
    // Generate JWT token
    $token = JWT::encode(array(
      "id" => $user->id,
      "username" => $user->username,
      "email" => $user->email
    ));

    http_response_code(201);
    echo json_encode(array(
      "success" => true,
      "message" => "User registered successfully.",
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
    http_response_code(500);
    echo json_encode(array(
      "success" => false,
      "message" => "Unable to register user."
    ));
  }
} else {
  http_response_code(400);
  echo json_encode(array(
    "success" => false,
    "message" => "Unable to register. Data is incomplete."
  ));
}
