<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg" style="background-color: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase" style="color: #FF3300; letter-spacing: 1px;" href="/">Cafeteria</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-sm btn-outline-warning logout-btn" type="button">Logout</button>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container flex-grow-1 d-flex flex-column justify-content-center py-5">