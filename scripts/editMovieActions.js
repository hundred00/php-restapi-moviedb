let deletedMovieData = null
let uploadedImages = []

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

function attachClickEvent(movieElement, movieData) {
    movieElement.addEventListener("click", async () => {
        toggleCreateMode(false)
        enableSaveButton()

        const movieId = movieElement.dataset.id
        document.getElementById("movieId").value = movieId
        document.getElementById("title").value = movieData.title
        document.getElementById("year").value = movieData.year
        document.getElementById("director").value = movieData.director
        document.getElementById("image").value = movieData.image
        document.getElementById("synopsis").value = movieData.synopsis
        document.getElementById("rating").value = movieData.rating

        updateImagePreview()
        await fetchMovieDetails(movieId)
    })
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

    selectedGenres = []
    if (movie.genres) {
        movie.genres.forEach(genre => {
            const genreData = { id: genre.id, title: genre.title }
            if (!selectedGenres.find(g => g.id === genre.id)) {
                selectedGenres.push(genreData)
            }
        })
    }

    selectedActors = []
    if (movie.actors) {
        movie.actors.forEach(actor => {
            const actorData = { id: actor.id, name: actor.name }
            if (!selectedActors.find(a => a.id === actor.id)) {
                selectedActors.push(actorData)
            }
        })
    }

    updateSelectedLists()
    updateImagePreview()
}

function addMovieToList(movieId, movieData) {
    const movieList = document.querySelector(".movie-list")

    const newMovie = document.createElement("div")
    newMovie.classList.add("movie-item")
    newMovie.style.backgroundImage = `url('/movie-database/images/posters/${movieData.image}')`

    newMovie.dataset.id = movieId
    newMovie.dataset.title = movieData.title
    newMovie.dataset.year = movieData.year
    newMovie.dataset.director = movieData.director
    newMovie.dataset.image = movieData.image
    newMovie.dataset.rating = movieData.rating
    newMovie.dataset.synopsis = movieData.synopsis

    newMovie.innerHTML = `<div class="overlay">${movieData.title}</div>`

    newMovie.addEventListener("click", async () => {
        toggleCreateMode(false)
        enableSaveButton()
        document.getElementById("movieId").value = movieId
        document.getElementById("title").value = movieData.title
        document.getElementById("year").value = movieData.year
        document.getElementById("director").value = movieData.director
        document.getElementById("image").value = movieData.image
        document.getElementById("synopsis").value = movieData.synopsis
        document.getElementById("rating").value = movieData.rating

        updateImagePreview()
        await fetchMovieDetails(movieId)
    })

    movieList.appendChild(newMovie)
}

function updateMovieInList(movieId, movieData) {
    const movieElement = document.querySelector(`.movie-item[data-id="${movieId}"]`)
    if (movieElement) {
        movieElement.style.backgroundImage = `url('/movie-database/images/posters/${movieData.image}')`
        movieElement.querySelector(".overlay").innerText = movieData.title

        movieElement.dataset.title = movieData.title
        movieElement.dataset.year = movieData.year
        movieElement.dataset.director = movieData.director
        movieElement.dataset.image = movieData.image
        movieElement.dataset.rating = movieData.rating
        movieElement.dataset.synopsis = movieData.synopsis
    }
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

        genres: selectedGenres.map(genre => genre.id),
        actors: selectedActors.map(actor => actor.id)
    }

    try {
        const response = await fetch(`/movie-database/api/movies/${movieId ? "?id=" + movieId : ""}`, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(movieData)
        })

        const result = await response.json()
        if (result.success) {
            alert("Movie saved successfully!")

            if (!movieId) {
                addMovieToList(result.id, movieData)
            } else {
                updateMovieInList(movieId, movieData)
            }

            const imageInput = document.getElementById("image").value
            uploadedImages = uploadedImages.filter(img => img !== imageInput)

            resetForm()
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
        })

        if (response.ok) {
            alert("Movie deleted successfully!")

            const deletedImage = document.getElementById("image").value

            if (deletedImage && !uploadedImages.includes(deletedImage)) {
                uploadedImages.push(deletedImage)
            }

            const movieElement = document.querySelector(`.movie-item[data-id="${movieId}"]`)
            if (movieElement) {
                movieElement.remove()
            }

            deletedMovieData = {
                id: movieId,
                title: document.getElementById("title").value,
                year: document.getElementById("year").value,
                director: document.getElementById("director").value,
                image: deletedImage,
                synopsis: document.getElementById("synopsis").value,
                rating: parseInt(document.getElementById("rating").value),
                genres: selectedGenres,
                actors: selectedActors
            }

            resetForm()
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
    if (!deletedMovieData) return

    const movieData = {
        title: deletedMovieData.title,
        year: parseInt(deletedMovieData.year),
        director: deletedMovieData.director,
        image: deletedMovieData.image,
        synopsis: deletedMovieData.synopsis,
        rating: parseInt(deletedMovieData.rating),
        genres: deletedMovieData.genres.map(genre => genre.id),
        actors: deletedMovieData.actors.map(actor => actor.id)
    }

    try {
        const response = await fetch("/movie-database/api/movies/", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(movieData)
        })

        const contentType = response.headers.get("content-type")
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Invalid JSON response.")
        }

        const result = await response.json()

        if (result.success && result.id) {
            alert("Movie reverted successfully!")

            const movieList = document.querySelector(".movie-list")
            const newMovie = document.createElement("div")
            newMovie.classList.add("movie-item")
            newMovie.style.backgroundImage = `url('/movie-database/images/posters/${deletedMovieData.image}')`

            newMovie.dataset.id = result.id || Math.random().toString(36).substr(2, 9)
            newMovie.dataset.title = deletedMovieData.title
            newMovie.dataset.year = deletedMovieData.year
            newMovie.dataset.director = deletedMovieData.director
            newMovie.dataset.image = deletedMovieData.image
            newMovie.dataset.rating = deletedMovieData.rating
            newMovie.dataset.synopsis = deletedMovieData.synopsis

            newMovie.innerHTML = `<div class="overlay">${deletedMovieData.title}</div>`
            movieList.appendChild(newMovie)
            attachClickEvent(newMovie, deletedMovieData)
            uploadedImages = uploadedImages.filter(img => img !== deletedMovieData.image)

            revertButton.style.display = "none"
            deletedMovieData = null
        } else {
            alert("Error reverting movie! " + (result.message || ""))
        }
    } catch (error) {
        console.error("Error reverting movie:", error)
        alert("Failed to revert movie.")
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
            uploadedImages.push(result.filename)

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

window.addEventListener("beforeunload", function (event) {
    uploadedImages.forEach(image => {
        const xhr = new XMLHttpRequest()
        xhr.open("DELETE", `/movie-database/api/movies/delete_image.php?filename=${image}`, false)
        xhr.send()
    })

    uploadedImages = []
})

document.getElementById("editForm").addEventListener("submit", (e) => {
    e.preventDefault()
})

saveButton.addEventListener("click", saveMovie)
deleteButton.addEventListener("click", deleteMovie)
revertButton.addEventListener("click", revertMovie)