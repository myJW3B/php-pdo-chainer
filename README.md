## Not Tested
- I have not tested this yet, but wanted to get the branch created. After bein tested I'll add them to Composer.
I'll provide the README content in a more straightforward format so you can easily copy and paste it without formatting issues.
- Looking, and reading how it is set up, I do not see why this will not work.

---

# PDOChainer & DBAL

This repository contains two essential PHP classes designed to simplify and enhance database operations using PDO:
- `PDOChainer`: A lightweight, chainable PDO wrapper that provides a more fluent API for interacting with databases.
- `DBAL`: A simple Database Abstraction Layer built on top of `PDOChainer` to handle common database operations such as `SELECT`, `INSERT`, `UPDATE`, and `DELETE` in a more structured manner.

## Requirements

- **PHP Version**: The classes are compatible with PHP 8.0 and later.

## Installation

1. **Download the Classes**: Clone or download the repository to your project directory.

2. **Autoloading**: I have not tested this version yet, after testing and confirming everything works I will add to Composer.
   - Ensure the classes are properly autoloaded. If you're using Composer, include them in your `composer.json`. Otherwise, use a custom autoloader or include them manually.

```php
require_once 'path/to/PDOChainer.php';
require_once 'path/to/DBAL.php';
```

3. **Dependencies**: No external dependencies are required beyond PHP and PDO.

## Usage

### 1. `PDOChainer` Class

`PDOChainer` is a PDO wrapper that allows chaining of database operations for a cleaner and more readable codebase.

#### Basic Usage

```php
use PDOChainer\PDOChainer;

// Initialize PDOChainer with database connection details
$pdo = new PDOChainer([
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'my_database',
    'user' => 'username',
    'pass' => 'password',
    'charset' => 'utf8',
    'errorMode' => \PDO::ERRMODE_EXCEPTION,
]);

// Example of a SELECT query
$result = $pdo->prepare('SELECT * FROM users WHERE id = :id')
              ->bindValue(':id', 1, \PDO::PARAM_INT)
              ->execute()
              ->fetch();

print_r($result);

// Example of an INSERT query
$pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)')
    ->bindValue(':name', 'John Doe')
    ->bindValue(':email', 'john.doe@example.com')
    ->execute();

echo 'Last Inserted ID: ' . $pdo->lastInsertId();
```

### 2. `DBAL` Class

`DBAL` extends `PDOChainer` to provide a higher-level abstraction for common database operations.

#### Basic Usage

```php
use PDOChainer\DBAL;

// Initialize DBAL with an instance of PDOChainer
$dbal = new DBAL($pdo);

// Example of an INSERT operation
$insertId = $dbal->insert('users', [
    ['name', 'John Doe', \PDO::PARAM_STR],
    ['email', 'john.doe@example.com', \PDO::PARAM_STR],
]);

echo 'Last Inserted ID: ' . $insertId;

// Example of an UPDATE operation
$affectedRows = $dbal->update('users', [
    ['name', 'Jane Doe', \PDO::PARAM_STR]
], [
    ['id', $insertId, \PDO::PARAM_INT]
]);

echo 'Number of rows updated: ' . $affectedRows;

// Example of a DELETE operation
$deletedRows = $dbal->delete('users', [
    ['id', $insertId, \PDO::PARAM_INT]
]);

echo 'Number of rows deleted: ' . $deletedRows;

// Example of a SELECT operation
$user = $dbal->select('SELECT * FROM users WHERE id = :id', 1, [
    [':id', 1, \PDO::PARAM_INT]
]);

print_r($user);
```

## Methods Overview

### PDOChainer

- **`__construct(array $options = [])`**: Initializes the PDO connection with the provided options.
- **`prepare(string $query): self`**: Prepares an SQL query for execution.
- **`bindValue(string $name, mixed $value, int $type = \PDO::PARAM_STR): self`**: Binds a value to a parameter.
- **`bindValues(array $binds): self`**: Binds multiple values to parameters.
- **`execute(): self`**: Executes the prepared statement.
- **`fetch(int $type = \PDO::FETCH_ASSOC): array|false`**: Fetches a single row from the result set.
- **`fetchAll(int $type = \PDO::FETCH_ASSOC): array|false`**: Fetches all rows from the result set.
- **`query(string $query): self`**: Executes an SQL query directly.
- **`lastInsertId(): int|false`**: Returns the ID of the last inserted row.
- **`rowCount(): int|false`**: Returns the number of affected rows by the last operation.

### DBAL

- **`insert(string $table, array $dataArr): int|false`**: Inserts a record into the specified table.
- **`update(string $table, array $dataArr, array $whereArr = [], int $limit = 1): int`**: Updates records in the specified table.
- **`delete(string $table, array $dataArr, int $limit = 1): int`**: Deletes records from the specified table.
- **`insertMulti(string $table, array $dataArr): int|false`**: Inserts multiple records into the specified table.
- **`select(string $sql, int $limit = 1, array $binds = []): array`**: Executes a SELECT query and returns the result.

## Error Handling

Both classes use PDO’s exception-based error handling. Ensure you have proper exception handling in place, particularly around `execute()` and `query()` operations to handle any database errors gracefully.

```php
try {
    // Database operations here
} catch (\PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
```

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

## Contributions

Contributions are welcome! Please feel free to submit a pull request or open an issue for discussion.

---

Thank you for using PDOChainer and DBAL! If you have any questions or need further assistance, feel free to reach out.

---
