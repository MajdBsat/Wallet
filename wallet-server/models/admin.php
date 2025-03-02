<?php
class Admin
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    private function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }

    private function responseSuccess($message, $data = [])
    {
        return json_encode(["status" => "success", "message" => $message, "data" => $data]);
    }

    private function responseError($message)
    {
        return json_encode(["status" => "error", "message" => $message]);
    }

    public function signUp($email, $password)
    {
        $query = $this->conn->prepare("SELECT id FROM Admins WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            return $this->responseError("Admin already exists");
        }
        $query->close();

        $hashedPassword = $this->hashPassword($password);

        $query = $this->conn->prepare("INSERT INTO Admins (email, password) VALUES (?, ?)");
        $query->bind_param("ss", $email, $hashedPassword);
        $success = $query->execute();

        if ($success) {
            return $this->responseSuccess("Admin added successfully", ["id" => $this->conn->insert_id, "email" => $email]);
        } else {
            return $this->responseError("Failed to signup Admin");
        }
    }

    public function signIn($email, $password)
    {
        if (empty($email) || empty($password)) {
            return $this->responseError("Missing field is required.");
        }

        $query = $this->conn->prepare("SELECT id, password FROM Admins WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $Admin = $result->fetch_assoc();

        if ($Admin && $this->verifyPassword($password, $Admin["password"])) {
            return $this->responseSuccess("Sign in successful", ["id" => $Admin["id"], "email" => $email]);
        } else {
            return $this->responseError("Wrong Email or Password");
        }
    }

    public function resetPassword($id, $newPassword)
    {
        if (empty($id) || empty($newPassword)) {
            return $this->responseError("Missing field is required.");
        }

        $query = $this->conn->prepare("SELECT id FROM Admins WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 0) {
            return $this->responseError("No Admin found with this ID");
        }

        $hashedPassword = $this->hashPassword($newPassword);
        $query = $this->conn->prepare("UPDATE Admins SET password = ? WHERE id = ?");
        $query->bind_param("si", $hashedPassword, $id);
        $success = $query->execute();

        return $success ? $this->responseSuccess("Password reset successfully.") : $this->responseError("Failed to reset password.");
    }

    public function getAllAdmins()
    {
        $query = $this->conn->prepare("SELECT * FROM Admins ORDER BY id DESC");
        $query->execute();
        $result = $query->get_result();
        $Admins = [];

        while ($Admin = $result->fetch_assoc()) {
            $Admins[] = $Admin;
        }

        return json_encode($Admins);
    }

    public function getAdminById($id)
    {
        if (empty($id)) {
            return $this->responseError("Admin ID is required");
        }

        $query = $this->conn->prepare("SELECT * FROM Admins WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();
        $Admin = $result->fetch_assoc();

        return $Admin ? $this->responseSuccess("Admin found", $Admin) : $this->responseError("Admin not found");
    }

    public function update($id, $email)
    {
        if (empty($id)) {
            return $this->responseError("Admin ID is required");
        }

        $query = $this->conn->prepare("UPDATE Admins SET email = ? WHERE id = ?");
        $query->bind_param("si", $email, $id);
        $success = $query->execute();

        return $success ? $this->responseSuccess("Admin updated successfully") : $this->responseError("Failed to update Admin");
    }

    public function delete($id)
    {
        if (empty($id)) {
            return $this->responseError("Admin ID is missing");
        }

        $query = $this->conn->prepare("DELETE FROM Admins WHERE ID = ?");
        $query->bind_param("s", $id);
        $success = $query->execute();

        return $success ? $this->responseSuccess("Admin deleted successfully") : $this->responseError("Failed to delete Admin");
    }
}
?>
