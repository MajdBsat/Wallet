<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");

$data = json_decode(file_get_contents("php://input"), true) ?? [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($data["name"], $data["email"], $data["phone"], $data["password"])) {
        $name = trim($data["name"]);
        $email = trim($data["email"]);
        $phone = trim($data["phone"]);
        $password = password_hash(trim($data["password"]), PASSWORD_DEFAULT);

        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $query = $conn->prepare($checkQuery);
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Email already registered."]);
            exit;
        }

        $insertQuery = "INSERT INTO users (name, email, phone_number, pass) VALUES (?, ?, ?, ?)";
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