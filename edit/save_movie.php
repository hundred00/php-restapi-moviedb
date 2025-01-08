<?php
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movieId = isset($_POST["movie_id"]) ? intval($_POST["movie_id"]) : 0;
    $title = trim($_POST["title"]);
    $year = intval($_POST["year"]);
    $director = trim($_POST["director"]);
    $image = trim($_POST["image"]);

    $genres = isset($_POST["genres"]) && is_array($_POST["genres"]) ? array_map("intval", $_POST["genres"]) : [];
    $actors = isset($_POST["actors"]) && is_array($_POST["actors"]) ? array_map("intval", $_POST["actors"]) : [];

    try {
        if ($movieId > 0) {
            $stmt = $conn->prepare("
               UPDATE movies 
               SET title = :title, year = :year, director = :director, image = :image, synopsis = :synopsis, rating = :rating
               WHERE id = :id
            ");
            $stmt->execute([
                "title" => $title,
                "year" => $year,
                "director" => $director,
                "image" => $image,
                "synopsis" => $synopsis,
                "rating" => intval($_POST["rating"]),
                "id" => $movieId
            ]);

            $existingGenres = $conn->prepare("SELECT genre_id FROM movie_genre WHERE movie_id = :id");
            $existingGenres->execute(["id" => $movieId]);
            $existingGenreIds = $existingGenres->fetchAll(PDO::FETCH_COLUMN);

            foreach ($genres as $genreId) {
                if (!in_array($genreId, $existingGenreIds)) {
                    $conn->prepare("
                        INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movieId, :genreId)
                    ")->execute(["movieId" => $movieId, "genreId" => $genreId]);
                }
            }

            foreach ($existingGenreIds as $existingGenreId) {
                if (!in_array($existingGenreId, $genres)) {
                    $conn->prepare("
                        DELETE FROM movie_genre WHERE movie_id = :movieId AND genre_id = :genreId
                    ")->execute(["movieId" => $movieId, "genreId" => $existingGenreId]);
                }
            }

            $existingActors = $conn->prepare("SELECT actor_id FROM movie_actor WHERE movie_id = :id");
            $existingActors->execute(["id" => $movieId]);
            $existingActorIds = $existingActors->fetchAll(PDO::FETCH_COLUMN);

            foreach ($actors as $actorId) {
                if (!in_array($actorId, $existingActorIds)) {
                    $conn->prepare("
                        INSERT INTO movie_actor (movie_id, actor_id) VALUES (:movieId, :actorId)
                    ")->execute(["movieId" => $movieId, "actorId" => $actorId]);
                }
            }

            foreach ($existingActorIds as $existingActorId) {
                if (!in_array($existingActorId, $actors)) {
                    $conn->prepare("
                        DELETE FROM movie_actor WHERE movie_id = :movieId AND actor_id = :actorId
                    ")->execute(["movieId" => $movieId, "actorId" => $existingActorId]);
                }
            }

            echo json_encode(["success" => true, "message" => "Movie saved successfully!", "id" => $movieId]);
            exit;
        } else {
            $stmt = $conn->prepare("
                INSERT INTO movies (title, year, director, image, synopsis, rating) 
                VALUES (:title, :year, :director, :image, :synopsis, :rating)
            ");
            $stmt->execute([
                "title" => $title,
                "year" => $year,
                "director" => $director,
                "image" => $image,
                "synopsis" => trim($_POST["synopsis"]),
                "rating" => intval($_POST["rating"])
            ]);
            $newMovieId = $conn->lastInsertId();

            foreach ($genres as $genreId) {
                $conn->prepare("
                    INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movieId, :genreId)
                ")->execute(["movieId" => $newMovieId, "genreId" => $genreId]);
            }

            foreach ($actors as $actorId) {
                $conn->prepare("
                    INSERT INTO movie_actor (movie_id, actor_id) VALUES (:movieId, :actorId)
                ")->execute(["movieId" => $newMovieId, "actorId" => $actorId]);
            }

            echo json_encode(["success" => true, "message" => "Movie created successfully!", "id" => $newMovieId]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
