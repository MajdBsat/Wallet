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

    public function createTicket($userEmail, $subject, $description)
    {
        if (empty($userEmail) || empty($subject) || empty($description)) {
            return $this->responseError("User email, subject, and description are required.");
        }

        $query = $this->conn->prepare("INSERT INTO tickets (user_email, subject, description) VALUES (?, ?, ?)");
        $query->bind_param("sss", $userEmail, $subject, $description);
        $success = $query->execute();

        if ($success) {
            return $this->responseSuccess("Ticket created successfully", [
                "ticket_id" => $this->conn->insert_id,
                "user_email" => $userEmail,
                "subject" => $subject,
                "description" => $description
            ]);
        } else {
            return $this->responseError("Failed to create ticket.");
        }
    }
}
?>
