<?php
$targetDir = "../images/posters/";
$response = ["success" => false];

if (isset($_FILES["imageUpload"]) && $_FILES["imageUpload"]["error"] == 0) {
    $fileName = basename($_FILES["imageUpload"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ["jpg", "jpeg", "png"];
    if (!in_array($imageFileType, $allowedTypes)) {
        $response["message"] = "Unsupported file type.";
    } elseif ($_FILES["imageUpload"]["size"] > 5 * 1024 * 1024) {
        $response["message"] = "File is too large.";
    } elseif (move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $targetFile)) {
        $response["success"] = true;
        $response["filename"] = $fileName;
    } else {
        $response["message"] = "Failed to upload image.";
    }
} else {
    $response["message"] = "No file uploaded or upload error.";
}

echo json_encode($response);