<?php

namespace Frobou\Db;

use Frobou\Db\Exceptions\FrobouConnectionException;
use Frobou\Db\Exceptions\FrobouSgdbErrorException;

abstract class FrobouDbAccess
{
    private $last_id;

    public function beginTransaction($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        try {
            $this->connect($db_name);
            $tr = $this->conn[$db_name]->beginTransaction();
            if (!is_null($this->logger) && $this->debug === true) {
                $this->logger->info('start transaction', ['status' => $tr, 'obj' => spl_object_hash($this->conn[$db_name])]);
            }
            if ($tr === false) {
                //colocar nas estatisticas
            }
            return $tr;
        } catch (\PDOException $e) {
            throw new FrobouConnectionException($e->getMessage());
        }
    }

    public function commit($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        if (isset($this->conn[$db_name]) && $this->conn[$db_name]->inTransaction()) {
            $sts = $this->conn[$db_name]->commit();
            if (!is_null($this->logger) && $this->debug === true) {
                $this->logger->info('commit', ['status' => $sts]);
            }
            if ($sts === false) {
//colocar nas estatisticas
            }
            return $sts;
        }
        return false;
    }

    public function rollback($db_name = null)
    {
        $db_name = $this->selectDb($db_name);
        if (isset($this->conn[$db_name]) && $this->conn[$db_name]->inTransaction()) {
            try {
                $sts = $this->conn[$db_name]->rollback();
                if (!is_null($this->logger) && $this->debug === true) {
                    $this->logger->info('rollback', ['status' => $sts, 'obj' => spl_object_hash($this->conn[$db_name])]);
                }
                if ($sts === false) {
                    //colocar nas estatisticas
                }
                return $sts;
            } catch (\PDOException $e) {
                throw new FrobouConnectionException($e->getMessage());
            }
        }
        return false;
    }

    public function select($query, $db_name = null, $params = [], $has_count = false, $fetch_mode = \PDO::FETCH_OBJ)
    {
        $db_name = $this->selectDb($db_name);
        $db = $this->dbInstance($db_name);
        try {
            $ret = $db->select($query, $params, $has_count, $fetch_mode);
            if (!$this->conn[$db_name]->inTransaction()) {
                $this->disconnect($db_name);
            }
            return $ret;
        } catch (\PDOException $e) {
            throw new FrobouSgdbErrorException($e->getMessage());
        }

    }

    public function insert($query, $db_name = null, $params = [])
    {
        $db_name = $this->selectDb($db_name);
        $db = $this->dbInstance($db_name);
        try {
            $ret = $db->insert($query, $params);
            if (!$this->conn[$db_name]->inTransaction()) {
                $this->disconnect($db_name);
            }
            $this->last_id = $db->last_id;
            return $ret;
        } catch (\PDOException $e) {
            throw new FrobouSgdbErrorException($e->getMessage());
        }
    }

    public function update($query, $db_name = null, $params = [])
    {
        $db_name = $this->selectDb($db_name);
        $db = $this->dbInstance($db_name);
        try {
            $ret = $db->update($query, $params);
            if (!$this->conn[$db_name]->inTransaction()) {
                $this->disconnect($db_name);
            }
            return $ret;
        } catch (\PDOException $e) {
            throw new FrobouSgdbErrorException($e->getMessage());
        }
    }

    public function delete($query, $db_name = null, $params = [])
    {
        $db_name = $this->selectDb($db_name);
        $db = $this->dbInstance($db_name);
        try {
            $ret = $db->delete($query, $params);
            if (!$this->conn[$db_name]->inTransaction()) {
                $this->disconnect($db_name);
            }
            return $ret;
        } catch (\PDOException $e) {
            throw new FrobouSgdbErrorException($e->getMessage());
        }
    }

    /**
     * estatisticas de operacao, como lastinsertid, rowcount, erros, etc
     */
    public function stats()
    {
//        if (isset($this->error)) {
//            return $this->error;
//        }
//        return null;
        $result['last_id'] = $this->last_id;
        return $result;
    }

}
