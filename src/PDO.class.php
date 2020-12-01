<?php
/*
 * PHP-PDO-MySQL-Class
 * https://github.com/lincanbin/PHP-PDO-MySQL-Class
 *
 * Copyright 2015 Canbin Lin (lincanbin@hotmail.com)
 * http://www.94cb.com/
 *
 * Licensed under the Apache License, Version 2.0:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * A PHP MySQL PDO class similar to the Python MySQLdb.
 */
require(__DIR__ . '/PDO.Log.class.php');
require(__DIR__ . '/PDO.Iterator.class.php');
/** Class DB
 * @property PDO pdo PDO object
 * @property PDOStatement sQuery PDOStatement
 * @property PDOLog PDOLog logObject
 */
class DB
{
	private $Host;
	private $DBPort;
	private $DBName;
	private $DBUser;
	private $DBPassword;
	private $pdo;
	private $sQuery;
	private $connectionStatus = false;
	private $logObject;
	private $parameters;
	public $rowCount   = 0;
	public $columnCount   = 0;
	public $querycount = 0;


	private $retryAttempt = 0; // 失败重试次数
	const AUTO_RECONNECT = true;
	const RETRY_ATTEMPTS = 3; // 最大失败重试次数

    /**
     * DB constructor.
     * @param $Host
     * @param $DBPort
     * @param $DBName
     * @param $DBUser
     * @param $DBPassword
     */
	public function __construct($Host, $DBPort, $DBName, $DBUser, $DBPassword)
	{
		$this->logObject  = new PDOLog();
		$this->Host       = $Host;
		$this->DBPort     = $DBPort;
		$this->DBName     = $DBName;
		$this->DBUser     = $DBUser;
		$this->DBPassword = $DBPassword;
		$this->parameters = array();
		$this->Connect();
	}


	private function Connect()
	{
		try {
			$dsn = 'mysql:';
			$dsn .= 'host=' . $this->Host . ';';
			$dsn .= 'port=' . $this->DBPort . ';';
			if (!empty($this->DBName)) {
				$dsn .= 'dbname=' . $this->DBName . ';';
			}
			$dsn .= 'charset=utf8;';
			$this->pdo = new PDO($dsn,
				$this->DBUser,
				$this->DBPassword,
				array(
					//For PHP 5.3.6 or lower
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					PDO::ATTR_EMULATE_PREPARES => false,

					//长连接
					//PDO::ATTR_PERSISTENT => true,

					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::MYSQL_ATTR_FOUND_ROWS => true
				)
			);
			/*
			//For PHP 5.3.6 or lower
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//$this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);//长连接
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			*/
			$this->connectionStatus = true;

		}
		catch (PDOException $e) {
			$this->ExceptionLog($e, '', 'Connect');
		}
	}

	private function SetFailureFlag()
	{
		$this->pdo = null;
		$this->connectionStatus = false;
	}

    /**
     * close pdo connection
     */
	public function closeConnection()
	{
		$this->pdo = null;
    }

	private function Init($query, $parameters = null, $driverOptions = array())
	{
		if (!$this->connectionStatus) {
			$this->Connect();
		}
		try {
			$this->parameters = $parameters;
			$this->sQuery     = $this->pdo->prepare($this->BuildParams($query, $this->parameters), $driverOptions);

			if (!empty($this->parameters)) {
				if (array_key_exists(0, $parameters)) {
					$parametersType = true;
					array_unshift($this->parameters, "");
					unset($this->parameters[0]);
				} else {
					$parametersType = false;
				}
				foreach ($this->parameters as $column => $value) {
					$this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); //It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
				}
			}

			if (!isset($driverOptions[PDO::ATTR_CURSOR])) {
                $this->sQuery->execute();
            }
			$this->querycount++;
		}
		catch (PDOException $e) {
			$this->ExceptionLog($e, $this->BuildParams($query), 'Init', array('query' => $query, 'parameters' => $parameters));

		}

