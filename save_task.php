<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// user_id always comes from the session — never from the form
$user_id = (int) $_SESSION['user_id'];

// Sanitise inputs
$title       = trim($_POST['title']       ?? '');
$description = trim($_POST['description'] ?? '');
$due_date    = $_POST['due_date']          ?? '';

// Whitelist priority — reject anything not in the allowed list
$allowed_priorities = ['low', 'medium', 'high'];
$priority = in_array($_POST['priority'] ?? '', $allowed_priorities, true)
    ? $_POST['priority']
    : 'low';

// Whitelist status
$allowed_statuses = ['not_started', 'in_progress', 'completed'];
$status = in_array($_POST['status'] ?? '', $allowed_statuses, true)
    ? $_POST['status']
    : 'not_started';

// Validate required fields
if ($title === '' || $due_date === '') {
    header('Location: index.php?action=add&error=missing');
    exit;
}

// Validate date format (YYYY-MM-DD)
$date = DateTimeImmutable::createFromFormat('Y-m-d', $due_date);
if (!$date || $date->format('Y-m-d') !== $due_date) {
    header('Location: index.php?action=add&error=missing');
    exit;
}

// is_done is derived from status — not trusted from the client
// If "mark as done" checkbox is checked, force status to completed
if (isset($_POST['is_done']) && $_POST['is_done'] === '1') {
    $status  = 'completed';
    $is_done = 1;
} else {
    $is_done = ($status === 'completed') ? 1 : 0;
}
$created_at = date('Y-m-d H:i:s');

$stmt = $conn->prepare(
    "INSERT INTO tasks (user_id, title, description, due_date, priority, status, is_done, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('isssssis', $user_id, $title, $description, $due_date, $priority, $status, $is_done, $created_at);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php');
exit;
