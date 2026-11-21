<?php
declare(strict_types=1);

// Searches the logged in user's tasks by title and description.
// Called from tasks.js with fetch(), answers with a JSON list of task ids.

session_start();

// Not logged in: answer with an error code instead of a redirect,
// because fetch() expects JSON and not a login page
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

require_once __DIR__ . '/db.php';

$user_id = (int) $_SESSION['user_id'];
$query   = trim($_GET['q'] ?? '');

// The % signs go around the text here, never inside the SQL itself
$like = '%' . $query . '%';

$stmt = $conn->prepare(
    "SELECT id FROM tasks
     WHERE user_id = ? AND (title LIKE ? OR description LIKE ?)"
);
$stmt->bind_param('iss', $user_id, $like, $like);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Only the ids are needed, the rows are already on the page
$ids = array_map(fn($row) => (int) $row['id'], $rows);

header('Content-Type: application/json');
echo json_encode($ids);
