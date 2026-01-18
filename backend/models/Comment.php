<?php
/**
 * Comment Model
 */

class Comment
{
  private $conn;
  private $table_name = "comments";

  public $id;
  public $user_id;
  public $post_id;
  public $comment_text;
  public $created_at;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Create new comment
   */
  public function create()
  {
    $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, post_id, comment_text) 
                  VALUES (:user_id, :post_id, :comment_text)
                  RETURNING id";

    $stmt = $this->conn->prepare($query);

    // Sanitize
    $this->comment_text = htmlspecialchars(strip_tags($this->comment_text));

    // Bind values
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":post_id", $this->post_id);
    $stmt->bindParam(":comment_text", $this->comment_text);

    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->id = $row['id'];
      return true;
    }

    return false;
  }

  /**
   * Get comments for a post
   */
  public function getByPost()
  {
    $query = "SELECT 
                    c.id, c.user_id, c.post_id, c.comment_text, c.created_at,
                    u.username, u.full_name, u.profile_picture
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.post_id = :post_id
                  ORDER BY c.created_at ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $this->post_id);
    $stmt->execute();

    return $stmt;
  }

  /**
   * Delete comment (only by owner)
   */
  public function delete()
  {
    // First check if user owns this comment
    $query = "SELECT user_id FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['user_id'] != $this->user_id) {
      return false; // Not authorized
    }

    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id);

    return $stmt->execute();
  }

  /**
   * Check if user owns this comment
   */
  public function isOwner($user_id)
  {
    $query = "SELECT user_id FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && $row['user_id'] == $user_id;
  }
}
