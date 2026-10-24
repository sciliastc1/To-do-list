<div class="task-form-section">
    <h2>Create New Task</h2>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'missing'): ?>
        <p class="alert-error">Title and due date are required.</p>
    <?php endif; ?>

    <form action="save_task.php" method="POST" class="app-form">
        <label for="title">Task Title:</label>
        <input type="text" id="title" name="title" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" class="form_textarea"></textarea>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" class="form_select" required>

        <label for="priority">Priority:</label>
        <select id="priority" name="priority" class="form_select" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>

        <label for="status">Status:</label>
        <select id="status" name="status" class="form_select" required>
            <option value="not_started">Not Started</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>

        <button type="submit" class="btn-submit">Create Task</button>
    </form>
</div>
