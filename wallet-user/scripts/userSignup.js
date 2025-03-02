document.getElementById("SignupForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const userData = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        phone: document.getElementById("phone").value,
        password: document.getElementById("password").value
    };

    axios.post("http://localhost/Wallet/wallet-server/user/V1/userSignup.php", userData, {
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(response => {
        console.log(response.data);
        alert(response.data.message);
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
    });
});

