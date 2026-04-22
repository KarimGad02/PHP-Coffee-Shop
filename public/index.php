<?php

header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/routes/web.php';

// Get database connection
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $database->getLastError() ?: 'Database connection failed'
    ]);
    exit;
}

// Initialize router
$router = new Router($db);
defineRoutes($router);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /public prefix from path if present
$path = str_replace('/public', '', $path);
if (empty($path) || $path === '/') {
    $path = '/api';
}

// Parse request body for POST/PUT requests
$body = [];
if (in_array($method, ['POST', 'PUT'])) {
    $input = file_get_contents('php://input');
    $body = json_decode($input, true) ?? [];
}

// Merge query params and body data
$params = array_merge($_GET, $body);

// Store params in a global for controllers to access
$_REQUEST_PARAMS = $params;

// Dispatch the request
$response = $router->dispatch($method, $path);

// Handle response
if (is_array($response)) {
    http_response_code($response['code'] ?? 200);
    echo json_encode($response);
} else {
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $response]);
}
