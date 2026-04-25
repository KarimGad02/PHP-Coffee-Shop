<?php

class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT id, name FROM " . $this->table . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new category
    public function create() {
        $query = "INSERT INTO " . $this->table . " (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);

        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
        }
        return ['success' => false, 'message' => 'Could not create category'];
    }
}