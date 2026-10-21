<div class="create-task-container">
    <h2>Create New Task</h2>
    <form action="save_task.php" method="POST" enctype="multipart/form-data" class="task-form">
        <label for="title">
            Task Title:
        </label>        
        <input type="text" id="title" name="title" required>

        <label for="description">
            Description:</label>
        <textarea id="description" name="description" class="input-bar" required></textarea>

        <label for="due_date">
            Due Date:</label>
        <input type="date" id="due_date" name="due_date" class="select-input" required>

        <label for="priority">
            Priority:
        </label>
        <select id="priority" name="priority" class="select-input" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>

        <label for="attachment">
            Attachment (optional):
        </label>
        <input type="file" id="attachment" name="attachment" class="input-bar">

        <button type="submit" class="submit-button">
            Create Task
        </button>
        <button type="button" class="cancel-button" onclick="window.location.href='index.php'">
            Cancel
        </button>
    </form>
</div>
