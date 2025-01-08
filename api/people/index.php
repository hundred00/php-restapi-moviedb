<?php
require_once "../../includes/db.php";
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM people WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(["error" => "Person not found"]);
            exit;
        }
        echo json_encode($result);
    } else {
        $stmt = $conn->prepare("SELECT * FROM people");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }
}

elseif ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Person name is required"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO people (name) VALUES (:name)");
    $stmt->execute(['name' => $data['name']]);
    echo json_encode(["message" => "Person added successfully"]);
}

elseif ($requestMethod === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);

    $id = intval($_GET['id'] ?? 0);
    if (!$id || empty($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing ID or name"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE people SET name = :name WHERE id = :id");
    $stmt->execute(['name' => $data['name'], 'id' => $id]);
    echo json_encode(["message" => "Person updated successfully"]);
}

elseif ($requestMethod === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing person ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM people WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(["message" => "Person deleted successfully"]);
}

else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}