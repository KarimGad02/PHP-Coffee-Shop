<?php include_once __DIR__ . '/../includes/header.php'; ?>

<?php 
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold section-title">Admin Dashboard</h2>
        <p class="text-light opacity-75">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Here is your system overview.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="glass-card text-center py-4 metric-card">
            <h5 class="text-uppercase metric-title">Active Orders</h5>
            <h1 id="activeOrdersCount" class="fw-bold m-0 metric-value">0</h1>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="glass-card text-center py-4 metric-card">
            <h5 class="text-uppercase metric-title">Total Products</h5>
            <h1 id="totalProductsCount" class="fw-bold m-0 metric-value">0</h1>
        </div>
    </div>

    <div class="col-md-4">
        <div class="glass-card text-center py-4 metric-card">
            <h5 class="text-uppercase metric-title">Registered Users</h5>
            <h1 id="registeredUsersCount" class="fw-bold m-0 metric-value">0</h1>
        </div>
    </div>
</div>

<div class="row g-3 mt-4">
    <div class="col-md-3">
        <a href="/admin/all-users.html" class="btn btn-coffee-secondary w-100">All Users</a>
    </div>
    <div class="col-md-3">
        <a href="/admin/add-user.html" class="btn btn-coffee-primary w-100">Add User</a>
    </div>
    <div class="col-md-3">
        <a href="/admin/all-products.html" class="btn btn-coffee-secondary w-100">All Products</a>
    </div>
    <div class="col-md-3">
        <a href="/admin/add-product.html" class="btn btn-coffee-primary w-100">Add Product</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>