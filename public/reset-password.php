<?php include __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="login-container">
            <h3 class="text-center mb-4" style="color: #E0F2FE;">NEW PASSWORD</h3>
            
            <form id="resetPasswordForm">
                <input type="hidden" id="resetToken">
                
                <div class="mb-3">
                    <input type="password" id="newPassword" class="form-control" placeholder="New Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn w-100 mt-2" style="background: #FF3300; color: white; font-weight: bold;">UPDATE PASSWORD</button>
            </form>
            
            <p id="resetMessage" class="mt-3 text-center" style="display: none;"></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>