<?php

require_once __DIR__ . '/../models/User.php';

class AdminUserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthorized', 'code' => 401];
        }

        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'Admin access required', 'code' => 403];
        }

        return null;
    }

    // Get all users
    public function getAllUsers() {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $users = $this->user->getAll();
        return ['success' => true, 'data' => $users];
    }

    // Get user by ID
    public function getUserById($id) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $user = $this->user->getById($id);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return ['success' => true, 'data' => $user];
    }

    // Create user
    public function createUser($name, $email, $password, $room_number = null, $extension = null, $role = 'customer') {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->password = $password;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->role = in_array($role, ['admin', 'customer'], true) ? $role : 'customer';

        return $this->user->create();
    }

    // Update user
    public function updateUser($id, $name, $email, $room_number = null, $extension = null, $role = null, $password = null) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $existingUser = $this->user->getById($id);
        if (!$existingUser) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $this->user->id = $id;
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->role = in_array($role, ['admin', 'customer'], true) ? $role : $existingUser['role'];
        $this->user->password = $password;

        return $this->user->update();
    }

    // Delete user
    public function deleteUser($id) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        if ((int)$_SESSION['user_id'] === (int)$id) {
            return ['success' => false, 'message' => 'Admin cannot delete own account'];
        }

        return $this->user->delete($id);
    }
}
