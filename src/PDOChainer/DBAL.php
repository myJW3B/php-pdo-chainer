<?php

/**
 * DBAL over PDOChainer.
 *
 * See usage examples in README file.
 * See lincense text in LICENSE file.
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
    private $pdo;

    /**
     * Default constructor.
     *
     * @param \PDOChainer\PDOChainer $pdo
     */
    public function __construct(\PDOChainer\PDOChainer $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Inserts data into DataBase.
     *
     * @param string $table
     * @param array $data
     * [
     *   ['id', 2, \PDO::PARAM_INT],
     *   ['name', 'James', \PDO::PARAM_STR],
     * ]
     *
     * @return int|false Inserted ID or false
     */
    public function insert($table, array $dataArr){
        $fields = $params = $values = [];
        foreach ($dataArr as $data) {
            $fields[] = "`{$data[0]}`";
            $params[] = ":{$data[0]}";
            $values[] = [":{$data[0]}", $data[1], (isset($data[2]) ? $data[2] : \PDO::PARAM_STR)];
        }

        $fields = implode(',', $fields);
        $params = implode(',', $params);

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$params})";
        $this->pdo->prepare($sql)->bindValues($values)->execute();
        return (bool) $this->pdo->lastInsertId();
    }

    /**
     * Updates data in DataBase.
     *
     * @param string $table
     * @param array $dataArr
     * [
     *   ['id', 2, \PDO::PARAM_INT],
     *   ['name', 'James', \PDO::PARAM_STR],
		 *    ...
     * ]
     * @param array $whereArr
     * [
     *   ['id', 2, \PDO::PARAM_INT],
     * ]
     * @param int $limit
     *
     * @return int Affected rows count
     */
    public function update($table, array $dataArr, array $whereArr = [], $limit = 1){
        $fields = $values = $where = [];
        foreach($dataArr as $data){
            $fields[] = "`{$data[0]}` = :{$data[0]}";
            $values[] = [":{$data[0]}", $data[1], (isset($data[2]) ? $data[2] : \PDO::PARAM_STR)];
        }
        $i = 0;
        foreach($whereArr as $wData){
            $i++; // The $i is in there because row wouldnt update with :value already being set above
            $where[] = "`{$wData[0]}` = :{$wData[0]}{$i}";
            $values[] = [":{$wData[0]}{$i}", $wData[1], (isset($wData[2]) ? $wData[2] : \PDO::PARAM_STR)];
        }

        $fields = implode(',', $fields);
        $whereStr = count($where) ? 'WHERE '.implode(' AND ', $where) : '';

        $sql = "UPDATE `{$table}` SET {$fields} {$whereStr} LIMIT {$limit}";
        $this->pdo->prepare($sql)->bindValues($values)->execute();
        return $this->pdo->rowCount();
    }

    /**
     * Removes data from DataBase.
     *
     * @param string $table
     * @param array $dataArr
     * [
     *   ['id', 2, \PDO::PARAM_INT],
     *   ['name', 'James', \PDO::PARAM_STR],
		 *    ...
     * ]
     * @param int $limit
     *
     * @return int Affected rows count
     */
    public function delete($table, array $dataArr, $limit = 1){
        foreach($dataArr as $data){
            $fields[] = "`{$data[0]}` = :{$data[0]}";
            $values[] = [":{$data[0]}", $data[1], (isset($data[2]) ? $data[2] : \PDO::PARAM_STR)];
        }

        $fields = implode(' AND ', $fields);

        $sql = "DELETE FROM `{$table}` WHERE {$fields} LIMIT {$limit}";
        $this->pdo->prepare($sql)->bindValues($values)->execute();
        return $this->pdo->rowCount();
    }

    /**
     * Inserts multiple data into DataBase.
     *
     * @param string $table
     * @param array $dataArr
     * [
     *   [
     *     ['id', 2, \PDO::PARAM_INT],
     *     ['name', 'James', \PDO::PARAM_STR],
     *   ],
     *   ...
     * ]
     *
     * @return int|false Last inserted ID or false
     */
    public function insertMulti($table, array $dataArr){
        $i = 0;
        $fields = array();
        foreach($dataArr as $data){
            $placeholders = array();
            foreach($data as $rowData){
                $i++;
                if(!in_array("`{$rowData[0]}`", $fields)) {
                    $fields[] = "`{$rowData[0]}`";
                }
                $placeholders[] = ":{$rowData[0]}{$i}";
                $values[] = [":{$rowData[0]}{$i}", $rowData[1], (isset($rowData[2]) ? $rowData[2] : \PDO::PARAM_STR)];
            }
            $params[] = '(' . implode(',', $placeholders) . ')';
        }

        $fields = implode(',', $fields);
        $params = implode(',', $params);

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES {$params}";
        $this->pdo->prepare($sql)->bindValues($values)->execute();
        return (int) $this->pdo->lastInsertId();
    }

		/**
     * Select data from the DataBase.
     *
     * @param string $sql 	Full sql statement
     * @param int $limit		if returning multi rows, put a number grater than 1
     * @param array $binds
     * [
     *   ['id', 2, \PDO::PARAM_INT],
     *   ['name', 'James', \PDO::PARAM_STR],
     *   ...
     * ]
     *
     * @return array containing all rows retrieved from the database
     */

    public function select($sql, $limit = 1, $binds = []){
        $get = $this->pdo->prepare($sql);
        if($binds != ''){
            $get = $get->bindValues($binds);
        }
        $get = $get->execute();
        if($limit > 0){
            if($limit > 1){
                $get = $get->fetchAll();
            } else {
                $get = $get->fetch();
            }
        }
        return $get;
    }
}