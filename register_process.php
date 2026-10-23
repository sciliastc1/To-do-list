<?php
declare(strict_types=1);

// TEMPORARY: show errors in browser to debug 500 — remove before going live
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/db.php';

// Reject anything that isn't a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_accout.php');
    exit;
}

$name             = trim($_POST['name']             ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';

// Presence check
if ($name === '' || $email === '' || $password === '') {
    header('Location: create_accout.php?error=missing');
    exit;
}

// Password match check
if ($password !== $confirm_password) {
    header('Location: create_accout.php?error=mismatch&name=' . urlencode($name) . '&email=' . urlencode($email));
    exit;
}

// Check if name is already taken
$stmt = $conn->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: create_accout.php?error=taken&email=' . urlencode($email));
    exit;
}
$stmt->close();

// Hash the password — never store plain text
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)"
);
$stmt->bind_param('sss', $name, $email, $password_hash);
$stmt->execute();
$new_user_id = $conn->insert_id;
$stmt->close();
$conn->close();

// Log the new user in immediately
session_regenerate_id(true);
$_SESSION['user_id'] = $new_user_id;
$_SESSION['name']    = $name;

header('Location: index.php');
exit;

