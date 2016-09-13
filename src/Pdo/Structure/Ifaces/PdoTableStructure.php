<?php

namespace Frobou\Pdo\Structure\Ifaces;

use Frobou\Pdo\Db\DbAccess;

interface PdoTableStructure {

    public function getTableStructure($table_name, DbAccess $db);
}
