<?php
require_once "../../includes/db.php";
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM genre WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(["error" => "Genre not found"]);
            exit;
        }
        echo json_encode($result);
    } else {
        $stmt = $conn->prepare("SELECT * FROM genre");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }
}

elseif ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['title'])) {
        http_response_code(400);
        echo json_encode(["error" => "Genre title is required"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO genre (title) VALUES (:title)");
    $stmt->execute(['title' => $data['title']]);
    echo json_encode(["message" => "Genre added successfully"]);
}

elseif ($requestMethod === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);

    $id = intval($_GET['id'] ?? 0);
    if (!$id || empty($data['title'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing ID or title"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE genre SET title = :title WHERE id = :id");
    $stmt->execute(['title' => $data['title'], 'id' => $id]);
    echo json_encode(["message" => "Genre updated successfully"]);
}

elseif ($requestMethod === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing genre ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM genre WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(["message" => "Genre deleted successfully"]);
}

else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}