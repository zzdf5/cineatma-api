<?php

require_once __DIR__ . "./../../config/database.php";

class UserModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createUser($username, $name, $phone, $email, $password, $city, $role = 'user')
    {
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, avatar, name, phone, email, password, city, role, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $avatars = ["boy_1.png", "boy_2.png", "boy_3.png", "girl_1.png", "girl_2.png", "girl_3.png"];
        $avatarUrl = "/avatar/" . $avatars[random_int(0, count($avatars) - 1)];

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bind_param("ssssssss", $username, $avatarUrl, $name, $phone, $email, $hashedPassword, $city, $role);

        return $stmt->execute();
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user ?: null;
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user ?: null;
    }

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user ?: null;
    }

    public function updateProfile($id, $username, $name, $phone, $city)
    {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET username = ?, name = ?, phone = ?, city = ? WHERE id = ?
        ");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("ssssi", $username, $name, $phone, $city, $id);
        return $stmt->execute();
    }

    public function updateAvatar($id, $avatarPath)
    {
        $stmt = $this->conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("si", $avatarPath, $id);
        return $stmt->execute();
    }

    public function updatePassword($id, $password)
    {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) throw new Exception("Failed to prepare statement: " . $this->conn->error);

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }
}
