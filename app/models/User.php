<?php

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $room_number;
    public $extension;
    public $profile_image;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users
    public function getAll() {
        $query = "SELECT id, name, email, room_number, extension, role, created_at FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT id, name, email, room_number, extension, profile_image, role, created_at FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by email
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create user
    public function create() {
        if (empty($this->name) || empty($this->email) || empty($this->password)) {
            return ['success' => false, 'message' => 'Name, email, and password are required'];
        }

        if ($this->getByEmail($this->email)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        $query = "INSERT INTO " . $this->table . " 
                  (name, email, password, room_number, extension, role) 
                  VALUES (:name, :email, :password, :room_number, :extension, :role)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':room_number', $this->room_number);
        $stmt->bindParam(':extension', $this->extension);
        $stmt->bindParam(':role', $this->role);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User created successfully', 'id' => $this->conn->lastInsertId()];
        }

        return ['success' => false, 'message' => 'Failed to create user'];
    }

    // Update user
    public function update() {
        if (empty($this->id)) {
            return ['success' => false, 'message' => 'User ID is required'];
        }

        $query = "UPDATE " . $this->table . " SET 
                  name = :name, 
                  email = :email, 
                  room_number = :room_number, 
                  extension = :extension, 
                  role = :role";

        if (!empty($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':room_number', $this->room_number);
        $stmt->bindParam(':extension', $this->extension);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password)) {
            $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $hashedPassword);
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update user'];
    }

    // Delete user
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete user'];
    }

    // Verify password
    public function verifyPassword($inputPassword, $hashedPassword) {
        return password_verify($inputPassword, $hashedPassword);
    }
}
