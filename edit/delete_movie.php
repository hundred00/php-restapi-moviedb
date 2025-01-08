<?php
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $movieId = intval($_GET['id'] ?? 0);

    try {
        $conn->prepare("DELETE FROM movie_genre WHERE movie_id = :id")->execute(["id" => $movieId]);
        $conn->prepare("DELETE FROM movie_actor WHERE movie_id = :id")->execute(["id" => $movieId]);
        $conn->prepare("DELETE FROM movies WHERE id = :id")->execute(["id" => $movieId]);

        http_response_code(200);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}