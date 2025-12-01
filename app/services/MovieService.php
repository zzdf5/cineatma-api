<?php

require_once __DIR__ . "./../models/MovieModel.php";

class MovieService
{
    private $movieModel;

    public function __construct($conn)
    {
        $this->movieModel = new MovieModel($conn);
    }

    public function getAllMovies()
    {
        $movies = $this->movieModel->getAllMovies();
        return [
            "success" => true,
            "data" => $movies
        ];
    }

    public function getMovieById($id)
    {
        $movie = $this->movieModel->getMoviesById($id);
        if (!$movie) {
            throw new Exception("Movie not found", 404);
        }

        return [
            "success" => true,
            "data" => $movie
        ];
    }

    public function createMovie($title, $genre, $posterFile, $trailer, $description, $duration_minutes, $release_date, $director, $cast, $production_company, $status)
    {
        if (!isset($posterFile) || $posterFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Poster file is required", 400);
        }

        $ext = pathinfo($posterFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid('poster_') . '.' . $ext;
        $targetDir = __DIR__ . '/../../public/poster/';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
            throw new Exception("Failed to create poster directory", 500);
        }

        $targetPath = $targetDir . $filename;

        if (!move_uploaded_file($posterFile['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload poster file", 500);
        }

        $posterPath = '/poster/' . $filename;

        $movie = $this->movieModel->insertMovie(
            $title,
            $genre,
            $posterPath,
            $trailer,
            $description,
            $duration_minutes,
            $release_date,
            $director,
            $cast,
            $production_company,
            $status
        );

        if (!$movie) {
            throw new Exception("Failed to create movie", 500);
        }

        return [
            "success" => true,
            "message" => "Movie created successfully",
            "data" => $movie
        ];
    }

    public function deleteMovie($id)
    {
        $movie = $this->movieModel->getMoviesById($id);
        if (!$movie) {
            throw new Exception("Movie not found", 404);
        }

        $showtimeCount = $this->movieModel->countShowtimesByMovieId($id);
        if ($showtimeCount > 0) {
            throw new Exception("Cannot delete movie: there are $showtimeCount showtimes still using this movie", 400);
        }

        $posterPath = __DIR__ . '/../../public' . $movie['detail']['poster'];
        if (file_exists($posterPath)) {
            if (!unlink($posterPath)) {
                throw new Exception("Failed to delete poster file", 500);
            }
        }

        $deleted = $this->movieModel->deleteMovie($id);
        if (!$deleted) {
            throw new Exception("Failed to delete movie", 500);
        }

        return [
            "success" => true,
            "message" => "Movie deleted successfully"
        ];
    }
}
