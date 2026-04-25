<?php

class Product {
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $price;
    public $image;
    public $is_available;
    public $category_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products (Admin View)
    public function getAll() {
        $query = "SELECT p.id, p.name, p.price, p.image, p.is_available, p.category_id, c.name as category_name 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ONLY available products (Customer Menu View)
    public function getAvailable() {
        $query = "SELECT p.id, p.name, p.price, p.image, p.category_id, c.name as category_name 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_available = 1
                  ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create new product
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, price, image, is_available, category_id) 
                  VALUES (:name, :price, :image, :is_available, :category_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':is_available', $this->is_available);
        $stmt->bindParam(':category_id', $this->category_id);

        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false];
    }
    
    
}