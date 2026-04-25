<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If they are already logged in, redirect them to their dashboard
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: /admin/dashboard.php");
        exit();
    } else {
        header("Location: /home.php");
        exit();
    }
}
?>
<?php include_once __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="login-container">
            <h2 class="text-center mb-4">SYSTEM LOGIN</h2>
            <form id="loginForm">
                <div class="mb-3">
                    <input type="email" id="email" class="form-control" placeholder="Email Address" required>
                </div>
                <div class="mb-3">
                    <input type="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn w-100 mt-2" style="background: #FF3300; color: white; font-weight: bold;">LOGIN</button>
                
                <div class="text-center mt-3">
                    <a href="/forgot-password.php" style="color: #00B4D8; text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
                </div>
            </form>
            <p id="errorMessage" class="text-danger mt-3 text-center" style="display: none;"></p>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>