<?php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    // Login user
    public function login($email, $password) {
        $user = $this->user->getByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!$this->user->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    // Logout user
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    // Register new customer
    public function register($name, $email, $password, $room_number = null, $extension = null) {
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->password = $password;
        $this->user->room_number = $room_number;
        $this->user->extension = $extension;
        $this->user->role = 'customer';

        return $this->user->create();
    }

    // Get currently logged in user
    public function me() {
        if (!self::isLoggedIn()) {
            return ['success' => false, 'message' => 'Unauthorized', 'code' => 401];
        }

        return ['success' => true, 'data' => self::currentUser()];
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Get current user
    public static function currentUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }

    // Check if user is admin
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    // Forgot password
    public function forgotPassword($email) {
        $user = $this->user->getByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email not found'];
        }

        $token = bin2hex(random_bytes(32));
        
        $query = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reset token generated', 'token' => $token];
        }

        return ['success' => false, 'message' => 'Failed to generate reset token'];
    }

    // Reset password using token
    public function resetPassword($token, $new_password, $confirm_password) {
        if (empty($token) || empty($new_password)) {
            return ['success' => false, 'message' => 'Token and new password are required'];
        }
        
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        // Find token in the database
        $query = "SELECT email FROM password_resets WHERE token = :token ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $resetRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetRecord) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }

        // Hash the new password and update the user
        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE users SET password = :password WHERE email = :email";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':email', $resetRecord['email']);

        if ($updateStmt->execute()) {
            // Security: Delete the token so it cannot be used again
            $delQuery = "DELETE FROM password_resets WHERE email = :email";
            $delStmt = $this->db->prepare($delQuery);
            $delStmt->bindParam(':email', $resetRecord['email']);
            $delStmt->execute();

            return ['success' => true, 'message' => 'Password has been successfully reset'];
        }

        return ['success' => false, 'message' => 'Failed to reset password'];
    }
}
