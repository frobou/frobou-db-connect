<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Db\Interfaces\DbMessagesInterface;
use Frobou\Pdo\Structure\Interfaces\PdoTableStructure;
use PDO;
use PDOException;
use PDOStatement;

class DbAccess
{

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
     * @var PdoTableStructure
     */
    private $structure;

    /**
     *
     * @param DbConfig $config
     * @param DbMessagesInterface $message
     * @param type $channel
     * @param PdoTableStructure $structure
     * @param type $logger
     * @return boolean
     */
    public function __construct(DbConfig $config, DbMessagesInterface $message, $channel = 'release', PdoTableStructure $structure = null, $logger = null)
    {
        if (is_null($config)) {
            return false;
        }
        $this->config = $config;
        $this->channel = $channel;
        $this->logger = $logger;
        $this->messages = $message;
        $this->structure = $structure;
    }

    /**
     *
     * @return type
     */
    public function getSchema()
    {
        return $this->config->getDbname();
    }

    /**
     *
     * @param type $table_name
     * @return type
     */
    public function getTableStructure($table_name)
    {
        if (!is_null($this->structure)) {
            return $this->structure->getTableStructure($table_name, $this);
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    private function connect()
    {
        $attrs = [];
        if (count($this->config->getAttributes() > 0)) {
            foreach ($this->config->getAttributes() as $attr) {
                $attrs[$attr['param']] = $attr['value'];
            }
        }
        try {
            $this->conn = new PDO("{$this->config->getServertype()}:"
                . "host={$this->config->getServername()};"
                . "dbname={$this->config->getDbname()};"
                . "charset={$this->config->getCharset()};"
                . "port={$this->config->getPort()}", $this->config->getUsername(), $this->config->getPassword(), $attrs);
        } catch (PDOException $e) {
            $this->setError($e->getCode(), 'SGDB access error');
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function disconnect()
    {
        try {
            $this->conn = null;
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     *
     * @return type
     */
    private function commit()
    {
        if ($this->begin_transaction) {
            return $this->conn->commit();
        }
        return null;
    }

    /**
     *
     * @return type
     */
    private function rollback()
    {
        if ($this->begin_transaction) {
            return $this->conn->rollback();
        }
        return null;
    }

    /**
     *
     * @param type $query
     * @param type $params
     * @return boolean
     */
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

    /**
     *
     * @param type $query
     * @param type $params
     * @return type
     */
    private function paramSubst($query, $params)
    {
        $error = $query;
        foreach ($params as $value) {
            $error = str_replace($value['param'], "'{$value['value']}'", $error);
        }
        return $error;
    }

    /**
     *
     * @param type $operation
     * @param type $query
     * @param type $params
     */
    private function errorMount($operation, $query, $params)
    {
        $error = $this->messages->getGeneric($operation);
        if (strtoupper($this->channel) === 'DEV' || strtoupper($this->channel) === 'DEBUG') {
            $error .= ' - Query: ' . $this->paramSubst($query, $params);
        }
        $this->setError('0001', trim($error));
    }

    /**
     *
     * @return integer
     */
    private function getTotalCount()
    {
        switch (strtoupper($this->config->getDbname())) {
            case 'MYSQL':
                return $this->conn->query('SELECT FOUND_ROWS() as rowcount;')->fetch(PDO::FETCH_COLUMN);
            default:
                break;
        }
        return 0;
    }

    /**
     * Send info to log
     * @param $operation
     * @param $query
     * @param $params
     */
    private function logInfo($operation, $query, $params)
    {
        if ((!is_null($this->logger)) && (strtoupper($this->channel) === 'DEV' || strtoupper($this->channel) === 'DEBUG')) {
            $this->logger->info($operation, ['Query' => $this->paramSubst($query, $params)]);
        }
    }

    /**
     *
     * @param type $operation
     * @param type $query
     * @param type $params
     * @param type $fetch_mode
     * @param type $has_count
     * @return boolean
     */
    private function execute($operation, $query, $params, $fetch_mode = PDO::FETCH_OBJ, $has_count = false)
    {
        if ($this->prepare($query, $params) !== true) {
            if (!is_null($this->getError())) {
                return false;
            }
            $this->errorMount($operation, $query, $params);
            return false;
        }
        if ($operation === 'select') {
            $this->stmt->setFetchMode($fetch_mode);
            if ($this->stmt->execute() === false) {
                $this->errorMount($operation, $query, $params);
                return false;
            }
            $result = $this->stmt->fetchAll();
            if ($has_count) {
                $result['rowcount'] = $this->getTotalCount();
            }
            $this->logInfo($operation, $query, $params);
        } else {
            $result = $this->stmt->execute();
            $this->logInfo($operation, $query, $params);
        }
        if ($this->stmt->errorCode() > 0) {
            $error = $this->stmt->errorInfo();
            $this->setError("I-{$error[1]}", $error[2]);
            return false;
        }
        if ($operation === 'insert') {
            $this->last_id = $this->conn->lastInsertId();
            if ($this->last_id == 0) {
                $this->setError('I-9990', $this->messages->getNotInserted());
                return false;
            }
        } else {
            $this->row_count = $this->stmt->rowCount();
            if ($this->row_count == 0) {
                if ($operation === 'delete') {
                    $this->setError('I-9991', $this->messages->getNotDeleted());
                    return false;
                } else if ($operation === 'update') {
                    $this->setError('I-9992', $this->messages->getNotUpdated());
                    return false;
                }
            }
        }
        if (!$this->begin_transaction) {
            $this->disconnect();
        }
        return $result;
    }

    /**
     *
     * @param type $query
     * @param type $params
     * @param type $fetch_mode
     * @param type $has_count
     * @return type
     */
    public function select($query, $params = [], $fetch_mode = PDO::FETCH_OBJ, $has_count = false)
    {
        return $this->execute('select', $query, $params, $fetch_mode, $has_count);
    }

    /**
     *
     * @param type $query
     * @param type $params
     * @return type
     */
    public function update($query, $params = [])
    {
        return $this->execute('update', $query, $params);
    }

    /**
     * Make an insertion on SGDB
     * @param $query
     * @param array $params
     * @return array|bool
     */
    public function insert($query, $params = [])
    {
        return $this->execute('insert', $query, $params);
    }

    /**
     *
     * @param type $query
     * @param type $params
     * @return type
     */
    public function delete($query, $params = [])
    {
        return $this->execute('delete', $query, $params);
    }

    /**
     *
     * @param type $code
     * @param type $text
     */
    private function setError($code, $text)
    {
        $this->error[1] = $code;
        $this->error[2] = $text;
    }

    /**
     *
     * @return type
     */
    public function getError()
    {
        if (isset($this->error)) {
            return $this->error;
        }
        return null;
    }

    /**
     *
     * @return type
     */
    public function getLastId()
    {
        if (isset($this->last_id)) {
            return $this->last_id;
        }
    }

    /**
     *
     * @return type
     */
    public function getRowCount()
    {
        if (isset($this->row_count)) {
            return $this->row_count;
        }
    }

    /**
     *
     * @return type
     */
    public function beginTransaction()
    {
        $this->connect();
        $this->begin_transaction = true;
        return $this->conn->beginTransaction();
    }

    /**
     *
     * @return type
     */
    public function cancel()
    {
        return $this->conn->rollBack();
    }

    /**
     *
     * @return boolean
     */
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
