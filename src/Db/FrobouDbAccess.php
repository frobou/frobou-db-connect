<?php

namespace Frobou\Db;

use Frobou\Db\Exceptions\FrobouDbConnectionException;
use Frobou\Db\Exceptions\FrobouDbSgdbErrorException;

abstract class FrobouDbAccess
{
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
            throw new FrobouDbConnectionException($e->getMessage());
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
                throw new FrobouDbConnectionException($e->getMessage());
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
            throw new FrobouDbSgdbErrorException($e->getMessage());
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
            $this->last_id = $db->lastInsertedId();
            return $ret;
        } catch (\PDOException $e) {
            throw new FrobouDbSgdbErrorException($e->getMessage());
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
            throw new FrobouDbSgdbErrorException($e->getMessage());
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
            throw new FrobouDbSgdbErrorException($e->getMessage());
        }
    }

    /**
     * estatisticas de operacao, como lastinsertid, rowcount, erros, etc
     */
    public function stats()
    {
        $out = [];
        if (isset($this->error)) {
            $out['error'] = $this->error;
        }
        if (isset($this->last_id)) {
            $out['last_id'] = $this->last_id;
        }
        return $out;
    }

}