
// 1. LOGIN FLOW
const loginForm = document.getElementById('loginForm');

if (loginForm) {
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Stop the form from reloading the page

        // Grab the values typed into the HTML
        const emailInput = document.getElementById('email').value;
        const passwordInput = document.getElementById('password').value;

        // Use a relative path! Because we are already on localhost:8000
        fetch('/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin', // Use this for embedded apps to keep sessions
            body: JSON.stringify({
                email: emailInput,
                password: passwordInput
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to the appropriate page based on the user's role
                if (data.user.role === 'admin') {
                    window.location.href = '/admin/dashboard.php'; 
                } else {
                    window.location.href = '/home.php'; 
                }
            } else {
                // Show the error on the screen
                const errorMsg = document.getElementById('errorMessage');
                errorMsg.innerText = data.message;
                errorMsg.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    });
}

// 2. FORGOT PASSWORD FLOW
const forgotForm = document.getElementById('forgotPasswordForm');

if (forgotForm) {
    forgotForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('resetEmail').value;
        const errorMsg = document.getElementById('forgotErrorMessage');
        const simBox = document.getElementById('simulatedEmailBox');
        const linkUrl = document.getElementById('resetLinkUrl');

        // Hide old messages
        errorMsg.style.display = 'none';
        simBox.style.display = 'none';

        fetch('/auth/forgot-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Show the simulated email link
                linkUrl.href = `/reset-password.php?token=${data.token}`;
                simBox.style.display = 'block';
            } else {
                errorMsg.innerText = data.message;
                errorMsg.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    });
}


// 3. RESET PASSWORD FLOW

const resetForm = document.getElementById('resetPasswordForm');

if (resetForm) {
    // On page load, grab the token from the URL and put it in the hidden input
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (token) {
        document.getElementById('resetToken').value = token;
    } else {
        const msgBox = document.getElementById('resetMessage');
        msgBox.innerText = "Invalid or missing token.";
        msgBox.className = "text-danger mt-3 text-center";
        msgBox.style.display = 'block';
        resetForm.style.display = 'none'; // Hide form if no token
    }

    resetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const submitToken = document.getElementById('resetToken').value;
        const msgBox = document.getElementById('resetMessage');

        if (newPassword !== confirmPassword) {
            msgBox.innerText = "Passwords do not match!";
            msgBox.className = "text-danger mt-3 text-center";
            msgBox.style.display = 'block';
            return;
        }

        fetch('/auth/reset-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                token: submitToken,
                new_password: newPassword,
                confirm_password: confirmPassword
            })
        })
        .then(res => res.json())
        .then(data => {
            msgBox.style.display = 'block';
            if (data.success) {
                msgBox.className = "text-success mt-3 text-center fw-bold";
                msgBox.innerText = "Password updated! Redirecting to login...";
                setTimeout(() => {
                    window.location.href = '/login.php';
                }, 2000);
            } else {
                msgBox.className = "text-danger mt-3 text-center";
                msgBox.innerText = data.message;
            }
        })
        .catch(error => console.error('Error:', error));
    });

// 4. GLOBAL LOGOUT FLOW
function logoutSystem() {
    fetch('/auth/logout', { method: 'POST' })
    .then(() => {
        window.location.href = '/login.php';
    })
    .catch(error => console.error('Error logging out:', error));
}
}