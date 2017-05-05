<?php

namespace Frobou\Db\Sgdb;

use Frobou\Exceptions\FrobouSgdbErrorException;

class Mysql extends SgdbAbstract
{

    private function prepare($query, $params)
    {
        if (!$this->stmt = $this->connection->prepare($query)) {
            throw new FrobouSgdbErrorException('Prepare error');
        }
        if (count($params) > 0) {
            foreach ($params as $param) {
                $this->stmt->bindValue($param['param'], $param['value'], $param['type']);
            }
        }
        return true;
    }

    private function logInfo($operation, $query, $params)
    {
        if (!is_null($this->logger) && $this->debug === true) {
            $this->logger->info($operation, ['Query' => $this->paramSubst($query, $params)]);
        }
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
        $error = 'um erro';
        if (!is_null($this->logger) && $this->debug === true) {
            $error .= ' - Query: ' . $this->paramSubst($query, $params);
        }
        return ['0001', trim($error)];
    }

    private function setError($code, $text)
    {
        $this->error[1] = $code;
        $this->error[2] = $text;
    }

    public function getTotalCount()
    {
        return $this->connection->query('SELECT FOUND_ROWS() as rowcount;')->fetch(\PDO::FETCH_COLUMN);
    }

    private function execute($operation, $query, $params, $has_count = false, $fetch_mode = \PDO::FETCH_OBJ)
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
        if (intval($this->stmt->errorCode()) > 0) {
            $error = $this->stmt->errorInfo();
            $this->setError("I-{$error[1]}", $error[2]);
            return false;
        }
        if ($operation === 'insert') {
            $this->last_id = $this->connection->lastInsertId();
        } else {
            $this->row_count = $this->stmt->rowCount();
            if ($this->row_count == 0 && !$this->transaction) {
                if ($operation === 'delete') {
                    $this->setError('I-9991', 'nada foi excluido');
                    return false;
                } else if ($operation === 'update') {
                    $this->setError('I-9992', 'nada foi atualizado');
                    return false;
                }
            }
        }
        return $result;
    }

    public function select($query, $params = [], $has_count = false, $fetch_mode = \PDO::FETCH_OBJ)
    {
        return $this->execute('select', $query, $params, $has_count, $fetch_mode);
    }

    public function insert($query, $params = [])
    {
        return $this->execute('insert', $query, $params);
    }

    public function update($query, $params = [])
    {
        return $this->execute('update', $query, $params);
    }

    public function delete($query, $params = [])
    {
        return $this->execute('delete', $query, $params);
    }

    public function getError(){
        if (isset($this->error[1])) {
            return ['code' =>$this->error[1], 'message' => $this->error[2]];
        }
        return null;
    }

}