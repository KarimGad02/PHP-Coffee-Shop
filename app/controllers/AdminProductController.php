<?php

require_once __DIR__ . '/../models/Products.php';
require_once __DIR__ . '/../models/Category.php';

class AdminProductController {
    private $db;
    private $product;
    private $category;

    public function __construct($db) {
        $this->db = $db;
        $this->product = new Product($db);
        $this->category = new Category($db);
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

    private function storeUploadedImage($file, $targetFolder = 'products') {
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

    public function updateProduct($id, $name, $price, $category_id = null, $is_available = 1, $image = null) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $existing = $this->product->getById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Product not found', 'code' => 404];
        }

        $storedImage = $this->storeUploadedImage($image, 'products');

        $this->product->id = (int)$id;
        $this->product->name = $name;
        $this->product->price = (float)$price;
        $this->product->category_id = $category_id !== null && $category_id !== '' ? (int)$category_id : null;
        $this->product->is_available = (int)$is_available === 1 ? 1 : 0;
        $this->product->image = $storedImage ?: ($existing['image'] ?? null);

        return $this->product->update();
    }

    public function getAllProducts() {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        return ['success' => true, 'data' => $this->product->getAll()];
    }

    public function getProductById($id) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $product = $this->product->getById($id);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found', 'code' => 404];
        }

        return ['success' => true, 'data' => $product];
    }

    public function createProduct($name, $price, $category_id = null, $is_available = 1, $image = null) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $storedImage = $this->storeUploadedImage($image, 'products');

        $this->product->name = $name;
        $this->product->price = (float)$price;
        $this->product->category_id = $category_id !== null && $category_id !== '' ? (int)$category_id : null;
        $this->product->is_available = (int)$is_available === 1 ? 1 : 0;
        $this->product->image = $storedImage;

        return $this->product->create();
    }

    public function deleteProduct($id) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        return $this->product->delete($id);
    }

    public function getAllCategories() {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        return ['success' => true, 'data' => $this->category->getAll()];
    }

    public function createCategory($name) {
        $auth = $this->ensureAdmin();
        if ($auth) {
            return $auth;
        }

        $trimmed = trim((string)$name);
        if ($trimmed === '') {
            return ['success' => false, 'message' => 'Category name is required', 'code' => 422];
        }

        $this->category->name = $trimmed;

        try {
            return $this->category->create();
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Category already exists', 'code' => 409];
        }
    }
}
