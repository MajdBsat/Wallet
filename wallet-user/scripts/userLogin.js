document.getElementById('LoginForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    axios.post('http://localhost/Wallet/wallet-server/user/V1/userLogin.php', {
        email: email,
        password: password
    })
    .then(function(response) {
        console.log('Login successful:', response);

        if (response.data.status === 'success') {
            window.location.href = '../pages/userMain.html';
        } else {
            alert('Login failed. Please check your credentials.');
        }
    })
    .catch(function(error) {
        console.error('Login error:', error);
        alert('Login failed. Please check your credentials.');
    });
});