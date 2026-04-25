<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /admin/dashboard.php');
    exit();
}

include_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center align-items-center g-4 py-4">
    <div class="col-lg-7">
        <div class="hero-panel p-5">
            <span class="eyebrow">Fresh coffee, fast service</span>
            <h1 class="display-5 fw-bold mt-3 mb-3">Welcome to Cafeteria</h1>
            <p class="lead text-light-emphasis mb-4">
                Your coffee shop ordering space for drinks, snacks, and quick room delivery.
                Browse the menu, manage your account, and keep your day moving.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="/login.php" class="btn btn-coffee-primary">Back to Login</a>
                <button type="button" class="btn btn-coffee-secondary logout-btn">Logout</button>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="glass-card feature-stack">
            <div class="feature-item">
                <h5>Made for the office</h5>
                <p>Warm drinks, simple browsing, and room-based delivery flow.</p>
            </div>
            <div class="feature-item">
                <h5>Fast admin control</h5>
                <p>Users, categories, and products can be managed from one place.</p>
            </div>
            <div class="feature-item mb-0">
                <h5>Clean customer route</h5>
                <p>Customers now land here instead of a missing page.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
