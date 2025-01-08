<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "movie_database";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("connection to db failed because: " . $e->getMessage());
}
