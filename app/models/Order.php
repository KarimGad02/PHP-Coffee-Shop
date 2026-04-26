<?php

class Order {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    //  Create a new order
    public function createOrder($userId, $roomId, $totalAmount, $notes, $items) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Insert the main order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, room_id, total_amount, notes, status) 
                VALUES (:user_id, :room_id, :total_amount, :notes, 'processing')
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':room_id' => $roomId,
                ':total_amount' => $totalAmount,
                ':notes' => $notes
            ]);

            // Get the ID of the newly created order
            $orderId = $this->db->lastInsertId();

            // Insert all order items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, historical_price) 
                VALUES (:order_id, :product_id, :quantity, :historical_price)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':quantity' => $item['quantity'],
                    ':historical_price' => $item['price']
                ]);
            }

            // Commit transaction if everything succeeded
            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId];

        } catch (PDOException $e) {
            // Roll back the transaction if anything fails
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    //  Fetch user orders, optionally filtered by date.
    public function getUserOrders($userId, $dateFrom = null, $dateTo = null) {
        $query = "SELECT * FROM orders WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        // Add date filters if they are provided
        if ($dateFrom) {
            $query .= " AND date(created_at) >= date(:date_from)";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $query .= " AND date(created_at) <= date(:date_to)";
            $params[':date_to'] = $dateTo;
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Fetch the specific items belonging to an order
    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    // Get the latest order for the user
    public function getLatestOrder($userId) {
        $stmt = $this->db->prepare("
            SELECT id FROM orders 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $latestOrder = $stmt->fetch();

        if ($latestOrder) {
            return $this->getOrderItems($latestOrder['id']);
        }
        return [];
    }

    // Cancel an order
    public function cancelOrder($orderId, $userId) {
        // First, check if the order exists, belongs to the user, and is 'processing'
        $checkStmt = $this->db->prepare("
            SELECT status FROM orders WHERE id = :id AND user_id = :user_id
        ");
        $checkStmt->execute([':id' => $orderId, ':user_id' => $userId]);
        $order = $checkStmt->fetch();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order['status'] !== 'processing') {
            return ['success' => false, 'message' => 'Only processing orders can be canceled.'];
        }

        // Update the status to 'canceled'
        $updateStmt = $this->db->prepare("
            UPDATE orders SET status = 'canceled' WHERE id = :id
        ");
        $updateStmt->execute([':id' => $orderId]);
        
        return ['success' => true, 'message' => 'Order canceled successfully.'];
    }
}