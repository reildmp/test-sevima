<?php
/**
 * Post Model
 */

class Post
{
  private $conn;
  private $table_name = "posts";

  public $id;
  public $user_id;
  public $caption;
  public $image_url;
  public $created_at;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Create new post
   */
  public function create()
  {
    $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, caption, image_url) 
                  VALUES (:user_id, :caption, :image_url)
                  RETURNING id";

    $stmt = $this->conn->prepare($query);

    // Sanitize
    $this->caption = htmlspecialchars(strip_tags($this->caption));
    $this->image_url = htmlspecialchars(strip_tags($this->image_url));

    // Bind values
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":caption", $this->caption);
    $stmt->bindParam(":image_url", $this->image_url);

    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->id = $row['id'];
      return true;
    }

    return false;
  }

  /**
   * Get all posts with user info, likes count, and comments count
   */
  public function getAll($limit = 20, $offset = 0)
  {
    $query = "SELECT 
                    p.id, p.user_id, p.caption, p.image_url, p.created_at,
                    u.username, u.full_name, u.profile_picture,
                    COUNT(DISTINCT l.id) as likes_count,
                    COUNT(DISTINCT c.id) as comments_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN likes l ON p.id = l.post_id
                  LEFT JOIN comments c ON p.id = c.post_id
                  GROUP BY p.id, p.user_id, p.caption, p.image_url, p.created_at, 
                           u.username, u.full_name, u.profile_picture
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt;
  }

  /**
   * Get post by ID
   */
  public function getById()
  {
    $query = "SELECT 
                    p.id, p.user_id, p.caption, p.image_url, p.created_at,
                    u.username, u.full_name, u.profile_picture,
                    COUNT(DISTINCT l.id) as likes_count,
                    COUNT(DISTINCT c.id) as comments_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN likes l ON p.id = l.post_id
                  LEFT JOIN comments c ON p.id = c.post_id
                  WHERE p.id = :id
                  GROUP BY p.id, p.user_id, p.caption, p.image_url, p.created_at,
                           u.username, u.full_name, u.profile_picture
                  LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Delete post (only by owner)
   */
  public function delete()
  {
    // First check if user owns this post
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
   * Check if user owns this post
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
