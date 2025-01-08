<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../includes/db.php";

// check admin access
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    die("Access Denied. You must be an admin to view this page.");
}

function fetchDataFromApi($endpoint)
{
    $apiUrl = "http://localhost/movie-database/api/$endpoint";
    $response = file_get_contents($apiUrl);
    return json_decode($response, true);
}

$movies = fetchDataFromApi("movies");
$genres = fetchDataFromApi("genre");
$actors = fetchDataFromApi("people");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/movie-database/style.css">
    <title>Admin | Edit Movies</title>
</head>

<body>
    <?php include "../includes/header.php"; ?>

    <main>
        <h1>Admin - Edit Movies</h1>
        <div class="admin-container">
            <section class="edit-form">
                <form id="editForm">
                    <h2 id="editingMovieName">Editing: None</h2>
                    <input type="hidden" id="movieId">

                    <div class="edit-form-data">
                        <div id="imagePreviewContainer">
                            <img id="imagePreview" class="preview-image" alt="Image Preview" src="../images/posters/poster-missing.jpg">
                            <p id="imageError" style="color: red; display: none;">Image not found!</p>
                        </div>

                        <div class="edit-form-general">
                            <label>Title:</label>
                            <input type="text" id="title" placeholder="Title" required>

                            <label>Year:</label>
                            <input type="number" id="year" placeholder="Year released" required>

                            <label>Director:</label>
                            <input type="text" id="director" placeholder="Director name" required>

                            <div class="edit-form-image">
                                <label>Image:</label>
                                <input type="text" id="image" placeholder="poster-missing.jpg">
                                <div class="edit-form-imageupload">
                                    <input type="file" id="imageUpload" accept=".jpg, .jpeg, .png">
                                </div>
                            </div>
                        </div>

                        <div class="edit-form-dropdowns">
                            <label>Genres:</label>
                            <div class="list-container">
                                <div id="selectedGenres" class="list"></div>
                                <div class="dropdown-button">
                                    <select id="genreDropdown">
                                        <?php foreach ($genres as $genre): ?>
                                            <option value="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['title']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="addGenre()">Add</button>
                                </div>
                            </div>

                            <label>Actors:</label>
                            <div class="list-container">
                                <div id="selectedActors" class="list"></div>
                                <div class="dropdown-button">
                                    <select id="actorDropdown">
                                        <?php foreach ($actors as $actor): ?>
                                            <option value="<?php echo $actor['id']; ?>"><?php echo htmlspecialchars($actor['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="addActor()">Add</button>
                                </div>
                            </div>
                            <div class="rating-slider">
                                <label>Rating:</label>
                                <input type="range" id="rating" min="1" max="5" step="1" value="3">
                                <span id="ratingValue">3</span> / 5
                            </div>
                        </div>

                        <div class="edit-form-synopsis">
                            <label>Synopsis:</label>
                            <textarea id="synopsis" placeholder="Movie synopsis" rows="3"></textarea>
                        </div>
                    </div>

                    <button id="saveButton" type="submit" disabled>Save Changes</button>
                    <button id="deleteButton" type="button" style="display:none;">Delete Movie</button>
                    <button id="revertButton" type="button" style="display:none;">Revert</button>
                </form>
            </section>

            <hr />
            <section class="movie-list">
                <div class="movie-item" data-id="new" data-type="add">
                    <div class="overlay" style="font-size: 10em;">+</div>
                </div>
                <?php foreach ($movies as $movie): ?>
                    <div class="movie-item"
                        style="background-image: url('/movie-database/images/posters/<?php echo htmlspecialchars($movie['image']); ?>');"
                        data-id="<?php echo $movie['id']; ?>"
                        data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                        data-year="<?php echo htmlspecialchars($movie['year']); ?>"
                        data-director="<?php echo htmlspecialchars($movie['director']); ?>"
                        data-rating="<?php echo htmlspecialchars($movie['rating']); ?>"
                        data-synopsis="<?php echo htmlspecialchars($movie['synopsis']); ?>"
                        data-image="<?php echo htmlspecialchars($movie['image']); ?>">
                        <div class="overlay"><?php echo htmlspecialchars($movie['title']); ?></div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>
    </main>

    <script src="/movie-database/scripts/editMovieUI.js"></script>
    <script src="/movie-database/scripts/editMovieActions.js"></script>
</body>

</html>