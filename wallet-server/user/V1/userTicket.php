<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/ticket.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['user_email'], $data['subject'], $data['description'])) {
        $userEmail = trim($data['user_email']);
        $subject = trim($data['subject']);
        $description = trim($data['description']);

        $ticket = new Ticket($conn);
        echo $ticket->createTicket($userEmail, $subject, $description);
    } else {
        echo json_encode(["status" => "error", "message" => "User email, subject, and description are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
