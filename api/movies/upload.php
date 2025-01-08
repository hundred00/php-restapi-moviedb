<?php
require_once "../../includes/db.php";
header('Content-Type: application/json');

$targetDir = "../../images/posters/";
$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["imageUpload"])) {
    $file = $_FILES["imageUpload"];
    $fileName = basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ["jpg", "jpeg", "png"];
    if (!in_array($imageFileType, $allowedTypes)) {
        $response["message"] = "Unsupported file type.";
    } elseif ($file["size"] > 5 * 1024 * 1024) {
        $response["message"] = "File size exceeds limit (5MB).";
    } elseif (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $response["success"] = true;
        $response["filename"] = $fileName;
    } else {
        $response["message"] = "Failed to upload image.";
    }
} else {
    $response["message"] = "No file uploaded.";
}

echo json_encode($response);
