<?php

/**
 * 
 * @author Fabio Pimenta <blobs@frobou.com.br>
 * @version 1.0.0
 * @license MIT
 */

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Db\Interfaces\DbMessagesInterface;
use Frobou\Pdo\Validator\Interfaces\PdoValidatorInterface;
use PDO;
use PDOException;
use PDOStatement;

class DbAccess {

    private $config;

    /**
     * @var PDO
     */
    private $conn = null;

    /**
     * @var PDOStatement
     */
    private $stmt = null;
    private $error;
    private $last_id;
    private $row_count;
    private $begin_transaction;
    private $logger;
    private $channel;

    /**
     * @var DbMessagesInterface 
     */
    private $messages;

    /**
     * @var PdoValidatorInterface 
     */
    private $validator;

    public function __construct(DbConfig $config, DbMessagesInterface $message, $channel = 'debug', PdoValidatorInterface $validator = null, $logger = null)
    {
        if (is_null($config)) {
            return false;
        }
        $this->config = $config;
        $this->channel = $channel;
        $this->logger = $logger;
        $this->messages = $message;
        $this->validator = $validator;
    }

    private function connect()
    {
        try {
            $this->conn = new PDO("{$this->config->getServertype()}:"
                    . "host={$this->config->getServername()};"
                    . "dbname={$this->config->getDbname()};"
                    . "charset={$this->config->getCharset()};"
                    . "port={$this->config->getPort()}", $this->config->getUsername(), $this->config->getPassword());
            if (count($this->config->getAttributes() > 0)) {
                foreach ($this->config->getAttributes() as $attr) {
                    $this->conn->setAttribute($attr['param'], $attr['value']);
                }
            }
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    private function disconnect()
    {
        try {
            $this->conn = null;
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function commit()
    {
        if ($this->begin_transaction) {
            return $this->conn->commit();
        }
        return null;
    }

    private function rollback()
    {
        if ($this->begin_transaction) {
            return $this->conn->rollback();
        }
        return null;
    }

    private function prepare($query, $params)
    {
        if (!$this->begin_transaction) {
            if (!$this->connect()) {
                return null;
            }
        }
        if (!$this->stmt = $this->conn->prepare($query)) {
            return null;
        }
        if (count($params) > 0) {
            foreach ($params as $param) {
                $this->stmt->bindValue($param['param'], $param['value'], $param['type']);
            }
        }
        return true;
    }

    private function paramSubst($query, $params)
    {
        $error = $query;
        foreach ($params as $value) {
            $error = str_replace($value['param'], "'{$value['value']}'", $error);
        }
        return $error;
    }

    private function errorMount($operation, $query, $params)
    {
        $error = $this->messages->getGeneric($operation);
        if (strtoupper($this->channel) === 'DEV' || strtoupper($this->channel) === 'DEBUG') {
            $error .= ' - Query: ' . $this->paramSubst($query, $params);
        }
        $this->setError('0001', trim($error));
    }

    private function execute($operation, $query, $params, $fetch_mode = PDO::FETCH_OBJ, $has_count = false)
    {
        if ($this->prepare($query, $params) !== true) {
            $this->errorMount($operation, $query, $params);
            return false;
        }
        if ($operation === 'select') {
            $this->stmt->setFetchMode($fetch_mode);
            $this->stmt->execute();
            $result = $this->stmt->fetchAll();
            if ($has_count) {
                $result['rowcount'] = $this->conn->query('SELECT FOUND_ROWS() as rowcount;')->fetch(PDO::FETCH_COLUMN);
            }
            if ((!is_null($this->logger)) && (strtoupper($this->channel) === 'DEV' || strtoupper($this->channel) === 'DEBUG')) {
                $this->logger->info($operation, ['Query' => $this->paramSubst($query, $params)]);
            }
        } else {
            $result = $this->stmt->execute();
            if ((!is_null($this->logger)) && (strtoupper($this->channel) === 'DEV' || strtoupper($this->channel) === 'DEBUG')) {
                $this->logger->info($operation, ['Query' => $this->paramSubst($query, $params)]);
            }
        }
        if ($this->stmt->errorCode() > 0) {
            $error = $this->stmt->errorInfo();
            $this->setError("I-{$error[1]}", $error[2]);
            return false;
        }
        if ($operation === 'insert') {
            $this->last_id = $this->conn->lastInsertId();
            if ($this->last_id == 0) {
                return($this->messages->getNotInserted());
            }
        } else {
            $this->row_count = $this->stmt->rowCount();
            if ($this->row_count == 0) {
                if ($operation === 'delete') {
                    return($this->messages->getNotDeleted());
                } else if ($operation === 'update') {
                    return($this->messages->getNotUpdated());
                }
            }
        }
        if (!$this->begin_transaction) {
            $this->disconnect();
        }
        return $result;
    }

    public function select($query, $params = [], $fetch_mode = PDO::FETCH_OBJ, $has_count = false)
    {
        return $this->execute('select', $query, $params, $fetch_mode, $has_count);
    }

    public function update($query, $params = [])
    {
        return $this->execute('update', $query, $params);
    }

    public function insert($query, $params = [])
    {
        if (!is_null($this->validator)) {
            $valid = $this->validator->setTableName('insert', $query);
            if ($valid === false) {
                return $valid;
            }
            $sql = "select COLUMN_NAME, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, "
                    . "NUMERIC_PRECISION, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_TYPE "
                    . "from information_schema.columns cols "
                    . "where table_schema = '{$this->config->getDbname()}' and TABLE_NAME = '{$valid[1]}' "
                    . "and cols.EXTRA not like '%auto_increment%'";
            $table = $this->select($sql);
            if (count($table) > 0) {
                $out = $this->validator->Validate($table);
            }
        }
        var_dump($out);die;
        return $this->execute('insert', $query, $params);
    }

    public function delete($query, $params = [])
    {
        return $this->execute('delete', $query, $params);
    }

    private function setError($code, $text)
    {
        $this->error[1] = $code;
        $this->error[2] = $text;
    }

    public function getError()
    {
        if (isset($this->error)) {
            return $this->error;
        }
    }

    public function getLastId()
    {
        if (isset($this->last_id)) {
            return $this->last_id;
        }
    }

    public function getRowCount()
    {
        if (isset($this->row_count)) {
            return $this->row_count;
        }
    }

    public function beginTransaction()
    {
        $this->connect();
        $this->begin_transaction = true;
        return $this->conn->beginTransaction();
    }

    public function cancel()
    {
        return $this->conn->rollBack();
    }

    public function save()
    {
        $comm = $this->commit();
        if (!is_null($comm)) {
            if ($comm === true) {
                return true;
            } else {
                $this->rollback();
                return false;
            }
        }
        return null;
    }

}
