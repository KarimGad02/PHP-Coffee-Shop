<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12 text-center text-light">
        <h2>Welcome to the Admin Dashboard</h2>
        <p>You have successfully logged in!</p>
        <button onclick="logout()" class="btn btn-outline-light mt-3">Test Logout</button>
    </div>
</div>

<script>
function logout() {
    fetch('/auth/logout', { method: 'POST' })
    .then(() => window.location.href = '/login.php');
}
</script>

<?php include '../includes/footer.php'; ?>