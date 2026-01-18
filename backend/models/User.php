<?php
/**
 * User Model
 */

class User
{
  private $conn;
  private $table_name = "users";

  public $id;
  public $username;
  public $email;
  public $password;
  public $full_name;
  public $bio;
  public $profile_picture;
  public $created_at;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Create new user
   */
  public function create()
  {
    $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, full_name, bio, profile_picture) 
                  VALUES (:username, :email, :password, :full_name, :bio, :profile_picture)
                  RETURNING id";

    $stmt = $this->conn->prepare($query);

    // Sanitize
    $this->username = htmlspecialchars(strip_tags($this->username));
    $this->email = htmlspecialchars(strip_tags($this->email));
    $this->full_name = htmlspecialchars(strip_tags($this->full_name));
    $this->bio = htmlspecialchars(strip_tags($this->bio));
    $this->password = password_hash($this->password, PASSWORD_BCRYPT);

    // Bind values
    $stmt->bindParam(":username", $this->username);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":password", $this->password);
    $stmt->bindParam(":full_name", $this->full_name);
    $stmt->bindParam(":bio", $this->bio);
    $stmt->bindParam(":profile_picture", $this->profile_picture);

    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->id = $row['id'];
      return true;
    }

    return false;
  }

  /**
   * Check if username exists
   */
  public function usernameExists()
  {
    $query = "SELECT id, username, email, password, full_name, bio, profile_picture 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":username", $this->username);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->id = $row['id'];
      $this->email = $row['email'];
      $this->password = $row['password'];
      $this->full_name = $row['full_name'];
      $this->bio = $row['bio'];
      $this->profile_picture = $row['profile_picture'];
      return true;
    }

    return false;
  }

  /**
   * Check if email exists
   */
  public function emailExists()
  {
    $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":email", $this->email);
    $stmt->execute();
    return $stmt->rowCount() > 0;
  }

  /**
   * Get user by ID
   */
  public function getById()
  {
    $query = "SELECT id, username, email, full_name, bio, profile_picture, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->username = $row['username'];
      $this->email = $row['email'];
      $this->full_name = $row['full_name'];
      $this->bio = $row['bio'];
      $this->profile_picture = $row['profile_picture'];
      $this->created_at = $row['created_at'];
      return true;
    }

    return false;
  }
}
