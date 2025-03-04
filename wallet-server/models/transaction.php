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

    public function transferMoney($senderWalletId, $recipientEmail, $recipientWalletName, $amount) {
        if ($amount <= 0) {
            return $this->responseError("Invalid transfer amount.");
        }
    
        $query = $this->conn->prepare("SELECT id, balance, user_id FROM wallets WHERE id = ?");
        $query->bind_param("i", $senderWalletId);
        $query->execute();
        $result = $query->get_result();
        $senderWallet = $result->fetch_assoc();
    
        if (!$senderWallet) {
            return $this->responseError("Sender wallet not found.");
        }
    
        if ($senderWallet["balance"] < $amount) {
            return $this->responseError("Insufficient funds.");
        }
    
        $query = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $query->bind_param("s", $recipientEmail);
        $query->execute();
        $result = $query->get_result();
        $recipientUser = $result->fetch_assoc();
    
        if (!$recipientUser) {
            return $this->responseError("Recipient email not found.");
        }
    
        $recipientUserId = $recipientUser["id"];
    
        $query = $this->conn->prepare("SELECT id FROM wallets WHERE user_id = ? AND wallet_name = ?");
        $query->bind_param("is", $recipientUserId, $recipientWalletName);
        $query->execute();
        $result = $query->get_result();
        $recipientWallet = $result->fetch_assoc();
    
        if (!$recipientWallet) {
            return $this->responseError("Recipient wallet not found.");
        }
    
        $recipientWalletId = $recipientWallet["id"];
    
        if ($senderWalletId == $recipientWalletId) {
            return $this->responseError("Cannot transfer to the same wallet.");
        }
    
        $this->conn->begin_transaction();
    
        try {
            $query = $this->conn->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?");
            $query->bind_param("di", $amount, $senderWalletId);
            if (!$query->execute()) {
                throw new Exception("Failed to deduct amount from sender.");
            }
    
            $query = $this->conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
            $query->bind_param("di", $amount, $recipientWalletId);
            if (!$query->execute()) {
                throw new Exception("Failed to add amount to recipient.");
            }
    
            $query = $this->conn->prepare("INSERT INTO transactions (sender_wallet_id, recipient_wallet_email, recipient_wallet_name, amount, transaction_type, transaction_time) VALUES (?, ?, ?, ?, 'transfer', NOW())");
            $query->bind_param("issd", $senderWalletId, $recipientEmail, $recipientWalletName, $amount);
            if (!$query->execute()) {
                throw new Exception("Failed to insert transaction record.");
            }
    
            $this->conn->commit();
    
            return $this->responseSuccess("Transfer successful.");
        } catch (Exception $e) {
            $this->conn->rollback();
            return $this->responseError("Transaction failed: " . $e->getMessage());
        }
    }
    
    public function getTransactionsByUser($userId){
        $query = $this->conn->prepare("SELECT t.recipient_wallet_email, t.recipient_wallet_name, t.amount, t.transaction_time FROM transactions t WHERE t.sender_wallet_id IN (SELECT id FROM wallets WHERE user_id = ?) ORDER BY t.transaction_time DESC");

        $query->bind_param("i", $userId);
        $query->execute();
        $result = $query->get_result();

        $transactions = [];
        while ($transaction = $result->fetch_assoc()) {
            $transactions[] = $transaction;
        }

        return !empty($transactions) 
            ? $this->responseSuccess("Transactions retrieved successfully.", $transactions) : $this->responseError("No transactions found.");
    }
}
?>
