<?php

namespace Frobou\Db\Sgdb;

class Mysql extends SgdbAbstract
{

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

    protected function setError($code, $text)
    {
        $this->error[1] = $code;
        $this->error[2] = $text;
    }

    protected function getTotalCount()
    {
        return $this->connection->query('SELECT FOUND_ROWS() as rowcount;')->fetch(\PDO::FETCH_COLUMN);
    }

    public function lastInsertedId()
    {
        return $this->last_id;
    }

}