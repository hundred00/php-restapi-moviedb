<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/movie-database/style.css">
    <title>Movie Database | Sign Up</title>
</head>

<body>
    <?php include "../includes/header.php"; ?>

    <main>
        <h1>Sign Up</h1>

        <article class="login-page">
            <form action="signup_action.php" method="post">
                <div class="login-container">
                    <label for="username">Username</label>
                    <input type="text" placeholder="Enter Username" name="username" required>

                    <label for="email">Email</label>
                    <input type="email" placeholder="Enter Email" name="email" required>

                    <label for="password">Password</label>
                    <input type="password" placeholder="Enter Password" name="password" required>

                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" placeholder="Confirm Password" name="confirm_password" required>

                    <a href="/movie-database/login">Already have an account? Log in here</a>

                    <button type="submit">Sign Up</button>
                </div>
            </form>
        </article>
    </main>
</body>

</html>