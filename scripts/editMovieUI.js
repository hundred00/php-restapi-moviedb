let selectedGenres = []
let selectedActors = []
let isCreatingNew = false
let uploadedFileName = ""
let deletedMovieData = null

const saveButton = document.getElementById("saveButton")
const deleteButton = document.getElementById("deleteButton")
const revertButton = document.getElementById("revertButton")
const imagePreview = document.getElementById("imagePreview")

disableSaveButton()

function disableSaveButton() {
    saveButton.disabled = true
    saveButton.style.opacity = 0.5
    saveButton.style.cursor = "not-allowed"
}

function enableSaveButton() {
    saveButton.disabled = false
    saveButton.style.opacity = 1
    saveButton.style.cursor = "pointer"
}

function resetForm() {
    document.getElementById("editForm").reset()
    document.getElementById("editingMovieName").innerText = "Editing: None"
    selectedGenres = []
    selectedActors = []
    updateSelectedLists()
    disableSaveButton()
    toggleCreateMode(false)
}

function toggleCreateMode(isNew) {
    isCreatingNew = isNew
    saveButton.textContent = isNew ? "Create New" : "Save Changes"
    saveButton.style.backgroundColor = isNew ? "#41cc5f" : "#358bdc"

    deleteButton.style.display = isNew ? "none" : "inline-block"
    revertButton.style.display = "none"
    imagePreview.src = "/movie-database/images/posters/poster-missing.jpg"

    enableSaveButton()
}

function updateSelectedLists() {
    updateList("selectedGenres", selectedGenres, removeGenre)
    updateList("selectedActors", selectedActors, removeActor)
}

function updateList(containerId, items, removeCallback) {
    const container = document.getElementById(containerId)
    container.innerHTML = ""
    items.forEach(item => {
        const span = document.createElement("span")
        span.className = "list-item"
        span.textContent = item.title || item.name
        span.addEventListener("click", () => removeCallback(item.id))
        container.appendChild(span)
    })
}

function addGenre() {
    addDropdownItem("genreDropdown", selectedGenres)
}

function addActor() {
    addDropdownItem("actorDropdown", selectedActors)
}

function addDropdownItem(dropdownId, collection) {
    const dropdown = document.getElementById(dropdownId)
    const id = parseInt(dropdown.value)
    const name = dropdown.options[dropdown.selectedIndex].text

    if (!collection.find(item => item.id === id)) {
        collection.push({ id, title: name })
        updateSelectedLists()
    }
}

function removeGenre(id) {
    selectedGenres = selectedGenres.filter(genre => genre.id !== id)
    updateSelectedLists()
}

function removeActor(id) {
    selectedActors = selectedActors.filter(actor => actor.id !== id)
    updateSelectedLists()
}

document.getElementById("rating").addEventListener("input", function () {
    document.getElementById("ratingValue").innerText = this.value
})

function updateImagePreview() {
    const imageInput = document.getElementById("image").value
    const error = document.getElementById("imageError")

    imagePreview.src = `/movie-database/images/posters/${imageInput}`

    imagePreview.onerror = () => {
        imagePreview.src = "/movie-database/images/posters/poster-missing.jpg"
        error.style.display = "block"
    }

    imagePreview.onload = () => {
        error.style.display = "none"
    }
}

document.getElementById("image").addEventListener("input", updateImagePreview)

document.querySelectorAll(".movie-item").forEach(movie => {
    movie.addEventListener("click", async () => {
        if (movie.dataset.id === "new") {
            resetForm()
            toggleCreateMode(true)
        } else {
            toggleCreateMode(false)
            enableSaveButton()

            const movieId = movie.dataset.id
            document.getElementById("movieId").value = movieId
            document.getElementById("title").value = movie.dataset.title
            document.getElementById("year").value = movie.dataset.year
            document.getElementById("director").value = movie.dataset.director
            document.getElementById("image").value = movie.dataset.image
            document.getElementById("synopsis").value = movie.dataset.synopsis
            document.getElementById("rating").value = movie.dataset.rating

            updateImagePreview()
            await fetchMovieDetails(movieId)
        }
    })
})