<?php
declare(strict_types=1);

// Start session before any output
session_start();

require_once __DIR__ . '/db.php';

// Reject anything that isn't a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password']      ?? '';

// Basic presence check
if ($username === '' || $password === '') {
    header('Location: login.php?error=missing');
    exit;
}

// Look up the user by username
$stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // User not found — use a generic message to avoid username enumeration
    $stmt->close();
    $conn->close();
    header('Location: login.php?error=invalid');
    exit;
}

$stmt->bind_result($user_id, $password_hash);
$stmt->fetch();
$stmt->close();
$conn->close();

// Verify the submitted password against the stored hash
if (!password_verify($password, $password_hash)) {
    header('Location: login.php?error=invalid');
    exit;
}

// Password is correct — create the session
session_regenerate_id(true); // prevent session fixation
$_SESSION['user_id']  = $user_id;
$_SESSION['username'] = $username;

header('Location: index.php');
exit;

