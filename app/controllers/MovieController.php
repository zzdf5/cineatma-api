<?php

require_once __DIR__ . "./../services/MovieService.php";

class MovieController
{
    private $movieService;

    public function __construct($conn)
    {
        $this->movieService = new MovieService($conn);
    }

    public function index()
    {
        header('Content-Type: application/json');

        try {
            $response = $this->movieService->getAllMovies();
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
            $response = $this->movieService->getMovieById($id);
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
            $required = ['title', 'genre', 'trailer', 'description', 'duration_minutes', 'release_date', 'director', 'cast', 'production_company', 'status'];
            $missing = [];

            foreach ($required as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing));
            }

            if (!isset($_FILES['poster'])) {
                throw new Exception("Poster file is required");
            }

            $response = $this->movieService->createMovie(
                $_POST['title'],
                $_POST['genre'],
                $_FILES['poster'],
                $_POST['trailer'],
                $_POST['description'],
                $_POST['duration_minutes'],
                $_POST['release_date'],
                $_POST['director'],
                $_POST['cast'],
                $_POST['production_company'],
                $_POST['status']
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
            $response = $this->movieService->deleteMovie($id);
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

            $required = ['title', 'genre', 'trailer', 'description', 'duration_minutes', 'release_date', 'director', 'cast', 'production_company', 'status'];
            $missing = [];

            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing));
            }

            $response = $this->movieService->updateMovie(
                $id,
                $data['title'],
                $data['genre'],
                $data['trailer'],
                $data['description'],
                $data['duration_minutes'],
                $data['release_date'],
                $data['director'],
                $data['cast'],
                $data['production_company'],
                $data['status']
            );

            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }

    public function updatePoster($id)
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_FILES['poster'])) {
                throw new Exception("Poster file is required");
            }

            $response = $this->movieService->updatePoster($id, $_FILES['poster']);

            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            HandleException::handle($e);
        }
    }
}
