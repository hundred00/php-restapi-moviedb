<?php
require_once "../../includes/db.php";
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PATCH');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // unauthorized
    echo json_encode(["error" => "Unauthorized access. Please log in."]);
    exit;
}

$userId = $_SESSION['user_id'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'PATCH') {
    $data = json_decode(file_get_contents("php://input"), true);
    $movieId = $data['movieId'] ?? null;

    if (!$movieId) {
        http_response_code(400);
        echo json_encode(["error" => "Missing movie ID."]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT favorites FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $favorites = $user['favorites'] ? explode(",", $user['favorites']) : [];

        if (in_array($movieId, $favorites)) {
            $favorites = array_diff($favorites, [$movieId]);
        } else {
            $favorites[] = $movieId;
        }

        $updatedFavorites = implode(",", $favorites);
        $stmt = $conn->prepare("UPDATE users SET favorites = :favorites WHERE id = :userId");
        $stmt->execute(['favorites' => $updatedFavorites, 'userId' => $userId]);

        echo json_encode(["success" => true, "favorites" => $favorites]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
