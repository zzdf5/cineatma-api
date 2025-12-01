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
            $response = $this->movieService->getMovieById($id);
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
            $response = $this->movieService->deleteMovie($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
