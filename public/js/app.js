// Inside public/js/app.js
document.getElementById('loginForm').addEventListener('submit', function(event) {
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
            alert("Login successful! Welcome Admin.");
            // Later, you can redirect them here: window.location.href = '/dashboard.html';
        } else {
            // Show the error on the screen
            document.getElementById('errorMessage').innerText = data.message;
            document.getElementById('errorMessage').style.display = 'block';
        }
    });
});