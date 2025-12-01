<?php

require_once __DIR__ . "./../models/BookingModel.php";
require_once __DIR__ . "./../models/UserModel.php";
require_once __DIR__ . "./../models/ShowtimeModel.php";

class BookingService
{
    private $bookingModel;
    private $userModel;
    private $showtimeModel;

    public function __construct($conn)
    {
        $this->bookingModel = new BookingModel($conn);
        $this->userModel = new UserModel($conn);
        $this->showtimeModel = new ShowtimeModel($conn);
    }

    public function getAllBookings()
    {
        $bookings = $this->bookingModel->getAllBookings();
        return [
            "success" => true,
            "data" => $bookings
        ];
    }

    public function getBookingById($id)
    {
        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) {
            throw new Exception("Booking not found", 404);
        }

        return [
            "success" => true,
            "data" => $booking
        ];
    }

    public function getBookingsByUserId($userId)
    {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            throw new Exception("User not found", 404);
        }

        $bookings = $this->bookingModel->getAllBookingsByUserId($userId);
        return [
            "success" => true,
            "data" => $bookings
        ];
    }

    public function getBookingsByShowtimeId($showtimeId)
    {
        $showtime = $this->showtimeModel->getShowtimeById($showtimeId);
        if (!$showtime) {
            throw new Exception("Showtime not found", 404);
        }

        $bookings = $this->bookingModel->getAllBookingsByShowtimeId($showtimeId);
        return [
            "success" => true,
            "data" => $bookings
        ];
    }

    public function createBooking($userId, $showtimeId, $seatCode, $paymentMethod)
    {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            throw new Exception("User not found", 404);
        }

        $showtime = $this->showtimeModel->getShowtimeById($showtimeId);
        if (!$showtime) {
            throw new Exception("Showtime not found", 404);
        }

        $validPayments = ['bank', 'credit_card', 'e-wallet'];
        if (!in_array($paymentMethod, $validPayments)) {
            throw new Exception("Invalid payment method", 400);
        }

        $booking = $this->bookingModel->insertBooking($userId, $showtimeId, $seatCode, $paymentMethod);

        return [
            "success" => true,
            "message" => "Booking created successfully",
            "data" => $booking
        ];
    }

    public function deleteBooking($id)
    {
        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) {
            throw new Exception("Booking not found", 404);
        }

        $this->bookingModel->deleteBooking($id);

        return [
            "success" => true,
            "message" => "Booking deleted successfully"
        ];
    }
}
