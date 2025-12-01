<?php

require_once __DIR__ . "./../services/BookingService.php";

class BookingController
{
    private $bookingService;

    public function __construct($conn)
    {
        $this->bookingService = new BookingService($conn);
    }

    public function index()
    {
        header('Content-Type: application/json');

        try {
            $response = $this->bookingService->getAllBookings();
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->bookingService->getBookingById($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getByUserId($userId)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->bookingService->getBookingsByUserId($userId);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getByShowtimeId($showtimeId)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->bookingService->getBookingsByShowtimeId($showtimeId);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $required = ['user_id', 'showtime_id', 'seat_code', 'payment_method'];
            $missing = [];

            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing), 400);
            }

            $response = $this->bookingService->createBooking(
                $data['user_id'],
                $data['showtime_id'],
                $data['seat_code'],
                $data['payment_method']
            );

            http_response_code(201);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->bookingService->deleteBooking($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
