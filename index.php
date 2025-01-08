<?php
session_start();
require_once "includes/db.php";

$apiUrl = "http://localhost/movie-database/api/movies/index.php";
$response = file_get_contents($apiUrl);
$movies = json_decode($response, true);

$isLoggedIn = isset($_SESSION["user_id"]);
$userFavorites = [];

if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT favorites FROM users WHERE id = :id");
    $stmt->execute(["id" => $_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userFavorites = $user['favorites'] ? explode(",", $user['favorites']) : [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Movie Database</title>
</head>

<body>
    <?php include "includes/header.php"; ?>

    <main>
        <h1>Movies</h1>

        <article class="search-container">
            <input type="text" id="searchInput" placeholder="Search by movie title..." onkeyup="filterMovies()">
            <select id="sortSelect" onchange="filterMovies()">
                <option value="id">Sort by ID</option>
                <option value="alphabetical">Alphabetical</option>
                <option value="release_date">Newest Release</option>
            </select>
        </article>

        <article id="movieContainer">
            <?php foreach ($movies as $movie): ?>
                <section class="movie" data-title="<?php echo htmlspecialchars($movie["title"]); ?>" data-year="<?php echo htmlspecialchars($movie["year"]); ?>" data-id="<?php echo htmlspecialchars($movie["id"]); ?>">
                    <section>
                        <h2><?php echo htmlspecialchars($movie["title"]); ?> <span class="movie-info-year">(<?php echo htmlspecialchars($movie["year"]); ?>)</span></h2>
                        <p><strong>Director:</strong> <?php echo htmlspecialchars($movie["director"]); ?></p>
                        <p><strong>Genres:</strong>
                            <?php
                            $genres = $movie["genres"];
                            $genreNames = array_map(fn($genre) => ucwords(strtolower($genre['title'])), $genres);
                            echo htmlspecialchars(implode(', ', $genreNames));
                            ?>
                        </p>
                        <p><strong>Actors:</strong>
                            <?php
                            $actors = $movie["actors"];
                            $actorNames = array_map(fn($actor) => htmlspecialchars($actor['name']), $actors);
                            echo implode(', ', $actorNames);
                            ?>
                        </p>
                        <p class="rating"><strong>Rating:</strong>
                            <span class="rating-stars">
                                <?php
                                $rating = intval($movie["rating"]);
                                for ($i = 0; $i < $rating; $i++): ?>
                                    <img src="images/rating-star.png" alt="Star">
                                <?php endfor; ?>
                            </span>
                        </p>
                        <p><strong>Synopsis:</strong> <?php echo htmlspecialchars($movie["synopsis"]); ?></p>

                        <?php if ($isLoggedIn): ?>
                            <button class="favorite-btn <?php echo in_array($movie['id'], $userFavorites) ? 'favorite' : ''; ?>"
                                data-id="<?php echo $movie['id']; ?>">
                            </button>
                        <?php endif; ?>
                    </section>
                    <section>
                        <img src="images/posters/<?php echo htmlspecialchars($movie["image"]); ?>" alt="Poster of <?php echo htmlspecialchars($movie["title"]); ?>">
                    </section>
                </section>
            <?php endforeach; ?>
        </article>
    </main>

    <script src="scripts/movieFilter.js"></script>
    <script src="scripts/favoriteToggle.js"></script>
</body>

</html>