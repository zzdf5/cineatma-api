<?php

require_once __DIR__ . "./../../config/database.php";

class ShowtimeModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllShowtimes()
    {
        $sql = "
        SELECT 
            s.id AS showtime_id, s.date, s.time, s.price, s.created_at AS showtime_created_at,
            
            m.id AS movie_id, m.director, m.cast, m.production_company, m.status, m.created_at AS movie_created_at,
            md.title, md.genre, md.poster, md.trailer, md.description, md.duration_minutes, md.release_date,
            
            st.id AS studio_id, st.name AS studio_name, st.seat_capacity, st.type AS studio_type, st.created_at AS studio_created_at
        FROM showtimes s
        INNER JOIN movies m ON s.movie_id = m.id
        INNER JOIN movies_detail md ON m.movie_detail_id = md.id
        INNER JOIN studios st ON s.studio_id = st.id
    ";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Failed to get showtimes: " . $this->conn->error);
        }

        $showtimes = [];
        while ($row = $result->fetch_assoc()) {
            $showtimes[] = [
                "id" => (int)$row["showtime_id"],
                "date" => $row["date"],
                "time" => $row["time"],
                "price" => (int)$row["price"],
                "movie" => [
                    "id" => (int)$row["movie_id"],
                    "director" => $row["director"],
                    "cast" => $row["cast"],
                    "production_company" => $row["production_company"],
                    "detail" => [
                        "title" => $row["title"],
                        "genre" => $row["genre"],
                        "poster" => $row["poster"],
                        "trailer" => $row["trailer"],
                        "description" => $row["description"],
                        "duration_minutes" => (int)$row["duration_minutes"],
                        "release_date" => $row["release_date"]
                    ],
                    "status" => $row["status"],
                    "created_at" => $row["movie_created_at"]
                ],
                "studio" => [
                    "id" => (int)$row["studio_id"],
                    "name" => $row["studio_name"],
                    "seat_capacity" => (int)$row["seat_capacity"],
                    "type" => $row["studio_type"],
                    "created_at" => $row["studio_created_at"]
                ],
                "created_at" => $row["showtime_created_at"]
            ];
        }

        return $showtimes;
    }

    public function getShowtimeById($id)
    {
        $stmt = $this->conn->prepare("
        SELECT 
            s.id AS showtime_id, s.date, s.time, s.price, s.created_at AS showtime_created_at,
            
            m.id AS movie_id, m.director, m.cast, m.production_company, m.status AS movie_status, m.created_at AS movie_created_at,
            md.title, md.genre, md.poster, md.trailer, md.description, md.duration_minutes, md.release_date,
            
            st.id AS studio_id, st.name AS studio_name, st.seat_capacity, st.type AS studio_type, st.created_at AS studio_created_at
        FROM showtimes s
        INNER JOIN movies m ON s.movie_id = m.id
        INNER JOIN movies_detail md ON m.movie_detail_id = md.id
        INNER JOIN studios st ON s.studio_id = st.id
        WHERE s.id = ?
    ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $showtime = $result->fetch_assoc();

        if (!$showtime) {
            return null;
        }

        return [
            "id" => (int)$showtime["showtime_id"],
            "date" => $showtime["date"],
            "time" => $showtime["time"],
            "price" => (int)$showtime["price"],
            "movie" => [
                "id" => (int)$showtime["movie_id"],
                "director" => $showtime["director"],
                "cast" => $showtime["cast"],
                "production_company" => $showtime["production_company"],
                "status" => $showtime["movie_status"],
                "detail" => [
                    "title" => $showtime["title"],
                    "genre" => $showtime["genre"],
                    "poster" => $showtime["poster"],
                    "trailer" => $showtime["trailer"],
                    "description" => $showtime["description"],
                    "duration_minutes" => (int)$showtime["duration_minutes"],
                    "release_date" => $showtime["release_date"]
                ],
                "created_at" => $showtime["movie_created_at"]
            ],
            "studio" => [
                "id" => (int)$showtime["studio_id"],
                "name" => $showtime["studio_name"],
                "seat_capacity" => (int)$showtime["seat_capacity"],
                "type" => $showtime["studio_type"],
                "created_at" => $showtime["studio_created_at"]
            ],
            "created_at" => $showtime["showtime_created_at"]
        ];
    }

    public function insertShowtime($movieId, $studioId, $date, $time, $price)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO showtimes (movie_id, studio_id, date, time, price, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("iissi", $movieId, $studioId, $date, $time, $price);
        $stmt->execute();
        return $this->getShowtimeById($stmt->insert_id);
    }

    public function deleteShowtime($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM showtimes WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
