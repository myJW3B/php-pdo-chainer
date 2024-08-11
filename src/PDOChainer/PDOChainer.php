<?php

/**
 * PDO chain wrapper.
 *
 * See usage examples in the README file.
 * See license text in the LICENSE file.
 *
 * (c) Evgeniy Udodov <flr.null@gmail.com>
 * (c) John Brittain <jb@jw3b.com>
 */

namespace PDOChainer;

/**
 * Main PDO wrapper class.
 */
class PDOChainer
{
	/**
	 * @var string $host The database host.
	 */
	private string $host = '127.0.0.1';

	/**
	 * @var int $port The database port.
	 */
	private int $port = 3306;

	/**
	 * @var string|null $dbname The database name.
	 */
	private ?string $dbname = null;

	/**
	 * @var string $user The database username.
	 */
	private string $user = 'root';

	/**
	 * @var string $pass The database password.
	 */
	private string $pass = '';

	/**
	 * @var int $errorMode The PDO error mode.
	 */
	private int $errorMode = \PDO::ERRMODE_WARNING;

	/**
	 * @var string $charset The character set.
	 */
	private string $charset = 'utf8';

	/**
	 * @var \PDO|null $pdo The PDO instance.
	 */
	private ?\PDO $pdo = null;

	/**
	 * @var \PDOStatement|null $pdoStatement The PDOStatement instance.
	 */
	private ?\PDOStatement $pdoStatement = null;

	/**
	 * Main constructor.
	 *
	 * @param array $options Optional database connection options.
	 */
	public function __construct(array $options = [])
	{
		$this->host = $options['host'] ?? $this->host;
		$this->port = $options['port'] ?? $this->port;
		$this->dbname = $options['dbname'] ?? $this->dbname;
		$this->user = $options['user'] ?? $this->user;
		$this->pass = $options['pass'] ?? $this->pass;
		$this->errorMode = $options['errorMode'] ?? $this->errorMode;
		$this->charset = $options['charset'] ?? $this->charset;

		$connectionOptions = [];
		if (isset($options['persistent'])) {
			$connectionOptions[\PDO::ATTR_PERSISTENT] = $options['persistent'];
		}

		$dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname}";
		try {
			$this->pdo = new \PDO($dsn, $this->user, $this->pass, $connectionOptions);
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, $this->errorMode);
			$this->pdo->exec("SET NAMES {$this->charset}");
		} catch (\PDOException $e) {
			trigger_error('Database error: ' . $e->getMessage(), E_USER_ERROR);
		}
	}

	/**
	 * Prepares an SQL query.
	 *
	 * @param string $query The SQL query.
	 *
	 * @return \PDOChainer\PDOChainer
	 */
	public function prepare(string $query): self
	{
		$this->pdoStatement = $this->pdo?->prepare($query);
		return $this;
	}

	/**
	 * Binds a value to a parameter in the prepared statement.
	 *
	 * @param string $name The parameter name.
	 * @param mixed $value The value to bind.
	 * @param int $type The PDO parameter type.
	 *
	 * @return \PDOChainer\PDOChainer
	 */
	public function bindValue(string $name, mixed $value, int $type = \PDO::PARAM_STR): self
	{
		$this->pdoStatement?->bindValue($name, $value, $type);
		return $this;
	}

	/**
	 * Binds multiple values to parameters in the prepared statement.
	 *
	 * @param array $binds Array of values to bind, in the format:
	 * [
	 *   [':id', 2, \PDO::PARAM_INT],
	 *   [':name', 'James', \PDO::PARAM_STR],
	 * ]
	 *
	 * @return \PDOChainer\PDOChainer
	 */
	public function bindValues(array $binds): self
	{
		foreach ($binds as $valuesArray) {
			$this->bindValue($valuesArray[0], $valuesArray[1], $valuesArray[2] ?? \PDO::PARAM_STR);
		}
		return $this;
	}

	/**
	 * Executes the prepared statement.
	 *
	 * @return \PDOChainer\PDOChainer
	 */
	public function execute(): self
	{
		try {
			$this->pdoStatement?->execute();
		} catch (\PDOException $e) {
			trigger_error('Database error: ' . $e->getMessage(), E_USER_ERROR);
		}
		return $this;
	}

	/**
	 * Fetches a single row from the result set.
	 *
	 * @param int $type The fetch mode.
	 *
	 * @return array|false The fetched row or false on failure.
	 */
	public function fetch(int $type = \PDO::FETCH_ASSOC): array|false
	{
		return $this->pdoStatement?->fetch($type) ?: false;
	}

	/**
	 * Fetches all rows from the result set.
	 *
	 * @param int $type The fetch mode.
	 *
	 * @return array|false The fetched rows or false on failure.
	 */
	public function fetchAll(int $type = \PDO::FETCH_ASSOC): array|false
	{
		return $this->pdoStatement?->fetchAll($type) ?: false;
	}

	/**
	 * Executes a SQL query.
	 *
	 * @param string $query The SQL query to execute.
	 *
	 * @return \PDOChainer\PDOChainer
	 */
	public function query(string $query): self
	{
		try {
			$this->pdoStatement = $this->pdo?->query($query);
		} catch (\PDOException $e) {
			trigger_error('Database error: ' . $e->getMessage(), E_USER_ERROR);
		}
		return $this;
	}

	/**
	 * Returns the ID of the last inserted row.
	 *
	 * @return int|false The last inserted ID or false on failure.
	 */
	public function lastInsertId(): int|false
	{
		return $this->pdo?->lastInsertId() ?: false;
	}

	/**
	 * Returns the number of rows affected by the last SQL statement.
	 *
	 * @return int|false The number of affected rows or false on failure.
	 */
	public function rowCount(): int|false
	{
		return $this->pdoStatement?->rowCount() ?: false;
	}
}
