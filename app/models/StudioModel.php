<?php

require_once __DIR__ . "./../../config/database.php";

class StudioModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllStudios()
    {
        $result = $this->conn->query("SELECT * FROM studios");
        if (!$result) {
            throw new Exception("Failed to get studios: " . $this->conn->error);
        }

        $studios = [];
        while ($studio = $result->fetch_assoc()) {
            $studios[] = $studio;
        }

        return $studios;
    }

    public function getStudioById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM studios WHERE id = ?");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $studio = $result->fetch_assoc();

        return $studio ?: null;
    }

    public function insertStudio($name, $seatCapacity, $type)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO studios (name, seat_capacity, type, created_at) 
            VALUES (?, ?, ?, NOW())
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("sis", $name, $seatCapacity, $type);
        $stmt->execute();
        return $this->getStudioById($stmt->insert_id);
    }

    public function deleteStudio($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM studios WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function countShowtimesByStudioId($studioId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM showtimes WHERE studio_id = ?");
        $stmt->bind_param("i", $studioId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['c'];
    }
}
