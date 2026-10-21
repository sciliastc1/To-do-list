<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Flow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="form-error">Invalid username or password.</p>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="task-form">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="submit-button">Login</button>
        </form>

        <p>Don't have an account? <a href="create_accout.php">Create one</a></p>
    </div>
</body>
</html>

</html>