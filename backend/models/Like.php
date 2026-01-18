<?php
/**
 * Like Model
 */

class Like
{
  private $conn;
  private $table_name = "likes";

  public $id;
  public $user_id;
  public $post_id;
  public $created_at;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Add like to post
   */
  public function create()
  {
    // Check if already liked
    if ($this->hasLiked()) {
      return false;
    }

    $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, post_id) 
                  VALUES (:user_id, :post_id)";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":post_id", $this->post_id);

    return $stmt->execute();
  }

  /**
   * Remove like from post
   */
  public function delete()
  {
    $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND post_id = :post_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":post_id", $this->post_id);

    return $stmt->execute();
  }

  /**
   * Check if user has liked this post
   */
  public function hasLiked()
  {
    $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND post_id = :post_id 
                  LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":post_id", $this->post_id);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }

  /**
   * Get likes for a post
   */
  public function getByPost()
  {
    $query = "SELECT l.id, l.user_id, l.created_at, u.username, u.full_name, u.profile_picture
                  FROM " . $this->table_name . " l
                  LEFT JOIN users u ON l.user_id = u.id
                  WHERE l.post_id = :post_id
                  ORDER BY l.created_at DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $this->post_id);
    $stmt->execute();

    return $stmt;
  }
}
