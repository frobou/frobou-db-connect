<?php

namespace Frobou\Pdo\Validator\Interfaces;

use Frobou\Pdo\Db\DbAccess;

interface PdoTableStructure {

    public function getTableStructure($table_name, DbAccess $db);
}