		$this->parameters = array();
	}

	private function BuildParams($query, $params = null)
	{
		if (!empty($params)) {
			$array_parameter_found = false;
			foreach ($params as $parameter_key => $parameter) {
				if (is_array($parameter)){
					$array_parameter_found = true;
					$in = "";
					foreach ($parameter as $key => $value){
						$name_placeholder = $parameter_key."_".$key;
						// concatenates params as named placeholders
                            $in .= ":".$name_placeholder.", ";
						// adds each single parameter to $params
						$params[$name_placeholder] = $value;
					}
					$in = rtrim($in, ", ");
					$query = preg_replace("/:".$parameter_key."/", $in, $query);
					// removes array form $params
					unset($params[$parameter_key]);
				}
			}

			// updates $this->params if $params and $query have changed
			if ($array_parameter_found) $this->parameters = $params;
		}
		return $query;
	}

    /**
     * @return bool
     */
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}

    /**
     * @return bool
     */
	public function commit()
	{
		return $this->pdo->commit();
	}

    /**
     * @return bool
     */
	public function rollBack()
	{
		return $this->pdo->rollBack();
	}

    /**
     * @return bool
     */
	public function inTransaction()
	{
		return $this->pdo->inTransaction();
	}

    /**
     * mysql unbuffered mode, suitable for reading huge data source
     * @see https://www.php.net/manual/en/mysqlinfo.concepts.buffering.php
     *
     * @return void
     */
	public function unbuffered()
	{
		$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
	}

    /**
     * execute a sql query, returns an result array in the select operation, and returns the number of rows affected in other operations
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return array|int|null
     */
	public function query($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
	{
		$query        = trim($query);
		$rawStatement = preg_split("/( |\r|\n)/", $query);
		$this->Init($query, $params);
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
			return $this->sQuery->fetchAll($fetchMode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			return $this->sQuery->rowCount();
		} else {
			return NULL;
		}
	}

    /**
     * execute a sql query, returns an iterator in the select operation, and returns the number of rows affected in other operations
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return int|null|PDOIterator
     */
    public function iterator($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
    {
        $query        = trim($query);
        $rawStatement = preg_split("/( |\r|\n)/", $query);
        $this->Init($query, $params, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $statement = strtolower(trim($rawStatement[0]));
        if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
            return new PDOIterator($this->sQuery, $fetchMode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }

    /**
     * @param $tableName
     * @param null $params
     * @return bool|string
     */
	public function insert($tableName, $params = null)
	{
		$keys = array_keys($params);
		$rowCount = $this->query(
			'INSERT INTO ' . $tableName . ' (`' . implode('`,`', $keys) . '`)
			VALUES (:' . implode(',:', $keys) . ')',
			$params
		);
		if ($rowCount === 0) {
			return false;
		}
		return $this->lastInsertId();
    }

    /**
     * insert multi rows
     *
     * @param string $tableName database table name
     * @param array $params structure like [[colname1 => value1, colname2 => value2], [colname1 => value3, colname2 => value4]]
     * @return boolean success or not
     */
    public function insertMulti($tableName, $params = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $insParaStr = '';
            $insValueArray = array();

            foreach ($params as $addRow) {
                $insColStr = implode('`,`', array_keys($addRow));
                $insParaStr .= '(' . implode(",", array_fill(0, count($addRow), "?")) . '),';
                $insValueArray = array_merge($insValueArray, array_values($addRow));
            }
            $insParaStr = substr($insParaStr, 0, -1);
            $dbQuery = "INSERT INTO {$tableName} (
                            `$insColStr`
                        ) VALUES
                            $insParaStr";
            $rowCount = $this->query($dbQuery, $insValueArray);
        }
        return (bool) ($rowCount > 0);
    }

    /**
     * update
     *
     * @param string $tableName
     * @param array $params
     * @param array $where
     * @return int affect rows
     */
    public function update($tableName, $params = array(), $where = array())
    {
        $rowCount = 0;
        if (!empty($params)) {
            $updColStr = '';
            $whereStr = '';
            $updatePara = array();
            // Build update statement
            foreach ($params as $key => $value) {
                $updColStr .= "{$key}=?,";
            }
            $updColStr = substr($updColStr, 0, -1);
            $dbQuery = "UPDATE {$tableName}
                        SET {$updColStr}";
            // where condition
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    // Is there need to add "OR" condition?
                    $whereStr .= "AND {$key}=?";
                }
                $dbQuery .= " WHERE 1=1 {$whereStr}";
                $updatePara = array_merge(array_values($params), array_values($where));
            } else {
                $updatePara = array_values($params);
            }
            $rowCount = $this->query($dbQuery, $updatePara);
        }
        return $rowCount;
    }

    /**
     * @return string
     */
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}

    /**
     * @param $query
     * @param null $params
     * @return array
     */
	public function column($query, $params = null)
	{
		$this->Init($query, $params);
		$resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultColumn;
	}

    /**
     * @param $query
     * @param null $params
     * @param int $fetchmode
     * @return mixed
     */
	public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$this->Init($query, $params);
		$resultRow = $this->sQuery->fetch($fetchmode);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultRow;
	}

    /**
     * @param $query
     * @param null $params
     * @return mixed
     */
	public function single($query, $params = null)
	{
		$this->Init($query, $params);
		return $this->sQuery->fetchColumn();
	}

    /**
     * @param PDOException $e
     * @param string $sql
     * @param string $method
     * @param array $parameters
     */
	private function ExceptionLog(PDOException $e, $sql = "", $method = '', $parameters = array())
	{
		$message = $e->getMessage();
		$exception = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";

		if (!empty($sql)) {
			$message .= "\r\nRaw SQL : " . $sql;
		}
		$this->logObject->write($message, $this->DBName . md5($this->DBPassword));
		if (
			self::AUTO_RECONNECT
			&& $this->retryAttempt < self::RETRY_ATTEMPTS
			&& stripos($message, 'server has gone away') !== false
			&& !empty($method)
			&& !$this->inTransaction()
		) {
			$this->SetFailureFlag();
			$this->retryAttempt ++;
			$this->logObject->write('Retry ' . $this->retryAttempt . ' times', $this->DBName . md5($this->DBPassword));
			call_user_func_array(array($this, $method), $parameters);
		} else {
			if (($this->pdo === null || !$this->inTransaction()) && php_sapi_name() !== "cli") {
				//Prevent search engines to crawl
				header("HTTP/1.1 500 Internal Server Error");
				header("Status: 500 Internal Server Error");
				echo $exception;
				exit();
			} else {
				throw $e;
			}
		}
	}
}
