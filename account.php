<?php
declare(strict_types=1);

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/twig.php';

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

render('account.html.twig', [
    'user'    => $user,
    'stats'   => $stats,
    'message' => $message,
]);
