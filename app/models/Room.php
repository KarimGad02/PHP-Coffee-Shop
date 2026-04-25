<?php

class Room {
    private $conn;
    private $table = 'rooms';

    public $id;
    public $name;
    public $extension;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all rooms for the combo box
    public function getAll() {
        $query = "SELECT id, name, extension FROM " . $this->table . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}