<?php
header('Content-Type: application/json');

$targetDir = "../../images/posters/";
$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    parse_str(file_get_contents("php://input"), $data);
    $fileName = basename($data["filename"] ?? "");

    $filePath = $targetDir . $fileName;

    if ($fileName && file_exists($filePath)) {
        unlink($filePath);
        $response["success"] = true;
        $response["message"] = "Image deleted.";
    } else {
        $response["message"] = "File not found.";
    }
} else {
    $response["message"] = "Invalid request.";
}

echo json_encode($response);