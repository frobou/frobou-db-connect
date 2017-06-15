<?php

namespace Frobou\Db\Sgdb;

class Postgre
{

    public function select($query, $db_name = null, $params = [], $has_count = false, $fetch_mode = \PDO::FETCH_OBJ)
    {
        return $this->execute('select', $query, $db_name, $params, $has_count, $fetch_mode);
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

}
