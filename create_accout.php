<link rel="stylesheet" href="style.css">
<body>
    <div class="login-container">
        <h2>Create Account</h2>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'taken'): ?>
                <p class="form-error">That username is already taken.</p>
            <?php elseif ($_GET['error'] === 'mismatch'): ?>
                <p class="form-error">Passwords do not match.</p>
            <?php elseif ($_GET['error'] === 'missing'): ?>
                <p class="form-error">All fields are required.</p>
            <?php endif; ?>
        <?php endif; ?>

        <form action="register_process.php" method="POST" class="task-form">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required
                   value="<?php echo htmlspecialchars($_GET['username'] ?? ''); ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" class="submit-button">Create Account</button>
        </form>

        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>


