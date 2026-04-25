<?php

require_once __DIR__ . '/../models/User.php';

class AdminUserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    private function storeUploadedImage($file, $targetFolder = 'users') {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $rootPath = realpath(__DIR__ . '/../../');
        if ($rootPath === false) {
            return null;
        }

        $uploadDir = $rootPath . '/public/assets/uploads/' . $targetFolder;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return null;
        }

        $fileName = uniqid($targetFolder . '_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        return '/assets/uploads/' . $targetFolder . '/' . $fileName;
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
    public function createUser($name, $email, $password, $room_number = null, $extension = null, $role = 'customer', $profile_image = null) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $storedImage = $this->storeUploadedImage($profile_image, 'users');

        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->password = $password;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->profile_image = $storedImage;
        $this->user->role = in_array($role, ['admin', 'customer'], true) ? $role : 'customer';

        return $this->user->create();
    }

    // Update user
    public function updateUser($id, $name, $email, $room_number = null, $extension = null, $role = null, $password = null, $profile_image = null) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $existingUser = $this->user->getById($id);
        if (!$existingUser) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $storedImage = $this->storeUploadedImage($profile_image, 'users');

        $this->user->id = $id;
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->profile_image = $storedImage ?: ($existingUser['profile_image'] ?? null);
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

    private function safeCount($table, $where = '1 = 1', $params = []) {
        try {
            $query = "SELECT COUNT(*) AS c FROM " . $table . " WHERE " . $where;
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['c'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Dashboard stats
    public function getDashboardStats() {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $activeOrders = $this->safeCount(
            'orders',
            "status IN (:status_processing, :status_delivery)",
            [
                ':status_processing' => 'processing',
                ':status_delivery' => 'out for delivery'
            ]
        );

        return [
            'success' => true,
            'data' => [
                'active_orders' => $activeOrders,
                'total_products' => $this->safeCount('products'),
                'registered_users' => $this->safeCount('users')
            ]
        ];
    }
}
