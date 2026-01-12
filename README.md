# Webshop Local Setup Guide

This guide explains how to set up and run the webshop project on your local machine.

## Requirements

1.  **PHP 8.1** or higher
2.  **MySQL** Database Server (e.g., via XAMPP, MAMP, or installed standalone)
3.  **Composer** (optional, but recommended for dependency management)

## Setup Steps

### 1. Database Setup

The project requires a MySQL database. By default, the code looks for a database named `webshop`.

1.  Open your MySQL management tool (e.g., phpMyAdmin, Tableau, or CLI).
2.  Create a new database named `webshop` (if it doesn't exist).
    ```sql
    CREATE DATABASE webshop;
    ```
3.  Import the schema file `schema.sql` into this database.
    - **CLI Command:**
      ```bash
      mysql -u root -p webshop < schema.sql
      ```
    - **phpMyAdmin:** Go to the `webshop` database, click "Import", select `schema.sql`, and click "Go".

### 2. Configuration (`includes/db.php`)

Check the `includes/db.php` file to ensure the database connection settings match your local environment.

The default fallback configuration (for XAMPP/MAMP) is:
- **Host:** `localhost`
- **User:** `root`
- **Password:** `""` (empty)
- **Database:** `webshop`

If your local MySQL setup has a password or uses a different port, modify the `else` block in `includes/db.php`:

```php
} else {
    // Local fallback (XAMPP / MAMP)
    $host = "localhost";
    $port = 3306;
    $user = "your_username"; // e.g., root
    $pass = "your_password"; // e.g., root or secret
    $dbname = "webshop";
}
```

### 3. Running the Server

You can use the built-in PHP development server to run the project.

1.  Open your terminal in the project root directory.
2.  Run the following command:
    ```bash
    php -S localhost:8000 -t public
    ```
3.  Open your browser and navigate to:
    [http://localhost:8000](http://localhost:8000)

## Project Structure

- **public/**: Contains the public-facing files (index.php, css, js). server root should be here.
- **includes/**: Helper scripts (database connection, header, footer).
- **admin/**: Admin interface files.
