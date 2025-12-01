<?php

require_once __DIR__ . "./../models/ShowtimeModel.php";
require_once __DIR__ . "./../models/MovieModel.php";
require_once __DIR__ . "./../models/StudioModel.php";
require_once __DIR__ . "./../models/BookingModel.php";

class ShowtimeService
{
    private $showtimeModel;
    private $movieModel;
    private $studioModel;
    private $bookingModel;

    public function __construct($conn)
    {
        $this->showtimeModel = new ShowtimeModel($conn);
        $this->movieModel = new MovieModel($conn);
        $this->studioModel = new StudioModel($conn);
        $this->bookingModel = new BookingModel($conn);
    }

    public function getAllShowtimes()
    {
        $showtimes = $this->showtimeModel->getAllShowtimes();
        return [
            'success' => true,
            'data' => $showtimes
        ];
    }

    public function getShowtimeById($id)
    {
        $showtime = $this->showtimeModel->getShowtimeById($id);
        if (!$showtime) {
            throw new Exception('Showtime not found', 404);
        }

        return [
            'success' => true,
            'data' => $showtime
        ];
    }

    public function insertShowtime($movieId, $studioId, $date, $time, $price)
    {
        $movie = $this->movieModel->getMoviesById($movieId);
        if (!$movie) {
            throw new Exception('Movie not found', 404);
        }

        $studio = $this->studioModel->getStudioById($studioId);
        if (!$studio) {
            throw new Exception('Studio not found', 404);
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            throw new Exception('Invalid date format, expected YYYY-MM-DD', 400);
        }

        $t = DateTime::createFromFormat('H:i:s', $time);
        if (!$t || $t->format('H:i:s') !== $time) {
            throw new Exception('Invalid time format, expected HH:MM:SS', 400);
        }

        $showtime = $this->showtimeModel->insertShowtime($movieId, $studioId, $date, $time, $price);
        if (!$showtime) {
            throw new Exception('Failed to create showtime');
        }

        return [
            'success' => true,
            'message' => 'Showtime created successfully',
            'data' => $showtime
        ];
    }

    public function deleteShowtime($id)
    {
        $showtime = $this->showtimeModel->getShowtimeById($id);
        if (!$showtime) {
            throw new Exception('Showtime not found', 404);
        }

        $bookingCount = $this->bookingModel->countBookingsByShowtimeId($id);
        if ($bookingCount > 0) {
            throw new Exception("Cannot delete showtime: there are $bookingCount bookings still using this showtime", 400);
        }

        $deleted = $this->showtimeModel->deleteShowtime($id);
        if (!$deleted) {
            throw new Exception('Failed to delete showtime');
        }

        return [
            'success' => true,
            'message' => 'Showtime deleted successfully'
        ];
    }
}
