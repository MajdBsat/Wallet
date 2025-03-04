<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include("../../connection/connection.php");
include("../../models/wallet.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['user_id'], $data['wallet_name'], $data['amount'])) {
        $userId = trim($data['user_id']);
        $walletName = trim($data['wallet_name']);
        $amount = trim($data['amount']);

        $wallet = new Wallet($conn);
        echo $wallet->withdrawMoney($userId, $walletName, $amount);
    } else {
        echo json_encode(["status" => "error", "message" => "User ID, wallet name, and amount are required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>
