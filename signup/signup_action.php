<?php
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        die("The passwords do not match eachother.");
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute(["username" => $username, "email" => $email]);
    if ($stmt->rowCount() > 0) {
        die("Username or email already taken.");
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
    if ($stmt->execute(["username" => $username, "password" => $hashed_password, "email" => $email])) {
        header("Location: /movie-database/login");
        exit;
    } else {
        die("error creating account: ");
    }
}