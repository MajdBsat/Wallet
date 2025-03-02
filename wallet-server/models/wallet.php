<?php
class Wallet
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

    public function createWallet($userId, $walletName)
    {
        if (empty($userId) || empty($walletName)) {
            return $this->responseError("User ID and wallet name are required.");
        }

        $checkQuery = $this->conn->prepare("SELECT id FROM Wallets WHERE user_id = ? AND name = ?");
        $checkQuery->bind_param("is", $userId, $walletName);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows > 0) {
            return $this->responseError("Wallet name already exists.");
        }

        $balance = 0;
        $query = $this->conn->prepare("INSERT INTO Wallets (user_id, name, balance) VALUES (?, ?, ?)");
        $query->bind_param("isd", $userId, $walletName, $balance);
        $success = $query->execute();

        if ($success) {
            return $this->responseSuccess("Wallet created successfully", [
                "wallet_id" => $this->conn->insert_id,
                "user_id" => $userId,
                "name" => $walletName,
                "balance" => $balance
            ]);
        } else {
            return $this->responseError("Failed to create wallet.");
        }
    }

    public function deleteWallet($userId, $walletName)
    {
        if (empty($userId) || empty($walletName)) {
            return $this->responseError("User ID and wallet name are required.");
        }

        $checkQuery = $this->conn->prepare("SELECT id FROM Wallets WHERE user_id = ? AND name = ?");
        $checkQuery->bind_param("is", $userId, $walletName);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows === 0) {
            return $this->responseError("Wallet not found or does not belong to the user.");
        }

        $query = $this->conn->prepare("DELETE FROM Wallets WHERE user_id = ? AND name = ?");
        $query->bind_param("is", $userId, $walletName);
        $success = $query->execute();

        return $success ? $this->responseSuccess("Wallet deleted successfully.") : $this->responseError("Failed to delete wallet.");
    }

    public function depositMoney($userId, $walletName, $amount)
    {
        if (empty($userId) || empty($walletName) || empty($amount) || $amount <= 0) {
            return $this->responseError("User ID, wallet name, and a valid deposit amount are required.");
        }

        $checkQuery = $this->conn->prepare("SELECT id, balance FROM Wallets WHERE user_id = ? AND name = ?");
        $checkQuery->bind_param("is", $userId, $walletName);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows === 0) {
            return $this->responseError("Wallet not found.");
        }

        $wallet = $checkResult->fetch_assoc();
        $newBalance = $wallet["balance"] + $amount;

        $updateQuery = $this->conn->prepare("UPDATE Wallets SET balance = ? WHERE user_id = ? AND name = ?");
        $updateQuery->bind_param("dis", $newBalance, $userId, $walletName);
        $success = $updateQuery->execute();

        return $success ? $this->responseSuccess("Deposit successful.", ["wallet_name" => $walletName, "new_balance" => $newBalance])
                        : $this->responseError("Failed to deposit money.");
    }

    public function getUserWallets($userId)
    {
        if (empty($userId)) {
            return $this->responseError("User ID is required.");
        }

        $query = $this->conn->prepare("SELECT * FROM Wallets WHERE user_id = ? ORDER BY id DESC");
        $query->bind_param("i", $userId);
        $query->execute();
        $result = $query->get_result();
        $wallets = [];

        while ($wallet = $result->fetch_assoc()) {
            $wallets[] = $wallet;
        }

        return json_encode(["status" => "success", "wallets" => $wallets]);
    }
}
?>
