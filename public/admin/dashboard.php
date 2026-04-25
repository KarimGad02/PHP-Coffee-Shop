<?php include_once __DIR__ . '/../includes/header.php'; ?>

<?php 
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #FF5722;">Admin Dashboard</h2>
        <p class="text-light opacity-75">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Here is your system overview.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-uppercase" style="color: #E0F2FE; font-size: 0.9rem;">Active Orders</h5>
            <h1 class="fw-bold m-0" style="color: #00B4D8;">0</h1>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-uppercase" style="color: #E0F2FE; font-size: 0.9rem;">Total Products</h5>
            <h1 class="fw-bold m-0" style="color: #00B4D8;">0</h1>
        </div>
    </div>

    <div class="col-md-4">
        <div class="glass-card text-center py-4">
            <h5 class="text-uppercase" style="color: #E0F2FE; font-size: 0.9rem;">Registered Users</h5>
            <h1 class="fw-bold m-0" style="color: #00B4D8;">0</h1>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>