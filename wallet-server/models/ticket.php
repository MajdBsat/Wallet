<?php
class Ticket
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

    public function createTicket($userName, $subject, $description)
    {
        if (empty($userName) || empty($subject) || empty($description)) {
            return $this->responseError("User name, subject, and description are required.");
        }

        $query = $this->conn->prepare("INSERT INTO tickets (user_name, subject, description) VALUES (?, ?, ?)");
        $query->bind_param("sss", $userName, $subject, $description);
        $success = $query->execute();

        if ($success) {
            return $this->responseSuccess("Ticket created successfully", [
                "ticket_id" => $this->conn->insert_id,
                "user_name" => $userName,
                "subject" => $subject,
                "description" => $description
            ]);
        } else {
            return $this->responseError("Failed to create ticket.");
        }
    }
}
?>
