<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/db.php";

$isAdmin = false;
if (isset($_SESSION["user_id"])) {
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
    $stmt->execute(["id" => $_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = $user && $user["is_admin"] == 1;
}
?>

<header>
    <nav>
        <ul>
            <li><a href="/movie-database/">Home</a></li>
            <li><a href="#">List</a></li>
            <li><a href="#">Settings</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="/movie-database/edit/">Admin</a></li>
            <?php endif; ?>
        </ul>
        <h2>Movie Database</h2>
        <div class="auth-button">
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="/movie-database/logout.php" class="btn logout">Log out</a>
            <?php else: ?>
                <a href="/movie-database/login/" class="btn login">Log in</a>
            <?php endif; ?>
        </div>
    </nav>
</header>