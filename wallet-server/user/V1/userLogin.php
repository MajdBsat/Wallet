<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    if (isset($data['email'], $data['password'])) {
        $email = trim($data['email']);
        $password = trim($data['password']);

        $checkQuery = "SELECT id, name, email, phone_number, pass, FROM users WHERE email = ?";
        $query = $conn->prepare($checkQuery);
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                echo json_encode(["status" => "success", "message" => "Login successful.", "user" => $user]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid password."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No user found with that email."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
