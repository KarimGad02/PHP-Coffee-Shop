<?php
require_once __DIR__ . '/../models/Products.php';

class ProductController {
    private $db;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new Product($db);
    }

    /**
     * Get only available products for the customer home page
     */
    public function getMenu() {
        $products = $this->productModel->getAvailable();
        return ['success' => true, 'data' => $products];
    }
}