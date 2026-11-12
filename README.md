# Task Flow (To-do list)

A to-do list website made with PHP, MySQL and Twig. You can create an account, log in and keep track of your own tasks.

## What it can do

- Create an account, log in and log out
- Add, edit and delete tasks
- Give a task a due date, a priority (low, medium, high) and a status (not started, in progress, completed)
- Tick a task off without the page reloading
- Filter the task list by status
- See overdue tasks marked in the list
- Account page with your details, task numbers and a form to change your password

## What you need

- PHP 8.1 or newer with the mysqli extension
- MySQL or MariaDB
- Composer (to install Twig)

## Setup

1. Install Twig:

   ```
   composer install
   ```

2. Create a database with these two tables:

   **users**

   | Column        | Notes                        |
   |---------------|------------------------------|
   | id            | primary key, auto increment  |
   | name          | unique, used to log in       |
   | email         | unique                       |
   | password_hash | made with `password_hash()`  |

   **tasks**

   | Column      | Notes                                   |
   |-------------|-----------------------------------------|
   | id          | primary key, auto increment             |
   | user_id     | id of the user that owns the task       |
   | title       |                                         |
   | description | can be empty                            |
   | due_date    | date (YYYY-MM-DD)                       |
   | priority    | `low`, `medium` or `high`               |
   | status      | `not_started`, `in_progress` or `completed` |
   | is_done     | 1 when the status is completed, else 0  |
   | created_at  | date and time the task was made         |

3. Put your database login in `config.php`:

   ```php
   <?php
   return [
       'host' => 'localhost',
       'user' => 'your_user',
       'pass' => 'your_password',
       'name' => 'your_database',
   ];
   ```

4. Start the site from the project folder:

   ```
   php -S localhost:8000
   ```

   Then open http://localhost:8000 in the browser.

## How it works

Every page has a PHP file and a Twig template. The PHP file checks if you are logged in, gets the data from the database and gives it to the template with `render()`. The template only builds the HTML.

Ticking and deleting tasks is done by `tasks.js`. It sends a request to `toggle_task.php` or `delete_task.php` with `fetch()`. Those files answer `ok` when the change is saved, and then the script updates the page without reloading it.

## Files

**Pages**

| File                | What it does                                   |
|---------------------|------------------------------------------------|
| `index.php`         | Home page with the to-do list                  |
| `tasks.php`         | Full task list with filters                    |
| `edit_task.php`     | Form to edit a task                            |
| `account.php`       | Profile, task numbers and change password      |
| `login.php`         | Login form                                     |
| `create_accout.php` | Create account form                            |

**Form handlers (no page of their own)**

| File                   | What it does                              |
|------------------------|-------------------------------------------|
| `login_process.php`    | Checks the login and starts the session   |
| `register_process.php` | Makes a new account                       |
| `logout.php`           | Ends the session                          |
| `save_task.php`        | Saves a new task                          |
| `toggle_task.php`      | Marks a task done or not done (from JS)   |
| `delete_task.php`      | Deletes a task (from JS)                  |

**Helpers**

| File              | What it does                                         |
|-------------------|------------------------------------------------------|
| `db.php`          | Connects to the database (`$conn`)                   |
| `config.php`      | Database settings                                    |
| `twig.php`        | Sets up Twig and has the `render()` function         |
| `task_worker.php` | Functions to get, edit and delete a task             |
| `tasks.js`        | Checkbox, delete button, filter tabs and the add form |
| `style.css`       | All the styling                                      |

**Templates** (`templates/`)

- `base.html.twig` is the layout with the navbar. The home, tasks, account and edit pages extend it.
- `login.html.twig` and `create_accout.html.twig` are separate pages without the navbar.
- `partials/create_task.html.twig` is the form to add a task. It is included in the home and tasks pages.

## Security

- Passwords are saved with `password_hash()` and checked with `password_verify()`
- All queries use prepared statements
- Every task query also checks the user id, so you can only see and change your own tasks
- Twig escapes everything it prints
- The session id is renewed after logging in

## Not finished yet

- The Reports card on the home page links to `reports.php`, which is not made yet
- Error display is still turned on in `index.php` and `register_process.php` for testing
