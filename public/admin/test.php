<?php
require_once __DIR__ . '/../../app/config/Database.php';
$db = (new Database())->connect();


$result = $db->query("SELECT * FROM orders");

foreach ($result as $row) {
    print_r($row);
}