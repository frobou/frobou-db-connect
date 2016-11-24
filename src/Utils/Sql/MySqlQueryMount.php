<?php

namespace Frobou\Utils\Sql;

use Frobou\Pdo\Exceptions\FrobouSqlErrorException;

abstract class Join
{
    const INNER = 0;
    const LEFT = 1;
    const RIGTH = 2;
}

class MySqlQueryMount
{

    protected $select = null;
    protected $table;
    protected $where = null;
    protected $and = null;
    protected $join = null;

    public function __construct($table)
    {
        if (is_string($table)) {
            $this->table = "FROM {$table}";
        } else if (is_array($table) && count($table) === 1) {
            foreach ($table as $key => $value) {
                $this->table = "FROM {$key} as {$value}";
            }
        } else {
            throw new FrobouSqlErrorException('Table must be one value');
        }
    }

    public function getQuery()
    {
        if (!isset($this->select)) {
            throw new FrobouSqlErrorException('Select fields not found');
        }
        if (isset($this->and) && !isset($this->where)) {
            throw new FrobouSqlErrorException('Where data not found');
        }
        return $this->select . $this->where . $this->and . $this->join;
    }

    // select SQL_CALC_FOUND_ROWS
    public function addSelectFields(array $fields, $calcrows = false)
    {
        if (count($fields) < 1) {
            throw new FrobouSqlErrorException('Search fields can not be empty');
        }
        $this->select = 'SELECT ';
        if ($calcrows === true) {
            $this->select .= 'SQL_CALC_FOUND_ROWS ';
        }

        $f = '';
        foreach ($fields as $value) {
            $f .= "{$value}, ";
        }
        $this->select .= substr($f, 0, strlen($f) - 2) . ' ' . $this->table . ' ';
        return $this;
    }

    // where
    public function addWhere(array $where)
    {
        if (count($where) != 1) {
            throw new FrobouSqlErrorException('Where field must have one field');
        }
        $this->where = 'WHERE ';
        return $this;
    }

    // and
    public function addAnd(array $and)
    {
        if (count($and) < 1) {
            throw new FrobouSqlErrorException('And fields can not be empty');
        }
        $this->and = 'AND ';
        return $this;
    }

    // join
    public function addJoin($join)
    {
        if (count($join) < 1) {
            throw new FrobouSqlErrorException('Search fields can not be empty');
        }
        $this->join = 'JOIN ';
        return $this;
    }


}