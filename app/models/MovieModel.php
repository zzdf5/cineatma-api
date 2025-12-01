<?php

require_once __DIR__ . "./../../config/database.php";

class MovieModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllMovies()
    {
        $result = $this->conn->query("
            SELECT 
                m.id AS movie_id, m.director, m.cast, m.production_company, m.status, m.created_at,
                md.title, md.genre, md.poster, md.trailer, md.description, md.duration_minutes, md.release_date
            FROM movies m
            INNER JOIN movies_detail md ON m.movie_detail_id = md.id
        ");

        if (!$result) {
            throw new Exception("Failed to get movies: " . $this->conn->error);
        }

        $movies = [];

        while ($movie = $result->fetch_assoc()) {
            $movies[] = [
                "id" => (int)$movie["movie_id"],
                "director" => $movie["director"],
                "cast" => $movie["cast"],
                "production_company" => $movie["production_company"],
                "status" => $movie["status"],
                "detail" => [
                    "title" => $movie["title"],
                    "genre" => $movie["genre"],
                    "poster" => $movie["poster"],
                    "trailer" => $movie["trailer"],
                    "description" => $movie["description"],
                    "duration_minutes" => (int)$movie["duration_minutes"],
                    "release_date" => $movie["release_date"]
                ],
                "created_at" => $movie["created_at"]
            ];
        }

        return $movies;
    }

    public function getMoviesById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                m.id AS movie_id, m.director, m.cast, m.production_company, m.status, m.created_at,
                md.title, md.genre, md.poster, md.trailer, md.description, md.duration_minutes, md.release_date
            FROM movies m
            INNER JOIN movies_detail md ON m.movie_detail_id = md.id 
            WHERE m.id = ?
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $movie = $result->fetch_assoc();

        if (!$movie) return null;
        return [
            "id" => (int)$movie["movie_id"],
            "director" => $movie["director"],
            "cast" => $movie["cast"],
            "production_company" => $movie["production_company"],
            "detail" => [
                "title" => $movie["title"],
                "genre" => $movie["genre"],
                "poster" => $movie["poster"],
                "trailer" => $movie["trailer"],
                "description" => $movie["description"],
                "duration_minutes" => (int)$movie["duration_minutes"],
                "release_date" => $movie["release_date"]
            ],
            "status" => $movie["status"],
            "created_at" => $movie["created_at"]
        ];
    }

    public function insertMovie($title, $genre, $poster, $trailer, $description, $duration_minutes, $release_date, $director, $cast, $production_company, $status)
    {
        $stmtDetail = $this->conn->prepare("
            INSERT INTO movies_detail (title, genre, poster, trailer, description, duration_minutes, release_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmtDetail) throw new Exception("Failed to prepare detail insert: " . $this->conn->error);
        $stmtDetail->bind_param("sssssis", $title, $genre, $poster, $trailer, $description, $duration_minutes, $release_date);
        $stmtDetail->execute();

        $movieDetailId = $stmtDetail->insert_id;
        $stmtMovie = $this->conn->prepare("
            INSERT INTO movies (movie_detail_id, director, cast, production_company, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmtMovie) throw new Exception("Failed to prepare movie insert: " . $this->conn->error);
        $stmtMovie->bind_param("issss", $movieDetailId, $director, $cast, $production_company, $status);
        $stmtMovie->execute();

        $movieId = $stmtMovie->insert_id;
        return $this->getMoviesById($movieId);
    }

    public function deleteMovie($id)
    {
        $stmtGet = $this->conn->prepare("SELECT movie_detail_id FROM movies WHERE id = ?");
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $result = $stmtGet->get_result()->fetch_assoc();
        $movieDetailId = $result['movie_detail_id'];

        $stmtDeleteMovie = $this->conn->prepare("DELETE FROM movies WHERE id = ?");
        $stmtDeleteMovie->bind_param("i", $id);
        $stmtDeleteMovie->execute();

        $stmtDeleteDetail = $this->conn->prepare("DELETE FROM movies_detail WHERE id = ?");
        $stmtDeleteDetail->bind_param("i", $movieDetailId);
        return $stmtDeleteDetail->execute();
    }

    public function countShowtimesByMovieId($movieId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM showtimes WHERE movie_id = ?");
        $stmt->bind_param("i", $movieId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['c'];
    }

    public function updateMovie($id, $title, $genre, $trailer, $description, $duration_minutes, $release_date, $director, $cast, $production_company, $status)
    {
        $stmtGet = $this->conn->prepare("SELECT movie_detail_id FROM movies WHERE id = ?");
        if (!$stmtGet) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $result = $stmtGet->get_result()->fetch_assoc();
        if (!$result) {
            throw new Exception("Movie not found", 404);
        }

        $movieDetailId = $result['movie_detail_id'];

        $stmtDetail = $this->conn->prepare("
            UPDATE movies_detail
            SET title = ?, genre = ?, trailer = ?, description = ?, duration_minutes = ?, release_date = ?
            WHERE id = ?
        ");
        if (!$stmtDetail) throw new Exception("Failed to prepare statement: " . $this->conn->error);
        $stmtDetail->bind_param(
            "ssssiss",
            $title,
            $genre,
            $trailer,
            $description,
            $duration_minutes,
            $release_date,
            $movieDetailId
        );
        $stmtDetail->execute();

        $stmtMovie = $this->conn->prepare("
            UPDATE movies
            SET director = ?, cast = ?, production_company = ?, status = ?
            WHERE id = ?
        ");
        if (!$stmtMovie) throw new Exception("Failed to prepare statement: " . $this->conn->error);
        $stmtMovie->bind_param(
            "ssssi",
            $director,
            $cast,
            $production_company,
            $status,
            $id
        );
        $stmtMovie->execute();

        return $this->getMoviesById($id);
    }

    public function updatePoster($id, $poster)
    {
        $stmtGet = $this->conn->prepare("SELECT movie_detail_id FROM movies WHERE id = ?");
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $result = $stmtGet->get_result()->fetch_assoc();

        if (!$result) {
            throw new Exception("Movie not found", 404);
        }

        $movieDetailId = $result['movie_detail_id'];

        $stmt = $this->conn->prepare("
            UPDATE movies_detail
            SET poster = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $poster, $movieDetailId);
        $stmt->execute();

        return $this->getMoviesById($id);
    }
}
