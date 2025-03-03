<?php
class User
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

    public function signUp($name, $email, $phone, $password){

    $query = $this->conn->prepare("SELECT id FROM Users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        return $this->responseError("User already exists");
    }
    $query->close();

    $hashedPassword = $this->hashPassword($password);
    $is_verified = 0;

    $query = $this->conn->prepare("INSERT INTO Users (name, email, phone_number, pass, is_verified) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("ssssi", $name, $email, $phone, $hashedPassword, $is_verified);
    $success = $query->execute();

    if ($success) {
        return $this->responseSuccess("User added successfully", [
            "id" => $this->conn->insert_id,
            "email" => $email,
            "is_verified" => $is_verified
        ]);
    } else {
        return $this->responseError("Failed to signup User");
    }
}



    public function signIn($email, $password)
    {
        if (empty($email) || empty($password)) {
            return $this->responseError("Missing field is required.");
        }

        $query = $this->conn->prepare("SELECT id, pass FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        if ($user && $this->verifyPassword($password, $user["pass"])) {
            return $this->responseSuccess("Sign in successful", ["id" => $user["id"], "email" => $email]);
        } else {
            return $this->responseError("Wrong Email or Password");
        }
    }

    public function getUserById($id)
    {
        if (empty($id)) {
            return $this->responseError("User ID is required");
        }

        $query = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        return $user ? $this->responseSuccess("User found", $user) : $this->responseError("User not found");
    }

    public function update($id, $email)
    {
        if (empty($id)) {
            return $this->responseError("User ID is required");
        }

        $query = $this->conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $query->bind_param("si", $email, $id);
        $success = $query->execute();

        return $success ? $this->responseSuccess("User updated successfully") : $this->responseError("Failed to update User");
    }
}
?>
