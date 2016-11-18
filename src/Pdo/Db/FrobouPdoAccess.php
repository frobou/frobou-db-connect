<?php

namespace Frobou\Pdo\Db;

abstract class FrobouPdoAccess
{
    private function selectDb($db_name)
    {
        if (is_null($db_name)) {
            return $this->config->getDefaultDb();
        }
        return $db_name;
    }

    public function beginTransaction($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        $this->transaction[$db_name] = true;
        $this->connect($db_name);
        if (!is_null($this->logger) && $this->debug === true) {
            $this->logger->info('start transaction', ['status' => $this->transaction]);
        }
        return $this->conn[$db_name]->beginTransaction();
    }

    public function commit($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        if (isset($this->transaction[$db_name]) && $this->transaction[$db_name]) {
            $sts = $this->conn[$db_name]->commit();
            if (!is_null($this->logger) && $this->debug === true) {
                $this->logger->info('commit', ['status' => $sts]);
            }
            $this->transaction[$db_name] = false;
            return $sts;
        }
        return false;
    }

    public function rollback($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        if (isset($this->transaction[$db_name]) && $this->transaction[$db_name]) {
            $sts = $this->conn[$db_name]->rollback();
            if (!is_null($this->logger) && $this->debug === true) {
                $this->logger->info('rollback', ['status' => $sts]);
            }
            $this->transaction[$db_name] = false;
            return $sts;
        }
        return false;
    }

    public function select($query, $db_name = null, $params = [], $fetch_mode = \PDO::FETCH_OBJ, $has_count = false)
    {
        return $this->execute('select', $query, $db_name, $params, $fetch_mode, $has_count);
    }

    public function insert()
    {
    }

    public function update()
    {
    }

    public function delete()
    {
    }

    /**
     * estatisticas de operacao, como lastinsertid, rowcount, erros, etc
     */
    public function stats()
    {
        if (isset($this->error)) {
            return $this->error;
        }
        return null;
    }

    private function prepare($query, $db_name, $params)
    {
        if (!$this->transaction) {
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

    private function execute($operation, $query, $db_name, $params, $fetch_mode = \PDO::FETCH_OBJ, $has_count = false)
    {
        if ($this->prepare($query, $db_name, $params) !== true) {
            if (!is_null($this->getError())) {
                return false;
            }
            $this->errorMount($operation, $query, $params);
            return false;
        }
    }

}