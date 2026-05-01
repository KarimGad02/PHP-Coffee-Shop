<?php

require_once __DIR__ . '/../../app/config/Database.php';

$db = (new Database())->connect();

$users = $db->query("SELECT id, name FROM users");

$user_id   = $_GET['user_id']   ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';
$sort      = $_GET['sort']       ?? 'date';

$query = "
    SELECT 
        orders.*, 
        users.name AS user_name,
        rooms.name AS room_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN rooms ON orders.room_id = rooms.id
    WHERE 1=1
";

$params = [];

if (!empty($user_id)) {
    $query .= " AND orders.user_id = :user_id";
    $params[':user_id'] = $user_id;
}

if (!empty($startDate)) {
    $query .= " AND DATE(orders.created_at) >= :start_date";
    $params[':start_date'] = $startDate;
}

if (!empty($endDate)) {
    $query .= " AND DATE(orders.created_at) <= :end_date";
    $params[':end_date'] = $endDate;
}

switch ($sort) {
    case 'name':
        $query .= " ORDER BY users.name ASC";
        break;
    case 'price':
        $query .= " ORDER BY orders.total_amount ASC";
        break;
    default:
        $query .= " ORDER BY orders.created_at DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Filter Orders</h2>

    <form method="GET" class="row g-3 mb-4">

        <div class="col-md-3">
            <label>User</label>
            <select name="user_id" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"
                        <?= $user_id == $user['id'] ? 'selected' : '' ?>>
                        <?= $user['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="<?= $startDate ?>">
        </div>

        <div class="col-md-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control"
                   value="<?= $endDate ?>">
        </div>

        <div class="col-md-3">
            <label>Sort By</label>
            <select name="sort" class="form-select">
                <option value="date" <?= $sort == 'date' ? 'selected' : '' ?>>Date</option>
                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>User Name</option>
                <option value="price" <?= $sort == 'price' ? 'selected' : '' ?>>Total Price</option>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>

    </form>

    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Room</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="6">No results found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['user_name'] ?></td>
                        <td><?= $order['room_name'] ?></td>
                        <td><?= $order['total_amount'] ?></td>
                        <td><?= $order['status'] ?></td>
                        <td><?= $order['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>