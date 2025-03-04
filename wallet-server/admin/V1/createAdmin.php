<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/Admin.php");

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($data["name"], $data["email"], $data["phone_number"], $data["password"])) {
        $name = trim($data["name"]);
        $email = trim($data["email"]);
        $phoneNumber = trim($data["phone_number"]);
        $password = trim($data["password"]);

        $admin = new Admin($conn);
        echo $admin->signUp($name, $email, $phoneNumber, $password);
    } else {
        echo json_encode(["status" => "error", "message" => "Name, email, phone number, and password are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>

