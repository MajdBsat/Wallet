<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/user.php");

$data = json_decode(file_get_contents("php://input"), true);


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($data["name"], $data["email"], $data["phone"], $data["password"])) {
        $name = trim($data["name"]);
        $email = trim($data["email"]);
        $phone = trim($data["phone"]);
        $password = trim($data["password"]);

        $user = new User($conn);
        echo $user->signUp($name, $email, $phone, $password);
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
