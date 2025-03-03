<?php
class Transaction
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

    public function transferMoney($senderWalletId, $recipientWalletId, $amount)
    {
        if ($senderWalletId == $recipientWalletId) {
            return $this->responseError("Cannot transfer to the same wallet.");
        }

        if ($amount <= 0) {
            return $this->responseError("Invalid transfer amount.");
        }

        $query = $this->conn->prepare("SELECT balance FROM wallets WHERE id = ?");
        $query->bind_param("i", $senderWalletId);
        $query->execute();
        $result = $query->get_result();
        $senderWallet = $result->fetch_assoc();

        if (!$senderWallet || $senderWallet["balance"] < $amount) {
            return $this->responseError("Insufficient funds.");
        }

        $query = $this->conn->prepare("SELECT id FROM wallets WHERE id = ?");
        $query->bind_param("i", $recipientWalletId);
        $query->execute();
        $result = $query->get_result();
        if ($result->num_rows === 0) {
            return $this->responseError("Recipient wallet not found.");
        }

        $this->conn->begin_transaction();

        try {
            $query = $this->conn->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?");
            $query->bind_param("di", $amount, $senderWalletId);
            $query->execute();

            $query = $this->conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
            $query->bind_param("di", $amount, $recipientWalletId);
            $query->execute();

            $query = $this->conn->prepare("INSERT INTO transactions (sender_wallet_id, recipient_wallet_id, amount, transaction_type, transaction_time) VALUES (?, ?, ?, 'transfer', NOW())");
            $query->bind_param("iid", $senderWalletId, $recipientWalletId, $amount);
            $query->execute();

            $this->conn->commit();

            return $this->responseSuccess("Transfer successful.");
        } catch (Exception $e) {
            $this->conn->rollback();
            return $this->responseError("Transaction failed.");
        }
    }

    public function getTransactionsByUser($userId)
    {
        $query = $this->conn->prepare("SELECT t.* FROM transactions t INNER JOIN wallets w ON t.sender_wallet_id = w.id OR t.recipient_wallet_id = w.id WHERE w.user_id = ? ORDER BY t.transaction_time DESC");
        $query->bind_param("i", $userId);
        $query->execute();
        $result = $query->get_result();

        $transactions = [];
        while ($transaction = $result->fetch_assoc()) {
            $transactions[] = $transaction;
        }

        return !empty($transactions) ? $this->responseSuccess("Transactions retrieved successfully.", $transactions) : $this->responseError("No transactions found.");
    }
}
?>
