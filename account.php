<?php
declare(strict_types=1);

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$user_id = (int) $_SESSION['user_id'];
$message = null; // ['type' => 'success'|'error', 'text' => string]

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password']     ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    if ($current_password === '' || $new_password === '') {
        $message = ['type' => 'error', 'text' => 'All password fields are required.'];
    } elseif (!password_verify($current_password, (string) $password_hash)) {
        $message = ['type' => 'error', 'text' => 'Current password is incorrect.'];
    } elseif ($new_password !== $confirm_password) {
        $message = ['type' => 'error', 'text' => 'New passwords do not match.'];
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param('si', $new_hash, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = ['type' => 'success', 'text' => 'Password updated.'];
    }
}

// Load profile details
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Account no longer exists — end the session
if ($user === null) {
    $conn->close();
    header('Location: logout.php');
    exit;
}

// Task stats
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(is_done = 1), 0) AS completed,
            COALESCE(SUM(is_done = 0 AND due_date < CURDATE()), 0) AS overdue
     FROM tasks WHERE user_id = ?"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - Task Flow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Task Flow</div>
    <div class="navbar_links">
        <a href="index.php">Home</a>
        <a href="tasks.php">Tasks</a>
        <a href="account.php">Account</a>
    </div>
</nav>

<div class="content">
    <h1>Account</h1>
    <p>Your profile and settings.</p>
</div>

<div class="todo-section">
    <h2>Profile</h2>
    <dl class="account-details">
        <dt>Name</dt>
        <dd><?php echo htmlspecialchars($user['name']); ?></dd>
        <dt>Email</dt>
        <dd><?php echo htmlspecialchars($user['email']); ?></dd>
    </dl>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <h2><?php echo (int) $stats['total']; ?></h2>
        <p>Total tasks</p>
    </div>
    <div class="dashboard-card">
        <h2><?php echo (int) $stats['completed']; ?></h2>
        <p>Completed</p>
    </div>
    <div class="dashboard-card">
        <h2><?php echo (int) $stats['overdue']; ?></h2>
        <p>Overdue</p>
    </div>
</div>

<div class="task-form-section">
    <h2>Change Password</h2>

    <?php if ($message !== null): ?>
        <p class="alert-<?php echo $message['type']; ?>"><?php echo htmlspecialchars($message['text']); ?></p>
    <?php endif; ?>

    <form method="POST" class="app-form">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="btn-submit">Update Password</button>
    </form>
</div>

<div class="todo-section">
    <a href="logout.php" class="btn-delete">Log Out</a>
</div>

</body>
</html>
