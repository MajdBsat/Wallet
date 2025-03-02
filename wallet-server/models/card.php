<?php
class Card
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    private function responseSuccess($message, $data = [])
    {
        return json_encode(["status" => "success", "message" => $message, "data" => $data]);
    }

    private function responseError($message)
    {
        return json_encode(["status" => "error", "message" => $message]);
    }

    public function createCard($walletId, $cardNumber, $cvvCode, $expiryDate)
    {
        if (empty($walletId) || empty($cardNumber) || empty($cvvCode) || empty($expiryDate)) {
            return $this->responseError("Wallet ID, card number, CVV, and expiry date are required.");
        }

        $checkQuery = $this->conn->prepare("SELECT id FROM Cards WHERE wallet_id = ?");
        $checkQuery->bind_param("i", $walletId);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows > 0) {
            return $this->responseError("This wallet already has a card.");
        }

        $query = $this->conn->prepare("INSERT INTO Cards (wallet_id, card_number, cvv_code, expiry_date) VALUES (?, ?, ?, ?)");
        $query->bind_param("isss", $walletId, $cardNumber, $cvvCode, $expiryDate);
        $success = $query->execute();

        if ($success) {
            return $this->responseSuccess("Card created successfully", [
                "card_id" => $this->conn->insert_id,
                "wallet_id" => $walletId,
                "card_number" => $cardNumber,
                "cvv_code" => $cvvCode,
                "expiry_date" => $expiryDate
            ]);
        } else {
            return $this->responseError("Failed to create card.");
        }
    }

    public function deleteCard($walletId)
    {
        if (empty($walletId)) {
            return $this->responseError("Wallet ID is required.");
        }

        $checkQuery = $this->conn->prepare("SELECT id FROM Cards WHERE wallet_id = ?");
        $checkQuery->bind_param("i", $walletId);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows === 0) {
            return $this->responseError("Card not found for the specified wallet.");
        }

        $query = $this->conn->prepare("DELETE FROM Cards WHERE wallet_id = ?");
        $query->bind_param("i", $walletId);
        $success = $query->execute();

        return $success ? $this->responseSuccess("Card deleted successfully.") : $this->responseError("Failed to delete card.");
    }

    public function viewCard($walletId)
    {
        if (empty($walletId)) {
            return $this->responseError("Wallet ID is required.");
        }

        $query = $this->conn->prepare("SELECT card_number, cvv_code, expiry_date FROM Cards WHERE wallet_id = ?");
        $query->bind_param("i", $walletId);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 0) {
            return $this->responseError("No card found for the specified wallet.");
        }

        $card = $result->fetch_assoc();

        return $this->responseSuccess("Card details retrieved successfully.", [
            "card_number" => $card["card_number"],
            "cvv_code" => $card["cvv_code"],
            "expiry_date" => $card["expiry_date"]
        ]);
    }
}
?>
