<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_GET['id'] ?? 0);

if ($task_id === 0) {
    header('Location: index.php');
    exit;
}

// Load existing task to pre-fill the form
$task = getTask($task_id, $user_id);

// Task not found or doesn't belong to this user
if ($task === null) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = $_POST['due_date']          ?? '';

    $allowed_priorities = ['low', 'medium', 'high'];
    $priority = in_array($_POST['priority'] ?? '', $allowed_priorities, true)
        ? $_POST['priority'] : 'low';

    $allowed_statuses = ['not_started', 'in_progress', 'completed'];
    $status = in_array($_POST['status'] ?? '', $allowed_statuses, true)
        ? $_POST['status'] : 'not_started';

    if ($title !== '' && $due_date !== '') {
        editTask($task_id, $user_id, $title, $description, $due_date, $priority, $status);
        $conn->close();
        header('Location: index.php');
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Task Flow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="task-form-section">
        <h2>Edit Task</h2>
        <form method="POST" class="app-form">
            <label for="title">Task Title:</label>
            <input type="text" id="title" name="title" required
                   value="<?php echo htmlspecialchars($task['title']); ?>">

            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form_textarea"><?php echo htmlspecialchars($task['description']); ?></textarea>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" class="form_select" required
                   value="<?php echo htmlspecialchars($task['due_date']); ?>">

            <label for="priority">Priority:</label>
            <select id="priority" name="priority" class="form_select">
                <option value="low"    <?php echo $task['priority'] === 'low'    ? 'selected' : ''; ?>>Low</option>
                <option value="medium" <?php echo $task['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="high"   <?php echo $task['priority'] === 'high'   ? 'selected' : ''; ?>>High</option>
            </select>

            <label for="status">Status:</label>
            <select id="status" name="status" class="form_select">
                <option value="not_started" <?php echo $task['status'] === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed"   <?php echo $task['status'] === 'completed'   ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit" class="btn-submit">Save Changes</button>
            <a href="index.php" class="btn-submit" style="text-align:center; text-decoration:none;">Cancel</a>
        </form>
    </div>
</body>
</html>

