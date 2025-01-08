document.addEventListener("DOMContentLoaded", () => {
    const favoriteButtons = document.querySelectorAll(".favorite-btn")

    favoriteButtons.forEach(button => {
        button.addEventListener("click", async () => {
            const movieId = button.dataset.id

            try {
                const response = await fetch("/movie-database/api/users/favorites.php", {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ movieId })
                })

                const result = await response.json()
                if (result.success) {
                    button.classList.toggle("favorite")
                } else {
                    alert("Error toggling favorite: " + (result.error || ""))
                }
            } catch (error) {
                console.error("Error toggling favorite:", error)
                alert("Failed to toggle favorite.")
            }
        })
    })
})