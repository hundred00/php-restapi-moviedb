const movies = Array.from(document.querySelectorAll(".movie"))

function filterMovies() {
    const searchInput = document.getElementById("searchInput").value.toLowerCase()
    const sortOption = document.getElementById("sortSelect").value

    let filteredMovies = movies.filter(movie => {
        const title = movie.dataset.title.toLowerCase()
        return title.includes(searchInput)
    })

    if (sortOption === "alphabetical") {
        filteredMovies.sort((a, b) => {
            const titleA = a.dataset.title.toLowerCase()
            const titleB = b.dataset.title.toLowerCase()
            return titleA.localeCompare(titleB)
        })
    } else if (sortOption === "release_date") {
        filteredMovies.sort((a, b) => {
            const yearA = parseInt(a.dataset.year)
            const yearB = parseInt(b.dataset.year)
            return yearB - yearA
        })
    } else if (sortOption === "id") { //default
        filteredMovies.sort((a, b) => {
            const idA = parseInt(a.dataset.id)
            const idB = parseInt(b.dataset.id)
            return idA - idB
        })
    }

    const container = document.getElementById("movieContainer")
    container.innerHTML = ""
    filteredMovies.forEach(movie => container.appendChild(movie))
}
