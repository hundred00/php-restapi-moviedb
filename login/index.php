<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/movie-database/style.css">
    <title>Movie Database | Log in</title>
</head>

<body>
    <?php include "../includes/header.php"; ?>

    <main>
        <h1>Log in</h1>

        <article class="login-page">
            <form action="action_page.php" method="post">
                <div class="login-container">
                    <label for="username">Username</label>
                    <input type="text" placeholder="Enter Username" name="username" required>

                    <label for="password">Password</label>
                    <input type="password" placeholder="Enter Password" name="password" required>

                    <a href="/movie-database/signup">Don't have an account? Sign up here</a>

                    <button type="submit">Login</button>
                </div>

                <div class="login-cancel-container" style="background-color:#f1f1f1">
                    <a href="/movie-database" class="login-cancel-button">Cancel</a>
                </div>
            </form>
        </article>
    </main>
</body>

</html>