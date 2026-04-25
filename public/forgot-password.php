<?php include_once __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="login-container">
            <h3 class="text-center mb-4" style="color: #E0F2FE;">RECOVER PASSWORD</h3>
            
            <form id="forgotPasswordForm">
                <div class="mb-3">
                    <p class="text-light" style="font-size: 0.9rem;">Enter your email address and we will send you a secure reset link.</p>
                    <input type="email" id="resetEmail" class="form-control" placeholder="Email Address" required>
                </div>
                <button type="submit" class="btn w-100 mt-2" style="background: #FF3300; color: white; font-weight: bold;">SEND LINK</button>
                <div class="text-center mt-3">
                    <a href="/login.php" style="color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.9rem;">Back to Login</a>
                </div>
            </form>

            <div id="simulatedEmailBox" class="mt-4 p-3 rounded" style="display: none; background: rgba(0, 180, 216, 0.1); border: 1px solid #00B4D8;">
                <p class="text-light mb-2" style="font-size: 0.85rem;"><strong>[SIMULATED EMAIL SYSTEM]</strong><br>An email has been sent to the user with the following link:</p>
                <a id="resetLinkUrl" href="#" style="color: #00B4D8; font-weight: bold; word-break: break-all;">Click here to reset password</a>
            </div>

            <p id="forgotErrorMessage" class="text-danger mt-3 text-center" style="display: none;"></p>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>