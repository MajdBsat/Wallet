<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/Transaction.php");

$data = json_decode(file_get_contents("php://input"), true);

$transaction = new Transaction($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($data["sender_wallet_id"], $data["recipient_email"], $data["recipient_wallet_name"], $data["amount"])) {
        $senderWalletId = intval($data["sender_wallet_id"]);
        $recipientEmail = trim($data["recipient_email"]);
        $recipientWalletName = trim($data["recipient_wallet_name"]);
        $amount = floatval($data["amount"]);

        echo $transaction->transferMoney($senderWalletId, $recipientEmail, $recipientWalletName, $amount);
    } else {
        echo json_encode(["status" => "error", "message" => "All fields (sender_wallet_id, recipient_email, recipient_wallet_name, amount) are required."]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["user_id"])) {
        $userId = intval($_GET["user_id"]);
        echo $transaction->getTransactionsByUser($userId);
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required to fetch transactions."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
