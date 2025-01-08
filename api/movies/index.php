<?php
require_once "../../includes/db.php";
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        $stmt = $conn->prepare("
            SELECT m.id, m.title, m.year, m.director, m.image, m.rating, m.synopsis,
                   GROUP_CONCAT(DISTINCT g.title SEPARATOR ', ') AS genres,
                   GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS actors
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genre g ON mg.genre_id = g.id
            LEFT JOIN movie_actor ma ON m.id = ma.movie_id
            LEFT JOIN people p ON ma.actor_id = p.id
            WHERE m.id = :id
            GROUP BY m.id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(["error" => "Movie not found"]);
            exit;
        }
        echo json_encode($result);
    } else {
        $stmt = $conn->prepare("
            SELECT m.id, m.title, m.year, m.director, m.image, m.rating, m.synopsis,
                   GROUP_CONCAT(DISTINCT g.title SEPARATOR ', ') AS genres,
                   GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS actors
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genre g ON mg.genre_id = g.id
            LEFT JOIN movie_actor ma ON m.id = ma.movie_id
            LEFT JOIN people p ON ma.actor_id = p.id
            GROUP BY m.id
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }
} elseif ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $conn->prepare("
        INSERT INTO movies (title, year, director, rating, synopsis, image) 
        VALUES (:title, :year, :director, :rating, :synopsis, :image)
    ");
    $stmt->execute([
        'title' => $data['title'],
        'year' => $data['year'],
        'director' => $data['director'],
        'rating' => $data['rating'],
        'synopsis' => $data['synopsis'],
        'image' => $data['image']
    ]);
    echo json_encode(["message" => "Movie added successfully"]);
}     elseif ($requestMethod === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    error_log("PUT Data: " . print_r($data, true));

    if (!$id || !isset($data['title']) || !isset($data['year'])) {
        throw new Exception("Invalid ID or missing fields.");
    }

    $stmt = $conn->prepare("
        UPDATE movies 
        SET title = :title, year = :year, director = :director,
            image = :image, synopsis = :synopsis, rating = :rating
        WHERE id = :id
    ");
    $stmt->execute([
        'title' => $data['title'],
        'year' => intval($data['year']),
        'director' => $data['director'],
        'image' => $data['image'],
        'synopsis' => $data['synopsis'],
        'rating' => intval($data['rating']),
        'id' => $id
    ]);

    $conn->prepare("DELETE FROM movie_genre WHERE movie_id = :id")->execute(['id' => $id]);
    if (!empty($data['genres'])) {
        $stmt = $conn->prepare("INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movieId, :genreId)");
        foreach ($data['genres'] as $genreId) {
            $stmt->execute(['movieId' => $id, 'genreId' => $genreId]);
        }
    }

    $conn->prepare("DELETE FROM movie_actor WHERE movie_id = :id")->execute(['id' => $id]);
    if (!empty($data['actors'])) {
        $stmt = $conn->prepare("INSERT INTO movie_actor (movie_id, actor_id) VALUES (:movieId, :actorId)");
        foreach ($data['actors'] as $actorId) {
            $stmt->execute(['movieId' => $id, 'actorId' => $actorId]);
        }
    }

    echo json_encode(["success" => true]);
} elseif ($requestMethod === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing movie ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM movies WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(["message" => "Movie deleted successfully"]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
