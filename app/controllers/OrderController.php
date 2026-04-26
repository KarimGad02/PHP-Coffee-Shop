<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Products.php';

class OrderController {
    private $db;
    private $orderModel;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->orderModel = new Order($db);
        $this->productModel = new Product($db);
    }

    // Process a new order from the cart
    public function placeOrder($user_id, $room_id, $notes = '', $items = []) {
        if (empty($user_id) || empty($room_id) || empty($items)) {
            return ['success' => false, 'message' => 'Missing required order details.', 'code' => 400];
        }

        $totalAmount = 0;
        $processedItems = [];

        // Validate items and calculate the total securely on the backend
        foreach ($items as $item) {
            $product = $this->productModel->getById($item['product_id']);
            
            if (!$product || $product['is_available'] == 0) {
                return ['success' => false, 'message' => 'A selected product is unavailable.', 'code' => 400];
            }

            $totalAmount += ($product['price'] * $item['quantity']);
            
            $processedItems[] = [
                'product_id' => $product['id'],
                'quantity' => $item['quantity'],
                'price' => $product['price'] // Lock in historical price
            ];
        }

        $result = $this->orderModel->createOrder($user_id, $room_id, $totalAmount, $notes, $processedItems);
        return $result;
    }

    //  Get orders for the logged-in user (with optional date filters)
    public function getMyOrders($user_id, $date_from = null, $date_to = null) {
        $orders = $this->orderModel->getUserOrders($user_id, $date_from, $date_to);
        
        // Fetch items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->orderModel->getOrderItems($order['id']);
        }

        return ['success' => true, 'data' => $orders];
    }

    // Cancel an order
    public function cancelOrder($id) {
        // Ensure the session is started so we can access $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get the user ID securely from the backend session, NOT the frontend
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthorized. Please log in.', 'code' => 401];
        }

        $user_id = $_SESSION['user_id'];

        // Pass both to the model (the route provides $id, the session provides $user_id)
        return $this->orderModel->cancelOrder($id, $user_id);
    }
    
    // Get the latest order for the Home page
    public function getLatestOrder($user_id) {
        $items = $this->orderModel->getLatestOrder($user_id);
        return ['success' => true, 'data' => $items];
    }
}