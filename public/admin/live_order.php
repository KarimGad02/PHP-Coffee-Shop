<?php

require_once __DIR__ . '/../../app/config/Database.php';

$db = (new Database())->connect();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $allowedStatuses = ['processing', 'out for delivery', 'done','canceled'];

    if (in_array($status, $allowedStatuses)) {

        $stmt = $db->prepare("
            UPDATE orders 
            SET status = :status 
            WHERE id = :id
        ");

        $stmt->execute([
            ':status' => $status,
            ':id' => $order_id
        ]);
    }

    header("Location: /admin/live_order.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';


$orders = $db->query("
    SELECT 
        orders.*, 
        users.name AS user_name, 
        rooms.name AS room_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN rooms ON orders.room_id = rooms.id
    ORDER BY orders.created_at DESC
");
?>

<div class="container mt-5">
    <h2 class="mb-4">Live Orders</h2>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Room</th>
                <th>Total</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= $order['user_name'] ?></td>
                    <td><?= $order['room_name'] ?></td>
                    <td><?= $order['total_amount'] ?></td>
                    <td><?= $order['notes'] ?></td>

                    <td>
                        <form method="POST" style="display:flex; gap:10px;">
                            
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                            <select name="status" class="form-select">
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>
                                    Processing
                                </option>
                                <option value="out for delivery" <?= $order['status'] == 'out for delivery' ? 'selected' : '' ?>>
                                    Out for Delivery
                                </option>
                                <option value="done" <?= $order['status'] == 'done' ? 'selected' : '' ?>>
                                    Done
                                </option>
                                <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>
                                    Canceled
                                </option>
                            </select>

                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>

                        </form>
                    </td>

                    <td><?= $order['created_at'] ?></td>

                    <td>—</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>