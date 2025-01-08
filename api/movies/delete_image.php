<?php
header('Content-Type: application/json');
$targetDir = "../../images/posters/";

if (isset($_GET['filename'])) {
    $file = $targetDir . basename($_GET['filename']);
    if (file_exists($file)) {
        unlink($file);
        echo json_encode(["success" => true, "message" => "File deleted."]);
    } else {
        echo json_encode(["success" => false, "message" => "File not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No filename provided."]);
}
