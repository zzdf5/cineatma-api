<?php

require_once __DIR__ . "./../../config/database.php";

class BookingModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllBookings()
    {
        $result = $this->conn->query("SELECT * FROM bookings");
        if (!$result) {
            throw new Exception("Failed to get bookings: " . $this->conn->error);
        }

        $bookings = [];
        while ($booking = $result->fetch_assoc()) {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function getBookingById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE id = ?");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        return $booking ?: null;
    }

    public function getAllBookingsByUserId($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $bookings = [];
        while ($booking = $result->fetch_assoc()) {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function getAllBookingsByShowtimeId($showtimeId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE showtime_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $showtimeId);
        $stmt->execute();

        $result = $stmt->get_result();
        $bookings = [];
        while ($booking = $result->fetch_assoc()) {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function insertBooking($userId, $showtimeId, $seatCode, $paymentMethod)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO bookings (user_id, showtime_id, seat_code, payment_method, booked_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("iiss", $userId, $showtimeId, $seatCode, $paymentMethod);
        $stmt->execute();

        return $this->getBookingById($stmt->insert_id);
    }

    public function deleteBooking($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM bookings WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function countBookingsByShowtimeId($showtimeId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM bookings WHERE showtime_id = ?");
        $stmt->bind_param("i", $showtimeId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['c'];
    }

    public function countBookingsByUserId($userId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM bookings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['c'];
    }
}
