<?php

/**
 * DBAL over PDOChainer.
 *
 * See usage examples in the README file.
 * See license text in the LICENSE file.
 *
 * (c) Evgeniy Udodov <flr.null@gmail.com>
 * (c) John Brittain <jb@jw3b.com>
 */

namespace PDOChainer;

/**
 * DBAL over PDOChainer realization.
 */
class DBAL
{
	/**
	 * PDOChainer link.
	 *
	 * @var \PDOChainer\PDOChainer
	 */
	private \PDOChainer\PDOChainer $pdo;

	/**
	 * Default constructor.
	 *
	 * @param \PDOChainer\PDOChainer $pdo
	 */
	public function __construct(\PDOChainer\PDOChainer $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Inserts data into the database.
	 *
	 * @param string $table The name of the table to insert data into.
	 * @param array $dataArr The data to be inserted, in the format:
	 * [
	 *   ['id', 2, \PDO::PARAM_INT],
	 *   ['name', 'James', \PDO::PARAM_STR],
	 * ]
	 *
	 * @return int|false The inserted ID or false on failure.
	 */
	public function insert(string $table, array $dataArr): int|false
	{
		$fields = $params = $values = [];
		foreach ($dataArr as $data) {
			$fields[] = "`{$data[0]}`";
			$params[] = ":{$data[0]}";
			$values[":{$data[0]}"] = [$data[1], $data[2] ?? \PDO::PARAM_STR];
		}

		$fieldsStr = implode(',', $fields);
		$paramsStr = implode(',', $params);

		$sql = "INSERT INTO `{$table}` ({$fieldsStr}) VALUES ({$paramsStr})";
		$this->pdo->prepare($sql)?->bindValues($values)?->execute();
		return (int)$this->pdo->lastInsertId();
	}

	/**
	 * Updates data in the database.
	 *
	 * @param string $table The name of the table to update.
	 * @param array $dataArr The data to be updated, in the format:
	 * [
	 *   ['id', 2, \PDO::PARAM_INT],
	 *   ['name', 'James', \PDO::PARAM_STR],
	 * ]
	 * @param array $whereArr The conditions for updating the data, in the format:
	 * [
	 *   ['id', 2, \PDO::PARAM_INT],
	 * ]
	 * @param int $limit The maximum number of rows to update.
	 *
	 * @return int The number of affected rows.
	 */
	public function update(string $table, array $dataArr, array $whereArr = [], int $limit = 1): int
	{
		$fields = $values = $where = [];
		foreach ($dataArr as $data) {
			$fields[] = "`{$data[0]}` = :{$data[0]}";
			$values[":{$data[0]}"] = [$data[1], $data[2] ?? \PDO::PARAM_STR];
		}
		$i = 0;
		foreach ($whereArr as $wData) {
			$i++; // The $i is in there because row wouldn't update with :value already being set above
			$where[] = "`{$wData[0]}` = :{$wData[0]}{$i}";
			$values[":{$wData[0]}{$i}"] = [$wData[1], $wData[2] ?? \PDO::PARAM_STR];
		}

		$fieldsStr = implode(',', $fields);
		$whereStr = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

		$sql = "UPDATE `{$table}` SET {$fieldsStr} {$whereStr} LIMIT {$limit}";
		$this->pdo->prepare($sql)?->bindValues($values)?->execute();
		return $this->pdo->rowCount();
	}

	/**
	 * Removes data from the database.
	 *
	 * @param string $table The name of the table to delete data from.
	 * @param array $dataArr The conditions for deleting the data, in the format:
	 * [
	 *   ['id', 2, \PDO::PARAM_INT],
	 * ]
	 * @param int $limit The maximum number of rows to delete.
	 *
	 * @return int The number of affected rows.
	 */
	public function delete(string $table, array $dataArr, int $limit = 1): int
	{
		$fields = $values = [];
		foreach ($dataArr as $data) {
			$fields[] = "`{$data[0]}` = :{$data[0]}";
			$values[":{$data[0]}"] = [$data[1], $data[2] ?? \PDO::PARAM_STR];
		}

		$fieldsStr = implode(' AND ', $fields);

		$sql = "DELETE FROM `{$table}` WHERE {$fieldsStr} LIMIT {$limit}";
		$this->pdo->prepare($sql)?->bindValues($values)?->execute();
		return $this->pdo->rowCount();
	}

	/**
	 * Inserts multiple data rows into the database.
	 *
	 * @param string $table The name of the table to insert data into.
	 * @param array $dataArr The data to be inserted, in the format:
	 * [
	 *   [
	 *     ['id', 2, \PDO::PARAM_INT],
	 *     ['name', 'James', \PDO::PARAM_STR],
	 *   ],
	 *   ...
	 * ]
	 *
	 * @return int|false The last inserted ID or false on failure.
	 */
	public function insertMulti(string $table, array $dataArr): int|false
	{
		$i = 0;
		$fields = [];
		$params = [];
		$values = [];
		foreach ($dataArr as $data) {
			$placeholders = [];
			foreach ($data as $rowData) {
				$i++;
				if (!in_array("`{$rowData[0]}`", $fields)) {
					$fields[] = "`{$rowData[0]}`";
				}
				$placeholders[] = ":{$rowData[0]}{$i}";
				$values[":{$rowData[0]}{$i}"] = [$rowData[1], $rowData[2] ?? \PDO::PARAM_STR];
			}
			$params[] = '(' . implode(',', $placeholders) . ')';
		}

		$fieldsStr = implode(',', $fields);
		$paramsStr = implode(',', $params);

		$sql = "INSERT INTO `{$table}` ({$fieldsStr}) VALUES {$paramsStr}";
		$this->pdo->prepare($sql)?->bindValues($values)?->execute();
		return (int)$this->pdo->lastInsertId();
	}

	/**
	 * Selects data from the database.
	 *
	 * @param string $sql The full SQL query to execute.
	 * @param int $limit If returning multiple rows, specify the number of rows.
	 * @param array $binds The data to bind to the query, in the format:
	 * [
	 *   ['id', 2, \PDO::PARAM_INT],
	 * ]
	 *
	 * @return array The retrieved rows from the database.
	 */
	public function select(string $sql, int $limit = 1, array $binds = []): array
	{
		$get = $this->pdo->prepare($sql);
		if (!empty($binds)) {
			$get?->bindValues($binds);
		}
		$get?->execute();
		if ($limit > 1) {
			return $get?->fetchAll() ?? [];
		}
		return $get?->fetch() ?: [];
	}
}
