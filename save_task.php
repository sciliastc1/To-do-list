<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// TEMPORARY: replace with session user once login is wired up
$user_id = 1;

$title       = trim($_POST['title']       ?? '');
$description = trim($_POST['description'] ?? '');
$due_date    = $_POST['due_date']          ?? '';
$priority    = $_POST['priority']          ?? 'low';

// Basic validation
if ($title === '' || $due_date === '') {
    http_response_code(400);
    die('Title and due date are required.');
}

// Handle optional file attachment
$attachment_path = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $safe_name       = uniqid() . '_' . basename($_FILES['attachment']['name']);
    $attachment_path = 'uploads/' . $safe_name;
    move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $safe_name);
}

$stmt = $conn->prepare(
    "INSERT INTO tasks (user_id, title, description, due_date, priority, attachment)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('isssss', $user_id, $title, $description, $due_date, $priority, $attachment_path);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php');
exit;

