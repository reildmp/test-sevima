<?php
/**
 * Database Configuration
 * PostgreSQL Database
 */

class Database
{
    private $host = "localhost";
    private $port = "5432";
    private $db_name = "instaapp";
    private $username = "postgres";
    private $password = "1234";
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch (PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}
