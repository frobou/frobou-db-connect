<?php

namespace Frobou\Db\Sgdb;

interface SgdbInterface
{
    public function select($query, $params = [], $has_count = false, $fetch_mode = \PDO::FETCH_OBJ);

    public function insert($query, $params = []);

    public function update($query, $params = []);

    public function delete($query, $params = []);
}
