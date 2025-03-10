<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/user.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['email'], $data['password'])) {
        $email = trim($data['email']);
        $password = trim($data['password']);

        $user = new User($conn);
        echo $user->signIn($email, $password);
    } else {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
