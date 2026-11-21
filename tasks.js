// Handles the task list in the browser so the page does not reload.
// The server (PHP) is only called to save a change in the database.

// Status value => text shown in the badge (same list as in tasks.php)
var statusLabels = {
    not_started: 'Not Started',
    in_progress: 'In Progress',
    completed: 'Completed'
};

// Show or hide the "Add New Task" form
document.getElementById('add-task-btn').addEventListener('click', function () {
    var form = document.getElementById('task-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
});

// Tick or untick a task
// item is the <li> of the task, its id is in data-id
function toggleTask(item) {
    var checkbox = item.querySelector('.todo-item_checkbox');

    // Same data a normal form would send
    var data = new FormData();
    data.append('task_id', item.dataset.id);
    if (checkbox.checked) {
        data.append('is_done', '1');
    }

    fetch('toggle_task.php', { method: 'POST', body: data })
        .then(function (response) { return response.text(); })
        .then(function (text) {
            if (text !== 'ok') {
                checkbox.checked = !checkbox.checked; // save failed, undo
                return;
            }
            item.classList.toggle('todo-item--done', checkbox.checked);

            // Only the Tasks page has statuses and filter counts
            if (item.dataset.status) {
                item.dataset.status = checkbox.checked ? 'completed' : 'not_started';
                item.querySelector('.badge--status').textContent = statusLabels[item.dataset.status];
                updateCounts();
                applyFilter();
            }
        });
}

// Delete a task (asks first, because it can't be undone)
function deleteTask(item) {
    if (!confirm('Delete this task?')) {
        return;
    }

    var data = new FormData();
    data.append('id', item.dataset.id);

    fetch('delete_task.php', { method: 'POST', body: data })
        .then(function (response) { return response.text(); })
        .then(function (text) {
            if (text === 'ok') {
                // Deleted in the database, now take it off the page too
                item.remove();
                updateCounts();
                applyFilter();
            }
        });
}

// One listener on the list handles every row, even rows added later
document.addEventListener('change', function (event) {
    if (event.target.classList.contains('todo-item_checkbox')) {
        toggleTask(event.target.closest('.todo-item'));
    }
});

// Clicks on a delete button or on a filter tab
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('btn-delete')) {
        event.preventDefault();
        deleteTask(event.target.closest('.todo-item'));
    }
    // closest() so clicking the count inside a tab also works
    var tab = event.target.closest('[data-filter]');
    if (tab) {
        event.preventDefault();
        currentFilter = tab.dataset.filter;
        applyFilter();
    }
});

// Filter tabs (Tasks page only)
var currentFilter = 'all';

// Ids of the tasks that match the search, or null when not searching
var searchIds = null;

// Search box (Tasks page only)
var searchBox = document.getElementById('task-search');
var searchTimer;

if (searchBox) {
    searchBox.addEventListener('input', function () {
        // Wait until the user stops typing so not every letter sends a request
        clearTimeout(searchTimer);
        searchTimer = setTimeout(searchTasks, 300);
    });
}

// Asks the server which tasks match the text in the search box
function searchTasks() {
    var text = searchBox.value.trim();

    // Empty search: show every task again
    if (text === '') {
        searchIds = null;
        applyFilter();
        return;
    }

    fetch('search_tasks.php?q=' + encodeURIComponent(text))
        .then(function (response) { return response.json(); })
        .then(function (ids) {
            // Ignore old answers if the text changed while waiting
            if (searchBox.value.trim() !== text) {
                return;
            }
            // data-id is text, so turn the numbers into text too
            searchIds = ids.map(String);
            applyFilter();
        });
}

// Shows only the tasks with the chosen status (and search) and marks the active tab
function applyFilter() {
    var items = document.querySelectorAll('.todo-item');
    var shown = 0;

    items.forEach(function (item) {
        var matchesTab = currentFilter === 'all' || item.dataset.status === currentFilter;
        var matchesSearch = searchIds === null || searchIds.indexOf(item.dataset.id) !== -1;
        var visible = matchesTab && matchesSearch;
        item.style.display = visible ? '' : 'none';
        if (visible) {
            shown++;
        }
    });

    document.querySelectorAll('.task-filter').forEach(function (tab) {
        tab.classList.toggle('task-filter--active', tab.dataset.filter === currentFilter);
    });

    // Nothing left to show, so show the "No tasks" text
    document.getElementById('empty-message').style.display = shown === 0 ? 'block' : 'none';
}

// Counts the tasks per status and writes the numbers in the tabs
function updateCounts() {
    var counts = { all: 0, not_started: 0, in_progress: 0, completed: 0 };

    document.querySelectorAll('.todo-item').forEach(function (item) {
        counts.all++;
        counts[item.dataset.status]++;
    });

    document.querySelectorAll('[data-count]').forEach(function (span) {
        span.textContent = counts[span.dataset.count];
    });
}

// Fill in the numbers when the page loads (only if the page has tabs)
if (document.querySelector('.task-filters')) {
    updateCounts();
}
