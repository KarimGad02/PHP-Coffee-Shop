<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the name of the current file being accessed
$currentPage = basename($_SERVER['PHP_SELF']);

// Define pages that DO NOT require a login
$publicPages = ['login.php', 'forgot-password.php', 'reset-password.php'];

// If the user is not logged in AND trying to access a protected page, redirect them
if (!isset($_SESSION['user_id']) && !in_array($currentPage, $publicPages)) {
    header("Location: login.php");
    exit();
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
    
    <script>
        const CURRENT_USER_ID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null' ?>;
        const CURRENT_USER_NAME = "<?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '' ?>";
    </script>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase" style="color: var(--accent-primary); letter-spacing: 1px;" href="home.php">Cafeteria</a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav align-items-center gap-3">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'home.php' ? 'active' : '' ?>" href="home.php" style="<?= $currentPage === 'home.php' ? 'color: var(--accent-secondary) !important;' : 'color: var(--text-muted);' ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'my-orders.php' ? 'active' : '' ?>" href="my-orders.php" style="<?= $currentPage === 'my-orders.php' ? 'color: var(--accent-secondary) !important;' : 'color: var(--text-muted);' ?>">My Orders</a>
                        </li>
                        
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);">
                                <img src="/images/default-avatar.png" alt="Profile" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover; border: 2px solid var(--accent-primary);">
                                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown" style="background: rgba(35, 22, 18, 0.95); border: 1px solid var(--glass-border);">
                                <li>
                                    <button class="dropdown-item text-danger" onclick="logout()">Logout</button>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        function logout() {
            fetch('/auth/logout', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                });
        }
    </script>

    <div class="container flex-grow-1 d-flex flex-column py-4">