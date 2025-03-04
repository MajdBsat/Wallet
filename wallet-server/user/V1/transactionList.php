<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/Transaction.php");

$transaction = new Transaction($conn);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["user_id"])) {
        $userId = intval($_GET["user_id"]);
        echo $transaction->getTransactionsByUser($userId);
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
