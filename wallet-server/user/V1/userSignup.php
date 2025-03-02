<?php

include("../connection/connection.php")

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["name"], $_POST["email"], $_POST["phone"], $_POST["password"])) {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $phone = trim($_POST["phone"]);
        $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);

        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $query = $conn->prepare($checkQuery);
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $existingUser = $result->fetch_assoc();

        if ($existingUser) {
            echo json_encode(["status" => "error", "message" => "Email already registered."]);
            exit;
        }

        $insertQuery = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $query = $conn->prepare($insertQuery);
        $query->bind_param("ssss", $name, $email, $phone, $password);

        if ($query->execute()) {
            echo json_encode(["status" => "success", "message" => "User registered successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Registration failed. Try again."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>