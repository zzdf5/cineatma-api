<?php

require_once __DIR__ . "./../services/ShowtimeService.php";

class ShowtimeController
{
    private $showtimeService;

    public function __construct($conn)
    {
        $this->showtimeService = new ShowtimeService($conn);
    }

    public function index()
    {
        header('Content-Type: application/json');

        try {
            $response = $this->showtimeService->getAllShowtimes();
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->showtimeService->getShowtimeById($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function getByMovieId($movieId)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->showtimeService->getShowtimesByMovieId($movieId);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function store()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $required = ['movie_id', 'studio_id', 'date', 'time', 'price'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing), 400);
            }

            $response = $this->showtimeService->insertShowtime(
                $data['movie_id'],
                $data['studio_id'],
                $data['date'],
                $data['time'],
                $data['price']
            );

            http_response_code(201);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->showtimeService->deleteShowtime($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function update($id)
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $required = ['movie_id', 'studio_id', 'date', 'time', 'price'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing), 400);
            }

            $response = $this->showtimeService->updateShowtime(
                $id,
                $data['movie_id'],
                $data['studio_id'],
                $data['date'],
                $data['time'],
                $data['price']
            );

            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }
}
