<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "oracle";
    private $dbname = "policlinica";
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Eroare conexiune: " . $e->getMessage());
        }
    }
}

$db = new Database();
$conn = $db->conn; // Conexiunea PDO
?>
