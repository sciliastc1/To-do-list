<?php
declare(strict_types=1);

// Searches the logged in user's tasks on several criteria at the same time:
// text (title/description), priority, status and a due date range.
// Every criterion is optional, only the filled in ones are used.
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

// Read the criteria from the URL (empty string = not used)
$text     = trim($_GET['q']        ?? '');
$priority = $_GET['priority']      ?? '';
$status   = $_GET['status']        ?? '';
$due_from = $_GET['due_from']      ?? '';
$due_to   = $_GET['due_to']        ?? '';

// Checks that a date is a real YYYY-MM-DD date (same check as save_task.php)
function isValidDate(string $value): bool {
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

// The query is built from pieces. These three lists always grow together:
// $conditions = SQL written by us, with a ? for every value
// $types      = the bind_param letters (i = integer, s = string)
// $params     = the values that go into the ?s
// User input only ever goes into $params, never into the SQL itself.

// Always: only this user's tasks
$conditions = ['user_id = ?'];
$types      = 'i';
$params     = [$user_id];

// Text: matches the title or the description
if ($text !== '') {
    $like = '%' . $text . '%';
    $conditions[] = '(title LIKE ? OR description LIKE ?)';
    $types       .= 'ss';
    $params[]     = $like;
    $params[]     = $like;
}

// Priority: only accepted if it is in the list, otherwise ignored
if (in_array($priority, ['low', 'medium', 'high'], true)) {
    $conditions[] = 'priority = ?';
    $types       .= 's';
    $params[]     = $priority;
}

// Status: same idea as priority
if (in_array($status, ['not_started', 'in_progress', 'completed'], true)) {
    $conditions[] = 'status = ?';
    $types       .= 's';
    $params[]     = $status;
}

// Due date range: each side is optional, invalid dates are ignored
if (isValidDate($due_from)) {
    $conditions[] = 'due_date >= ?';
    $types       .= 's';
    $params[]     = $due_from;
}
if (isValidDate($due_to)) {
    $conditions[] = 'due_date <= ?';
    $types       .= 's';
    $params[]     = $due_to;
}

// Join the pieces: "user_id = ? AND priority = ? AND ..."
$sql = 'SELECT id FROM tasks WHERE ' . implode(' AND ', $conditions);

$stmt = $conn->prepare($sql);
// ... spreads the array, so every value becomes its own argument
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Only the ids are needed, the rows are already on the page
$ids = array_map(fn($row) => (int) $row['id'], $rows);

header('Content-Type: application/json');
echo json_encode($ids);
