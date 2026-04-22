<?php

require_once __DIR__ . '/../models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    private function ensureLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthorized', 'code' => 401];
        }

        return null;
    }

    // Read current user profile
    public function getMe() {
        $auth = $this->ensureLoggedIn();
        if ($auth) {
            return $auth;
        }

        $user = $this->user->getById($_SESSION['user_id']);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return ['success' => true, 'data' => $user];
    }

    // Update current user profile
    public function updateMe($name, $email, $room_number = null, $extension = null, $password = null) {
        $auth = $this->ensureLoggedIn();
        if ($auth) {
            return $auth;
        }

        $current = $this->user->getById($_SESSION['user_id']);
        if (!$current) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $this->user->id = $_SESSION['user_id'];
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->role = $current['role'];
        $this->user->password = $password;

        return $this->user->update();
    }

    // Delete current user account
    public function deleteMe() {
        $auth = $this->ensureLoggedIn();
        if ($auth) {
            return $auth;
        }

        $id = (int)$_SESSION['user_id'];
        session_destroy();
        return $this->user->delete($id);
    }
}
