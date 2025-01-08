async function fetchMovieDetails(id) {
    try {
        const response = await fetch(`/movie-database/api/movies?id=${id}`)
        const data = await response.json()

        populateForm(data)
        enableSaveButton()
    } catch (error) {
        console.error("Failed to fetch movie details:", error)
    }
}

function populateForm(movie) {
    document.getElementById("movieId").value = movie.id
    document.getElementById("title").value = movie.title
    document.getElementById("year").value = movie.year
    document.getElementById("director").value = movie.director
    document.getElementById("image").value = movie.image
    document.getElementById("synopsis").value = movie.synopsis

    const rating = parseInt(movie.rating)
    document.getElementById("rating").value = rating
    document.getElementById("ratingValue").innerText = `${rating}`

    selectedGenres = [];
    if (movie.genres) {
        const genreArray = movie.genres.split(", ")
        genreArray.forEach(title => {
            const genre = { id: null, title }
            if (!selectedGenres.find(g => g.title === title)) {
                selectedGenres.push(genre)
            }
        })
    }

    selectedActors = []
    if (movie.actors) {
        const actorArray = movie.actors.split(", ")
        actorArray.forEach(name => {
            const actor = { id: null, name }
            if (!selectedActors.find(a => a.name === name)) {
                selectedActors.push(actor)
            }
        })
    }

    updateSelectedLists()
    updateImagePreview()
}

async function saveMovie() {
    const movieId = document.getElementById("movieId").value || null
    const method = movieId ? "PUT" : "POST"

    const movieData = {
        title: document.getElementById("title").value,
        year: parseInt(document.getElementById("year").value),
        director: document.getElementById("director").value,
        image: document.getElementById("image").value,
        synopsis: document.getElementById("synopsis").value,
        rating: parseInt(document.getElementById("rating").value),

        genres: selectedGenres
            .map(genre => parseInt(genre.id))
            .filter(id => !isNaN(id) && id > 0),

        actors: selectedActors
            .map(actor => parseInt(actor.id))
            .filter(id => !isNaN(id) && id > 0)
    };

    try {
        const response = await fetch(`/movie-database/api/movies${movieId ? "?id=" + movieId : ""}`, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(movieData)
        })

        const contentType = response.headers.get("content-type")
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Invalid JSON response.")
        }

        const result = await response.json()
        if (result.success) {
            alert("Movie saved successfully!")
            window.location.reload()
        } else {
            alert("Error saving movie! " + (result.message || ""))
        }
    } catch (error) {
        console.error("Error saving movie:", error)
        alert("Failed to save movie.")
    }
}

async function deleteMovie() {
    const movieId = document.getElementById("movieId").value

    if (!confirm("Are you sure you want to delete this movie?")) return

    try {
        const response = await fetch(`/movie-database/api/movies?id=${movieId}`, {
            method: "DELETE"
        });

        if (response.ok) {
            alert("Movie deleted successfully!")

            deletedMovieData = {
                id: movieId,
                title: document.getElementById("title").value,
                year: document.getElementById("year").value,
                director: document.getElementById("director").value,
                image: document.getElementById("image").value,
                synopsis: document.getElementById("synopsis").value,
                rating: parseInt(document.getElementById("rating").value),
                genres: selectedGenres,
                actors: selectedActors
            };

            resetForm();
            revertButton.style.display = "inline-block"
        } else {
            alert("Error deleting movie.")
        }
    } catch (error) {
        console.error("Error deleting movie:", error)
        alert("Failed to delete movie.")
    }
}

async function revertMovie() {
    if (!deletedMovieData) return;

    const movieData = {
        title: deletedMovieData.title,
        year: parseInt(deletedMovieData.year),
        director: deletedMovieData.director,
        image: deletedMovieData.image,
        synopsis: deletedMovieData.synopsis,
        rating: parseInt(deletedMovieData.rating),
        genres: deletedMovieData.genres.map(genre => genre.id),
        actors: deletedMovieData.actors.map(actor => actor.id)
    };

    try {
        const response = await fetch("/movie-database/api/movies", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(movieData)
        });

        const result = await response.json();
        if (result.success) {
            alert("Movie reverted successfully!");
            window.location.reload();
        } else {
            alert("Error reverting movie!");
        }
    } catch (error) {
        console.error("Error reverting movie:", error);
        alert("Failed to revert movie.");
    }
}

document.getElementById("imageUpload").addEventListener("change", async function () {
    const file = this.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append("imageUpload", file)

    try {
        const response = await fetch("/movie-database/api/movies/upload.php", {
            method: "POST",
            body: formData
        })
        const result = await response.json()

        if (result.success) {
            uploadedFileName = result.filename

            document.getElementById("image").value = result.filename
            updateImagePreview()
        } else {
            alert(result.message || "Image upload failed!")
        }
    } catch (error) {
        console.error("Image upload error:", error)
        alert("Failed to upload image.")
    }
})

async function deleteTempImage() {
    if (!uploadedFileName) return

    try {
        const response = await fetch(`/movie-database/api/movies/delete_image.php?filename=${encodeURIComponent(uploadedFileName)}`, {
            method: "DELETE"
        })

        const result = await response.json()

        if (result.success) {
            console.log("Temporary image deleted.")
        } else {
            console.warn("Temporary image deletion failed:", result.message)
        }
    } catch (error) {
        console.error("Error deleting temporary image:", error)
    }
}

window.addEventListener("beforeunload", deleteTempImage);

document.getElementById("editForm").addEventListener("submit", (e) => {
    e.preventDefault()
    saveMovie()
})

saveButton.addEventListener("click", saveMovie);
deleteButton.addEventListener("click", deleteMovie);
revertButton.addEventListener("click", revertMovie);